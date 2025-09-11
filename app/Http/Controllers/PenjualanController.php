<?php

namespace App\Http\Controllers;

use App\Models\KonfigBunga;
use App\Models\Penjualan;
use App\Models\PenjualanCicil;
use App\Models\PenjualanDetail;
use App\Models\StokUnit;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

use function Illuminate\Log\log;

class PenjualanController extends Controller
{
    public function index(Request $request): View
    {
        return view('transaksi.Penjualan', [
            // 'roles' => Role::with('permissions')->get(),
            'invoice' => $this->genCode(),
            'unit' => Unit::find(Auth::user()->unit_kerja),
        ]);
    }
    public function nota($invoice): View
    {   
        $hdr = Penjualan::join('users','users.id','penjualan.created_user')
            ->leftJoin('users as anggota','anggota.id','penjualan.anggota_id')
            ->select('penjualan.*','users.name as kasir','anggota.nomor_anggota')
            ->where('penjualan.nomor_invoice',$invoice)
            ->first();

        $dtl = PenjualanDetail::join('barang','barang.id','penjualan_detail.barang_id')
            ->select('barang.nama_barang','barang.kode_barang','penjualan_detail.qty','penjualan_detail.harga')
            ->where('penjualan_id',$hdr->id)
            ->get();

        if ($hdr->metode_bayar == 'cicilan') {
        // Ambil semua cicilan untuk transaksi ini
        $cicilan = PenjualanCicil::where('penjualan_id', $hdr->id)
                    ->orderBy('cicilan','asc')
                    ->get();

        return view('transaksi.PenjualanNotaCicilan', [
            'hdr'     => $hdr,
            'dtl'     => $dtl,
            'cicilan' => $cicilan
        ]);
    } else {
            return view('transaksi.PenjualanNota', [
                'hdr' => $hdr,
                'dtl' => $dtl,
            ]);
        }
    }

