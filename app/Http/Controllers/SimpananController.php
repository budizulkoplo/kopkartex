<?php

namespace App\Http\Controllers;

use App\Models\Simpan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class SimpananController extends Controller
{
    public function index(Request $request): View
    {
        return view('transaksi.simpanan', [
            // 'roles' => Role::with('permissions')->get(),
            // 'allroles' => Role::all(),
            // 'unit' => Unit::all(),
        ]);
    }
    public function getdata(Request $request){
        $barang = Simpan::join('users','simpan.anggota_id','users.id')
        ->select('simpan.*','users.name','users.id as idusers','users.nik','users.nomor_anggota');
        //if($request->kategori != 'all'){$barang->where('kategori',$request->kategori);}
        return DataTables::of($barang)
        ->addIndexColumn()
        ->filter(function ($query) use ($request) {
            if ($request->has('search') && $request->search != '') {
                $query->where(function ($query2) use($request) {
                    return $query2
                    ->orWhere('users.name','like','%'.$request->search['value'].'%')
                    ->orWhere('users.username','like','%'.$request->search['value'].'%')
                    ->orWhere('users.nomor_anggota','like','%'.$request->search['value'].'%')
                    ->orWhere('users.nik','like','%'.$request->search['value'].'%');
                }); 
            }
        })
        ->editColumn('simpan.id', function($query) {
            return Crypt::encryptString($query->id);        })
        ->make(true);
    }
}
