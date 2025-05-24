<?php

namespace App\Http\Controllers;

use App\Models\MutasiStok;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class MutasiStockController extends Controller
{
    public function index(Request $request): View
    {
        return view('transaksi.MutasiStokList', [
            // 'roles' => Role::with('permissions')->get(),
            // 'allroles' => Role::all(),
            //'unit' => Unit::all(),
        ]);
    }
    public function FormMutasi(Request $request): View
    {
        return view('transaksi.MutasiStok', [
            // 'roles' => Role::with('permissions')->get(),
            // 'allroles' => Role::all(),
            'unit' => Unit::all(),
        ]);
    }
    public function GetData(Request $request){
        $barang = MutasiStok::join('users','users.id','mutasi_stok.created_user')
        ->join('unit as unit1','unit1.id','mutasi_stok.dari_unit')
        ->join('unit as unit2','unit2.id','mutasi_stok.ke_unit')
        ->whereBetween('mutasi_stok.tanggal', [$request->startdate, $request->enddate])
        ->select('mutasi_stok.id','mutasi_stok.tanggal','mutasi_stok.status','users.name as petugas','unit1.nama_unit as NamaUnit1','unit2.nama_unit as NamaUnit2');
        return DataTables::of($barang)
        ->addIndexColumn()
        ->filter(function ($query) use ($request) {
            if ($request->has('search') && $request->search != '') {
                $query->where(function ($query2) use($request) {
                    return $query2
                    ->orWhere('users.name','like','%'.$request->search['value'].'%');
                }); 
            }
        })
        ->editColumn('id', function($query) {
            return Crypt::encryptString($query->id);        })
        ->make(true);
    }
}
