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
use Illuminate\View\View;

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
        $hdr=Penjualan::join('users','users.id','penjualan.created_user')
        ->select('penjualan.*','users.name as kasir')
        ->where('penjualan.nomor_invoice',$invoice)->first();
        $dtl=PenjualanDetail::join('barang','barang.id','penjualan_detail.barang_id')
        ->select('barang.nama_barang','barang.kode_barang','penjualan_detail.qty','penjualan_detail.harga')
        ->where('penjualan_id',$hdr->id)->get();
        return view('transaksi.PenjualanNota', [
            'hdr' => $hdr,
            'dtl' => $dtl,
        ]);
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

        $users = User::where('name', 'LIKE', "%{$query}%")
                    ->select('id', 'name')
                    ->get();

        return response()->json($users);
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

                $batas = 0.35 * $user->gaji; // 35% dari gaji
                if ($cicilanpertama < $batas) { //PR  hitung hutang yg masih aktif jika < $user->limit_hutang maka lolos
                    $penjualan->VarCicilan = 0; //Cicilan memenuhi syarat (di bawah 35% gaji)
                } else {
                    $penjualan->VarCicilan = 1; //Cicilan terlalu besar (melebihi 35% gaji)
                }

                $penjualan->bunga_barang = $bunga->bunga_barang;
                $penjualan->status_ambil = 'pending';
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
}
