<?php

namespace App\Http\Controllers;

use App\Models\KonfigBunga;
use App\Models\Penjualan;
use App\Models\PenjualanCicil;
use App\Models\PenjualanDetail;
use App\Models\StokUnit;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class AmbilBarangController extends Controller
{
    public function index(Request $request): View
    {
        return view('transaksi.AmbilBarang', []);
    }
    public function getPenjualan(Request $request)
    {
        $jual = Penjualan::where(['type_order'=>'mobile','unit_id'=>Auth::user()->unit_kerja])->whereBetween(DB::raw('DATE(tanggal)'), [$request->startdate, $request->enddate]);
        if($request->status != 'all'){
            $jual->where('status_ambil',$request->status);
        }
        return DataTables::of($jual)->addIndexColumn()->make(true);
        
    }
    public function DeleteItem(Request $request){
        $dtl = PenjualanDetail::find($request->id);
        $dtl->delete();
        DB::statement("CALL RecalcPenjualan(?)", [$dtl->penjualan_id]);
        if($request->retur){
            $jual = Penjualan::find($dtl->penjualan_id);
            StokUnit::where('unit_id', $jual->unit_id)
            ->where('barang_id', $dtl->barang_id)
            ->increment('stok', $dtl->qty);
            if($jual->metode_bayar=='cicilan'){
                PenjualanCicil::where('penjualan_id', $jual->id)->forceDelete();
                for ($i = 1; $i <= $jual->tenor; $i++) {
                    $pokoktotal = DB::select("SELECT hitung_pokok(?, ?) AS jumlah", [$jual->grandtotal, $jual->tenor]);
                    $bungatotal = DB::select("SELECT hitung_bunga(?, ?, ?, ?) AS jumlah", [$jual->grandtotal, $jual->bunga_barang, $jual->tenor, $i]);
                    $cicilan = new PenjualanCicil();
                    $cicilan->penjualan_id = $jual->id;
                    $cicilan->cicilan = $i;
                    $cicilan->anggota_id = $jual->anggota_id;
                    $cicilan->pokok = $pokoktotal[0]->jumlah;
                    $cicilan->bunga = $bungatotal[0]->jumlah;
                    $cicilan->total_cicilan = $pokoktotal[0]->jumlah+$bungatotal[0]->jumlah;
                    $cicilan->status = 'hutang';
                    $cicilan->save();
                }
            }
        }
        return response()->json('success', 200);
    }
    public function getPenjualanDtl($idjual)
    {
        $hdr = Penjualan::find($idjual);
        $cicilan = PenjualanCicil::where(['penjualan_id'=>$hdr->id,'status'=>'linas'])->count();
        $dtl = PenjualanDetail::join('barang','barang.id','penjualan_detail.barang_id')
        ->where(['penjualan_detail.penjualan_id'=>$idjual])
        ->select('penjualan_detail.*', 'barang.nama_barang', 'barang.kode_barang')->get();
        return response()->json(['hdr'=>$hdr,'dtl'=>$dtl,'cicilanlunas'=> $cicilan], 200);
        
    }
    public function AmbilBarang(Request $request)
    {
        $request->validate(['id' => 'required']);
        DB::beginTransaction();
        try {
            $jual = Penjualan::find($request->id);
            $jual->status_ambil = $request->status;
            
            if($request->status == 'finish'){
                $jual->ambil_at = now();
                $jual->metode_bayar = $request->metode;
                
                if($request->metode == 'cicilan'){
                    // Ambil bunga konfigurasi (walaupun untuk toko selalu 0)
                    $bunga = KonfigBunga::select('bunga_barang')->first();
                    $jual->status = 'hutang';
                    $jual->tenor = $request->jmlcicilan;
                    $user = User::find($jual->anggota_id);
                    
                    // Hitung total per kategori dari detail penjualan
                    $detail = PenjualanDetail::where('penjualan_id', $request->id)->get();
                    $totalCicilan0 = 0;
                    $totalCicilan1 = 0;
                    
                    foreach ($detail as $item) {
                        $barang = DB::table('barang')
                            ->join('kategori', 'kategori.id', '=', 'barang.idkategori')
                            ->where('barang.id', $item->barang_id)
                            ->select('kategori.cicilan')
                            ->first();
                        
                        if($barang) {
                            $subtotal = $item->qty * $item->harga;
                            if($barang->cicilan == 0) {
                                $totalCicilan0 += $subtotal;
                            } else {
                                $totalCicilan1 += $subtotal;
                            }
                        }
                    }
                    
                    // Hitung cicilan pertama untuk kategori 1 menggunakan fungsi toko
                    $cicilanpertamaKategori1 = 0;
                    if($totalCicilan1 > 0 && $request->jmlcicilan > 0) {
                        $result = DB::select("SELECT hitung_cicilan_toko(?, ?, ?, ?) AS jumlah", [
                            $totalCicilan1, 
                            $bunga->bunga_barang, 
                            $request->jmlcicilan, 
                            1
                        ]);
                        $cicilanpertamaKategori1 = $result[0]->jumlah;
                    }
                    
                    $cicilanpertama = $cicilanpertamaKategori1 + $totalCicilan0; // Tambahkan cicilan 0

                    // Hitung total cicilan yang masih aktif (hutang)
                    $totalcicilan = PenjualanCicil::where(['anggota_id' => $jual->anggota_id, 'status' => 'hutang'])
                        ->sum('total_cicilan');

                    // Cek limit hutang
                    if (!empty($user->limit_hutang) && $user->limit_hutang > 0) {
                        $batas = $user->limit_hutang;
                    } else {
                        $batas = 0.35 * $user->gaji;
                    }
                    
                    if (($totalcicilan + $cicilanpertama) > $batas) {
                        DB::rollBack();
                        return response()->json('Tidak dapat diproses, Melebihi batas limit', 500);
                    }

                    // Untuk toko selalu tanpa bunga
                    $jual->bunga_barang = 0;
                    $jual->kembali = 0;
                    $jual->dibayar = 0;

                    // Hapus cicilan lama jika ada (untuk keamanan)
                    PenjualanCicil::where('penjualan_id', $jual->id)->delete();
                    
                    // Buat cicilan untuk kategori 0 (hanya 1 cicilan)
                    if($totalCicilan0 > 0) {
                        $cicilan0 = new PenjualanCicil();
                        $cicilan0->penjualan_id = $jual->id;
                        $cicilan0->cicilan = 1;
                        $cicilan0->anggota_id = $jual->anggota_id;
                        $cicilan0->pokok = $totalCicilan0;
                        $cicilan0->bunga = 0;
                        $cicilan0->total_cicilan = $totalCicilan0;
                        $cicilan0->status = 'hutang';
                        $cicilan0->kategori = 0;
                        $cicilan0->save();
                    }
                    
                    // Buat cicilan untuk kategori 1 (sesuai jumlah cicilan) menggunakan fungsi toko
                    if($totalCicilan1 > 0 && $request->jmlcicilan > 0) {
                        for ($i = 1; $i <= $request->jmlcicilan; $i++) {
                            $result = DB::select("SELECT hitung_cicilan_toko(?, ?, ?, ?) AS jumlah", [
                                $totalCicilan1, 
                                $bunga->bunga_barang, 
                                $request->jmlcicilan, 
                                $i
                            ]);
                            
                            $cicilan = new PenjualanCicil();
                            $cicilan->penjualan_id = $jual->id;
                            $cicilan->cicilan = $i;
                            $cicilan->anggota_id = $jual->anggota_id;
                            $cicilan->pokok = $result[0]->jumlah; // Total cicilan = pokok karena tanpa bunga
                            $cicilan->bunga = 0;
                            $cicilan->total_cicilan = $result[0]->jumlah;
                            $cicilan->status = 'hutang';
                            $cicilan->kategori = 1;
                            $cicilan->save();
                        }
                    }
                } else {
                    $jual->status = 'lunas';
                    $jual->kembali = $request->kembalian;
                    $jual->dibayar = $request->dibayar;
                    
                    // Hapus cicilan jika ada (jika sebelumnya cicilan lalu diubah menjadi tunai)
                    PenjualanCicil::where('penjualan_id', $jual->id)->delete();
                }
                
                // Kurangi stok hanya jika status_ambil = 'finish'
                $detail = PenjualanDetail::where('penjualan_id', $request->id)->get();
                foreach ($detail as $value) {
                    StokUnit::where('unit_id', $jual->unit_id)
                        ->where('barang_id', $value->barang_id)
                        ->decrement('stok', $value->qty);
                }
            }
            
            $jual->save();      
            
            DB::commit();
            // Kembalikan nomor invoice untuk cetak nota
            return response()->json(['invoice' => $jual->nomor_invoice], 200);
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