    function genCode(){
        $total = Penjualan::withTrashed()->whereDate('created_at', date("Y-m-d"))->count();
        $nomorUrut = $total + 1;
        $newcode='INV-'.date("ymd").str_pad($nomorUrut, 3, '0', STR_PAD_LEFT);
        return $newcode;
    }
    public function getInvoice(){
        return response()->json($this->genCode());
    }
    public function getAnggota(Request $request){
        $query = $request->get('query');

        $users = User::leftJoin('penjualan_cicilan', function($join) {
            $join->on('penjualan_cicilan.anggota_id', '=', 'users.id')
                ->where('penjualan_cicilan.status', '=', 'hutang');
        })
        ->where(function ($q) use ($query) {
            $q->where('users.nomor_anggota', 'LIKE', "%{$query}%")
            ->orWhere('users.name', 'LIKE', "%{$query}%");
        })
        ->select(
            'users.id',
            'users.name',
            'users.nomor_anggota',
            'users.limit_hutang',
            DB::raw('SUM(penjualan_cicilan.pokok) as total_pokok')
        )
        ->groupBy('users.id', 'users.name', 'users.nomor_anggota', 'users.limit_hutang')
        ->get();
        // format data sesuai kebutuhan typeahead
        $formatted = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'nomor_anggota' => $user->nomor_anggota,
                'limit_hutang' => $user->limit_hutang - $user->total_pokok,
                'total_pokok' => $user->total_pokok,
            ];
        });

        return response()->json($formatted);
       // return response()->json($users);
    }
    public function getBarang(Request $request){
        $barang = StokUnit::join('barang','barang.id','stok_unit.barang_id')
        ->where('stok_unit.unit_id',Auth::user()->unit_kerja)
        ->whereRaw("CONCAT(barang.kode_barang, barang.nama_barang) LIKE ?", ["%{$request->q}%"])
        ->select('barang.id','barang.kode_barang as code','barang.nama_barang as text','stok_unit.stok','barang.harga_beli','barang.harga_jual')
        ->get();
        return response()->json($barang);
    }
    public function getBarangByCode(Request $request){
        $barang = StokUnit::join('barang','barang.id','stok_unit.barang_id')
        ->where("barang.kode_barang", "=",$request->kode)
        ->where("stok_unit.unit_id", "=",Auth::user()->unit_kerja)
        ->select('barang.id','barang.kode_barang as code','barang.nama_barang as text','stok_unit.stok','barang.harga_beli','barang.harga_jual')
        ->first();
        if($barang){
            return response()->json($barang);
        }else{
            return response()->json('error',404);
        }
        
    }
    public function SetApproval(Request $request){
        // if($request->jmlcicilan >= 1){
        //         $totalcicil = $request->grandtotal / $request->jmlcicilan;
        //         for ($i = $request->jmlcicilan; $i >= 1; $i--) {
        //             $cicil = new PenjualanCicil;
        //             $cicil->penjualan_id = $penjualan->id;
        //             $cicil->cicilan = $i;
        //             $cicil->total_cicilan = $totalcicil;
        //             $cicil->status = 'hutang';
        //             $cicil->save();
        //         }
        //     }
    }
    public function CekTanggungan(Request $request){
        $tanggungan = Penjualan::where(['anggota_id'=>$request->anggota,'status'=>'hutang','metode_bayar'=>'cicilan','approval3'=>1])->selectRaw('SUM(grandtotal) as total')->value('total');
        $usr = User::find($request->anggota);
        if(($tanggungan+$request->grandtotal) < $usr->limit_hutang){
            return response()->json(true,200);
        }else{
            return response()->json('Limit hutang melebihi total transaksi',400);
        }
    }
    public function Store(Request $request){
        DB::beginTransaction();
        try {
            $date = Carbon::parse($request->tanggal)->setTimeFrom(Carbon::now());
            $penjualan = new Penjualan;
            $penjualan->nomor_invoice = $this->genCode();
            $penjualan->tanggal = $date->toDateTimeString();
            $penjualan->grandtotal = $request->grandtotal;
            $penjualan->subtotal = $request->subtotal;
            $penjualan->metode_bayar = $request->metodebayar;
            $penjualan->unit_id = Auth::user()->unit_kerja;
            $penjualan->customer = $request->customer;
            $penjualan->anggota_id = $request->idcustomer;
            $penjualan->diskon = $request->diskon;
            $penjualan->note = $request->note;
            $penjualan->type_order = 'offline';
            
            if($request->metodebayar == 'cicilan'){
                $bunga = KonfigBunga::select('bunga_barang')->first();
                $penjualan->status = 'hutang';
                $penjualan->tenor = $request->jmlcicilan;
                $user = User::find($request->idcustomer);
                $result = DB::select("SELECT hitung_cicilan(?, ?, ?, ?) AS jumlah", [$request->grandtotal, $bunga->bunga_barang, $request->jmlcicilan, 1]);
                $cicilanpertama = $result[0]->jumlah;

                $totalcicilan = PenjualanCicil::where(['anggota_id'=>$request->idcustomer,'status'=>'hutang'])
                ->sum('total_cicilan');

                $batas = 0.35 * $user->gaji; // 35% dari gaji
                if (($totalcicilan+$cicilanpertama) > $batas) { //PR  hitung hutang yg masih aktif jika < $user->limit_hutang maka lolos
                    return response()->json('Tidak dapat diproses, Melebihi batas limit',500);
                    //return response()->json([$request->idcustomer,$totalcicilan,$cicilanpertama],500);
                }

                $penjualan->bunga_barang = $bunga->bunga_barang;
                $penjualan->status_ambil = 'pesan';
                $penjualan->kembali = 0;
                $penjualan->dibayar = 0;
            }elseif($request->metodebayar == 'tunai'){
                $penjualan->status = 'lunas';
                $penjualan->tenor = 0;
                $penjualan->status_ambil = 'finish';
                $penjualan->kembali = $request->kembali;
                $penjualan->dibayar = $request->dibayar;
            }
            $penjualan->created_user = Auth::user()->id;
            $penjualan->save();
            $no=0;
            
            foreach ($request->idbarang as $item) {
                $dtl = new PenjualanDetail;
                $dtl->penjualan_id = $penjualan->id;
                $dtl->barang_id = $item;
                $dtl->qty = $request->qty[$no];
                $dtl->harga = $request->harga_jual[$no];
                $dtl->save();

                StokUnit::where('unit_id',Auth::user()->unit_kerja)->where('barang_id',$item)->decrement('stok', $request->qty[$no]);
                $no++;
            }
            if($request->metodebayar == 'cicilan'){
                for ($i = 1; $i <= $request->jmlcicilan; $i++) {
                    $pokoktotal = DB::select("SELECT hitung_pokok(?, ?) AS jumlah", [$request->grandtotal, $request->jmlcicilan]);
                    $bungatotal = DB::select("SELECT hitung_bunga(?, ?, ?, ?) AS jumlah", [$request->grandtotal, $bunga->bunga_barang, $request->jmlcicilan, $i]);
                    $cicilan = new PenjualanCicil();
                    $cicilan->penjualan_id = $penjualan->id;
                    $cicilan->cicilan = $i;
                    $cicilan->anggota_id = $request->idcustomer;
                    $cicilan->pokok = $pokoktotal[0]->jumlah;
                    $cicilan->bunga = $bungatotal[0]->jumlah;
                    $cicilan->total_cicilan = $pokoktotal[0]->jumlah+$bungatotal[0]->jumlah;
                    $cicilan->status = 'hutang';
                    $cicilan->save();
                }
            }
            DB::commit();
            return response()->json(['message' => 'Order saved successfully.','invoice'=>$penjualan->nomor_invoice]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'error'   => 'Failed to save order',
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ], 500);
        }
    }

    public function RiwayatPenjualan(Request $request): View
    {
        $tanggalAwal = $request->tanggal_awal ?? date('Y-m-d');
        $tanggalAkhir = $request->tanggal_akhir ?? date('Y-m-d');

        $query = Penjualan::with('user')
            ->join('users', 'users.id', '=', 'penjualan.created_user')
            ->select('penjualan.*', 'users.name as kasir')
            ->whereDate('penjualan.tanggal', '>=', $tanggalAwal)
            ->whereDate('penjualan.tanggal', '<=', $tanggalAkhir)
            ->whereIn('penjualan.status', ['lunas', 'hutang'])
            ->orderBy('penjualan.tanggal', 'desc');

        if($request->anggota){
            $query->where('anggota_id', $request->anggota);
        }

        $penjualan = $query->get();

        return view('transaksi.RiwayatPenjualan', [
            'penjualan' => $penjualan,
            'tanggal_awal' => $tanggalAwal,
            'tanggal_akhir' => $tanggalAkhir,
            'anggota' => $request->anggota ?? ''
        ]);
    }

}
