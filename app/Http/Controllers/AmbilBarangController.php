<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\StokUnit;
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
        
        $jual = Penjualan::find($request->id);
        $jual->status_ambil = $request->status;
        if($request->status == 'finish'){
            $jual->ambil_at = now();
        }
        $jual->save();      
        
        $detail = PenjualanDetail::where('penjualan_id',$request->id)->get();
        foreach ($detail as $value) {
            StokUnit::where('unit_id',$jual->unit_id)
                ->where('barang_id',$value->barang_id)
                ->decrement('stok', $value->qty);
        }

        // Kembalikan nomor invoice untuk cetak nota
        return response()->json(['invoice' => $jual->nomor_invoice], 200);
    }

}
