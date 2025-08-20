<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ApprovalController extends Controller
{
    public function index(Request $request): View
    {
        return view('transaksi.Approval', []);
    }
    public function getHutang(Request $request)
    {
        $jual = Penjualan::where(['metode_bayar'=>'cicilan'])
        ->whereBetween(DB::raw('DATE(tanggal)'), [$request->startdate, $request->enddate]);
        if(Auth::user()->hasRole('hrd')){
            $jual->where('VarCicilan',1);
        }
        if(Auth::user()->hasRole('admin')){
            $jual->where('unit_id',Auth::user()->unit_kerja);
        }
        return DataTables::of($jual)->addIndexColumn()->make(true);
        
    }
    public function setapproval(Request $request){
        $code=$request->code;
        $cek=Penjualan::find($code);

        if($cek->VarCicilan === 0){
            if($request->fld == 'approval1' && $cek->approval3 == 1)
            return response()->json(['error' => true,'message' => 'Dokumen sudah disetujui Pengurus!',], 422);
            if($request->fld == 'approval3' && $cek->approval1 == 0)
            return response()->json(['error' => true,'message' => 'Menunggu persetujuan Petugas!',], 422);
        }
        if($cek->VarCicilan === 1){
            if($request->fld == 'approval1' && $cek->approval2 == 1)
            return response()->json(['error' => true,'message' => 'Dokumen sudah disetujui HRD!',], 422);
            if(($request->fld == 'approval2' || $request->fld == 'approval3') && $cek->approval1 == 0)
            return response()->json(['error' => true,'message' => 'Menunggu persetujuan Petugas!',], 422);
            if($request->fld == 'approval3' && $cek->approval2 == 0)
            return response()->json(['error' => true,'message' => 'Menunggu persetujuan HRD!',], 422);
        }

        $update=Penjualan::where('id', $code)->update([
            $request->fld =>$request->chk ,
            $request->fld.'_at' =>now() ,
            $request->fld.'_user' => Auth::user()->id,
        ]);
        if($update){
        return response()->json(true);
        }else{
        return response()->json(false);}
    }
}
