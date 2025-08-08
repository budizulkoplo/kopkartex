<?php

namespace App\Http\Controllers;

use App\Models\StockOpnameDTL;
use App\Models\StockOpnameHDR;
use App\Models\StokUnit;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockOpnameController extends Controller
{
    public function index(Request $request): View
    {
        return view('transaksi.StockOpname');
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
            $date = Carbon::parse($request->tgl_opname);
            $data = [];

            foreach ($request->id as $index => $id) {
                $qty = $request->qty[$index];
                $exp = $request->exp[$index];
                $code = $request->code[$index];

                // Group berdasarkan ID dan EXP jika perlu
                $key = $id;

                if (!isset($data[$key])) {
                    $data[$key] = [
                        'code'=> $code,
                        'id' => $id,
                        'qty' => 0,
                    ];
                }

                $data[$key]['qty'] += $qty;
            }
            foreach ($data as $value) {
                $stoksys = StokUnit::where(['barang_id'=>$value['id'],'unit_id'=>Auth::user()->unit_kerja])->first();
                $hdr = new StockOpnameHDR();
                $hdr->id_unit = Auth::user()->unit_kerja;
                $hdr->id_barang = $value['id'];
                $hdr->kode_barang = $value['code'];
                $hdr->tgl_opname = $date->format('Y-m-d');
                $hdr->user = Auth::user()->id;
                $hdr->stock_sistem = $stoksys->stok;
                $hdr->stock_fisik = $value['qty'];
                $hdr->save();

                $idhdr = $value['id'];
                $qty = $request->qty;
                $exp = $request->exp;
                $id = $request->id;
                $datadtl = [];
                for ($i = 0; $i < count($id); $i++) {
                    $datadtl[] = [
                        'id' => $id[$i],
                        'qty' => $qty[$i],
                        'exp' => $exp[$i],
                    ];
                }
                $filtered = array_filter($datadtl, function ($item) use($idhdr) {
                    return $item['id'] == $idhdr;
                });
                foreach ($filtered as $item) {
                    $dtl = new StockOpnameDTL();
                    $dtl->opnameid = $hdr->id;
                    $dtl->id_barang = $item['id'];
                    $dtl->qty = $item['qty'];
                    $dtl->expired_date = $item['exp'];
                    $dtl->save();
                }
            }
             
            
            DB::commit();
            return response()->json(['message' => 'Order saved successfully.']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to save order', 'message' => $e->getMessage()], 500);
        }
    }
}
