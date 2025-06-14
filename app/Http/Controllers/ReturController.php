<?php

namespace App\Http\Controllers;

use App\Models\ReturBarang;
use App\Models\ReturBarangDetail;
use App\Models\StokUnit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Exception;

class ReturController extends Controller
{
    public function index(Request $request): View
    {
        return view('transaksi.ReturBarang', [
            // 'roles' => Role::with('permissions')->get(),
            // 'allroles' => Role::all(),
            // 'unit' => Unit::all(),
        ]);
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
    public function Store(Request $request){
        DB::beginTransaction();
        try {
            $date = Carbon::parse($request->tgl_retur);
            $hdr = new ReturBarang;
            $hdr->nama_supplier = $request->nama_supplier;
            $hdr->note = $request->note;
            $hdr->tgl_retur = $date->format('Y-m-d');
            $hdr->unit_id = Auth::user()->unit_kerja;
            $hdr->created_user = Auth::user()->id;
            $hdr->save();
            $no=0;
            $quantities = $request->input('qty');
            $barang = $request->input('id');
            foreach ($request->id  as $index => $item) {
                $dtl = new ReturBarangDetail;
                $dtl->idretur = $hdr->id;
                $dtl->barang_id = $barang[$index];;
                $dtl->qty = $quantities[$index];
                $dtl->save();

                StokUnit::where('unit_id',Auth::user()->unit_kerja)->where('barang_id',$item)->decrement('stok', $quantities[$index]);
                $no++;
            }
            DB::commit();
            return response()->json(['message' => 'Order saved successfully.']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to save order', 'message' => $e->getMessage()], 500);
        }
    }
}
