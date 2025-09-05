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
        PenjualanDetail::find($request->id)->delete();
        DB::statement("CALL RecalcPenjualan(?)", [$request->penjualan]);
        return response()->json('success', 200);
    }
    public function getPenjualanDtl($idjual)
    {
        $hdr = Penjualan::find($idjual);
        $dtl = PenjualanDetail::join('barang','barang.id','penjualan_detail.barang_id')
        ->where(['penjualan_detail.penjualan_id'=>$idjual])
        ->select('penjualan_detail.*', 'barang.nama_barang', 'barang.kode_barang')->get();
        return response()->json(['hdr'=>$hdr,'dtl'=>$dtl], 200);
        
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
                    $bunga = KonfigBunga::select('bunga_barang')->first();
                    $jual->status = 'hutang';
                    $jual->tenor = $request->jmlcicilan;
                    $user = User::find($jual->anggota_id);
                    $result = DB::select("SELECT hitung_cicilan(?, ?, ?, ?) AS jumlah", [$jual->grandtotal, $bunga->bunga_barang, $request->jmlcicilan, 1]);
                    $cicilanpertama = $result[0]->jumlah;

                    $totalcicilan = PenjualanCicil::where(['anggota_id'=>$jual->anggota_id,'status'=>'hutang'])
                    ->sum('total_cicilan');

                    $batas = 0.35 * $user->gaji; // 35% dari gaji
                    if (($totalcicilan+$cicilanpertama) > $batas) { //PR  hitung hutang yg masih aktif jika < $user->limit_hutang maka lolos
                        DB::rollBack();
                        return response()->json('Tidak dapat diproses, Melebihi batas limit',500);
                        //return response()->json([$request->idcustomer,$totalcicilan,$cicilanpertama],500);
                    }

                    $jual->bunga_barang = $bunga->bunga_barang;
                    $jual->kembali = 0;
                    $jual->dibayar = 0;

                    for ($i = 1; $i <= $request->jmlcicilan; $i++) {
                        $pokoktotal = DB::select("SELECT hitung_pokok(?, ?) AS jumlah", [$jual->grandtotal, $request->jmlcicilan]);
                        $bungatotal = DB::select("SELECT hitung_bunga(?, ?, ?, ?) AS jumlah", [$jual->grandtotal, $bunga->bunga_barang, $request->jmlcicilan, $i]);
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
                }else{
                    $jual->status = 'lunas';
                    $jual->kembali = $request->kembalian;
                    $jual->dibayar = $request->dibayar;
                }
            }
            $jual->save();      
            
            $detail = PenjualanDetail::where('penjualan_id',$request->id)->get();
            foreach ($detail as $value) {
                StokUnit::where('unit_id',$jual->unit_id)
                    ->where('barang_id',$value->barang_id)
                    ->decrement('stok', $value->qty);
            }
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
