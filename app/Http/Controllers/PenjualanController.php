<?php

namespace App\Http\Controllers;

use App\Models\KonfigBunga;
use App\Models\Penjualan;
use App\Models\PenjualanCicil;
use App\Models\PenjualanDetail;
use App\Models\StokUnit;
use App\Models\Unit;
use App\Models\User;
use App\Models\Kategori;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PenjualanController extends Controller
{
    public function index(Request $request): View
    {
        return view('transaksi.Penjualan', [
            'invoice' => $this->genCode(),
            'unit' => Unit::find(Auth::user()->unit_kerja),
        ]);
    }

    // New method for Umum
    public function indexUmum(Request $request): View
    {
        return view('transaksi.PenjualanUmum', [
            'invoice' => $this->genCodeUmum(),
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
            ->join('kategori','kategori.id','barang.idkategori')
            ->select(
                'barang.nama_barang',
                'barang.kode_barang',
                'penjualan_detail.qty',
                'penjualan_detail.harga',
                'kategori.cicilan as kategori_cicilan'
            )
            ->where('penjualan_id',$hdr->id)
            ->get();

        if ($hdr->metode_bayar == 'cicilan') {
            // Ambil semua cicilan untuk transaksi ini dengan perhitungan baru
            $cicilan = PenjualanCicil::where('penjualan_id', $hdr->id)
                        ->orderBy('cicilan','asc')
                        ->get();

            // Kelompokkan item berdasarkan kategori cicilan
            $itemsCicilan0 = $dtl->where('kategori_cicilan', 0);
            $itemsCicilan1 = $dtl->where('kategori_cicilan', 1);

            return view('transaksi.PenjualanNotaCicilan', [
                'hdr'           => $hdr,
                'dtl'           => $dtl,
                'cicilan'       => $cicilan,
                'itemsCicilan0' => $itemsCicilan0,
                'itemsCicilan1' => $itemsCicilan1
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

    function genCodeUmum(){
        $total = Penjualan::withTrashed()->whereDate('created_at', date("Y-m-d"))->count();
        $nomorUrut = $total + 1;
        $newcode='UM-'.date("ymd").str_pad($nomorUrut, 3, '0', STR_PAD_LEFT);
        return $newcode;
    }

    public function getInvoice(){
        return response()->json($this->genCode());
    }

    public function getInvoiceUmum(){
        return response()->json($this->genCodeUmum());
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
    }

    public function getBarang(Request $request)
    {
        $barang = StokUnit::join('barang', 'barang.id', '=', 'stok_unit.barang_id')
            ->join('kategori', 'kategori.id', '=', 'barang.idkategori')
            ->where('stok_unit.unit_id', Auth::user()->unit_kerja)
            ->whereRaw(
                "CONCAT(barang.kode_barang, ' ', barang.nama_barang) LIKE ?",
                ["%{$request->q}%"]
            )
            ->select(
                'barang.id',
                'barang.kode_barang as code',
                DB::raw("CONCAT(barang.kode_barang, ' | ', barang.nama_barang , ' | ', barang.type) as text"),
                'barang.nama_barang',
                'stok_unit.stok',
                'barang.harga_beli',
                'barang.harga_jual',
                'barang.harga_jual_umum',
                'kategori.cicilan as kategori_cicilan'
            )
            ->get();

        return response()->json($barang);
    }

    public function getBarangUmum(Request $request){
        $barang = StokUnit::join('barang','barang.id','stok_unit.barang_id')
        ->join('kategori','kategori.id','barang.idkategori')
        ->where('stok_unit.unit_id',Auth::user()->unit_kerja)
        ->whereRaw("CONCAT(barang.kode_barang, barang.nama_barang) LIKE ?", ["%{$request->q}%"])
        ->select(
            'barang.id',
            'barang.kode_barang as code',
            'barang.nama_barang as text',
            'stok_unit.stok',
            'barang.harga_beli',
            'barang.harga_jual_umum as harga_jual', // Gunakan harga_jual_umum
            'barang.harga_jual_umum',
            'kategori.cicilan as kategori_cicilan'
        )
        ->get();
        return response()->json($barang);
    }

    public function getBarangByCode(Request $request){
        $barang = StokUnit::join('barang','barang.id','stok_unit.barang_id')
        ->join('kategori','kategori.id','barang.idkategori')
        ->where("barang.kode_barang", "=",$request->kode)
        ->where("stok_unit.unit_id", "=",Auth::user()->unit_kerja)
        ->select(
            'barang.id',
            'barang.kode_barang as code',
            'barang.nama_barang as text',
            'stok_unit.stok',
            'barang.harga_beli',
            'barang.harga_jual',
            'barang.harga_jual_umum',
            'kategori.cicilan as kategori_cicilan'
        )
        ->first();
        
        if($barang){
            return response()->json($barang);
        }else{
            return response()->json('error',404);
        }
    }

    public function getBarangByCodeUmum(Request $request){
        $barang = StokUnit::join('barang','barang.id','stok_unit.barang_id')
        ->join('kategori','kategori.id','barang.idkategori')
        ->where("barang.kode_barang", "=",$request->kode)
        ->where("stok_unit.unit_id", "=",Auth::user()->unit_kerja)
        ->select(
            'barang.id',
            'barang.kode_barang as code',
            'barang.nama_barang as text',
            'stok_unit.stok',
            'barang.harga_beli',
            'barang.harga_jual_umum as harga_jual', // Gunakan harga_jual_umum
            'barang.harga_jual_umum',
            'kategori.cicilan as kategori_cicilan'
        )
        ->first();
        
        if($barang){
            return response()->json($barang);
        }else{
            return response()->json('error',404);
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
                
                // Ambil data barang untuk cek kategori
                $barangIds = $request->idbarang;
                $qtys = $request->qty;
                $hargas = $request->harga_jual;
                
                // Hitung total per kategori
                $totalCicilan0 = 0;
                $totalCicilan1 = 0;
                
                for($i = 0; $i < count($barangIds); $i++) {
                    $barang = DB::table('barang')
                        ->join('kategori', 'kategori.id', '=', 'barang.idkategori')
                        ->where('barang.id', $barangIds[$i])
                        ->select('kategori.cicilan')
                        ->first();
                    
                    if($barang) {
                        $subtotal = $qtys[$i] * $hargas[$i];
                        if($barang->cicilan == 0) {
                            $totalCicilan0 += $subtotal;
                        } else {
                            $totalCicilan1 += $subtotal;
                        }
                    }
                }

                $result = DB::select("SELECT hitung_cicilan(?, ?, ?, ?) AS jumlah", [$totalCicilan1, $bunga->bunga_barang, $request->jmlcicilan, 1]);
                $cicilanpertama = $result[0]->jumlah + $totalCicilan0; // Tambahkan cicilan 0

                $totalcicilan = PenjualanCicil::where(['anggota_id'=>$request->idcustomer,'status'=>'hutang'])
                ->sum('total_cicilan');

                if (!empty($user->limit_hutang) && $user->limit_hutang > 0) {
                    $batas = $user->limit_hutang;
                } else {
                    $batas = 0.35 * $user->gaji;
                }
                
                if (($totalcicilan+$cicilanpertama) > $batas) {
                    return response()->json('Tidak dapat diproses, Melebihi batas limit',500);
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
                // Buat cicilan untuk kategori 0 (hanya 1 cicilan)
                if($totalCicilan0 > 0) {
                    $cicilan0 = new PenjualanCicil();
                    $cicilan0->penjualan_id = $penjualan->id;
                    $cicilan0->cicilan = 1;
                    $cicilan0->anggota_id = $request->idcustomer;
                    $cicilan0->pokok = $totalCicilan0;
                    $cicilan0->bunga = 0;
                    $cicilan0->total_cicilan = $totalCicilan0;
                    $cicilan0->status = 'hutang';
                    $cicilan0->kategori = 0;
                    $cicilan0->save();
                }
                
                // Buat cicilan untuk kategori 1 (sesuai jumlah cicilan)
                if($totalCicilan1 > 0 && $request->jmlcicilan > 0) {
                    for ($i = 1; $i <= $request->jmlcicilan; $i++) {
                        $pokoktotal = DB::select("SELECT hitung_pokok(?, ?) AS jumlah", [$totalCicilan1, $request->jmlcicilan]);
                        $bungatotal = DB::select("SELECT hitung_bunga(?, ?, ?, ?) AS jumlah", [$totalCicilan1, $bunga->bunga_barang, $request->jmlcicilan, $i]);
                        $cicilan = new PenjualanCicil();
                        $cicilan->penjualan_id = $penjualan->id;
                        $cicilan->cicilan = $i;
                        $cicilan->anggota_id = $request->idcustomer;
                        $cicilan->pokok = $pokoktotal[0]->jumlah;
                        $cicilan->bunga = $bungatotal[0]->jumlah;
                        $cicilan->total_cicilan = $pokoktotal[0]->jumlah + $bungatotal[0]->jumlah;
                        $cicilan->status = 'hutang';
                        $cicilan->kategori = 1;
                        $cicilan->save();
                    }
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

    // Store untuk penjualan umum
    public function StoreUmum(Request $request){
        DB::beginTransaction();
        try {
            $date = Carbon::parse($request->tanggal)->setTimeFrom(Carbon::now());
            $penjualan = new Penjualan;
            $penjualan->nomor_invoice = $this->genCodeUmum();
            $penjualan->tanggal = $date->toDateTimeString();
            $penjualan->grandtotal = $request->grandtotal;
            $penjualan->subtotal = $request->subtotal;
            $penjualan->metode_bayar = 'tunai'; // Selalu tunai untuk umum
            $penjualan->unit_id = Auth::user()->unit_kerja;
            $penjualan->customer = $request->customer;
            $penjualan->anggota_id = null;
            $penjualan->diskon = $request->diskon;
            $penjualan->note = $request->note;
            $penjualan->type_order = 'umum';
            $penjualan->status = 'lunas';
            $penjualan->tenor = 0;
            $penjualan->status_ambil = 'finish';
            $penjualan->kembali = $request->kembali;
            $penjualan->dibayar = $request->dibayar;
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

    public function getDetail($id)
    {
        try {
            $penjualan = Penjualan::findOrFail($id);
            
            // Pastikan hanya bisa mengakses data dari unit yang sama
            if ($penjualan->unit_id != Auth::user()->unit_kerja) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            
            $detail = PenjualanDetail::join('barang', 'barang.id', 'penjualan_detail.barang_id')
                ->where('penjualan_id', $id)
                ->select(
                    'penjualan_detail.id',
                    'penjualan_detail.barang_id',
                    'barang.kode_barang',
                    'barang.nama_barang',
                    'penjualan_detail.qty',
                    'penjualan_detail.harga'
                )
                ->get();
            
            return response()->json([
                'success' => true,
                'detail' => $detail
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail penjualan'
            ], 500);
        }
    }
    
    /**
     * Proses retur penjualan
     */
    public function prosesRetur(Request $request)
    {
        DB::beginTransaction();
        try {
            $penjualanId = $request->penjualan_id;
            $items = $request->items;
            
            $penjualan = Penjualan::findOrFail($penjualanId);
            
            // Pastikan hanya bisa mengakses data dari unit yang sama
            if ($penjualan->unit_id != Auth::user()->unit_kerja) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            
            foreach ($items as $item) {
                $detail = PenjualanDetail::findOrFail($item['id']);
                
                // Kembalikan stok
                StokUnit::where('unit_id', $penjualan->unit_id)
                    ->where('barang_id', $item['barang_id'])
                    ->increment('stok', $item['qty']);
                
                // Hapus item dari penjualan
                $detail->delete();
                
                // Catat retur dalam log atau tabel khusus retur
                // Anda bisa membuat tabel retur_penjualan untuk mencatat history retur
                DB::table('retur_penjualan')->insert([
                    'penjualan_id' => $penjualanId,
                    'barang_id' => $item['barang_id'],
                    'qty' => $item['qty'],
                    'harga' => $detail->harga,
                    'created_at' => now(),
                    'created_user' => Auth::id()
                ]);
            }
            
            // Recalculate grand total
            $newTotal = PenjualanDetail::where('penjualan_id', $penjualanId)
                ->select(DB::raw('SUM(qty * harga) as total'))
                ->value('total');
            
            // Update penjualan
            $penjualan->grandtotal = $newTotal;
            $penjualan->subtotal = $newTotal;
            
            // Jika semua item di-retur, ubah status penjualan
            $remainingItems = PenjualanDetail::where('penjualan_id', $penjualanId)->count();
            if ($remainingItems === 0) {
                $penjualan->status = 'diretur';
            }
            
            $penjualan->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Retur berhasil diproses'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses retur: ' . $e->getMessage()
            ], 500);
        }
    }

}
