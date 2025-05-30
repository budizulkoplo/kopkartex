<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Satuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class BarangController extends Controller
{
    public function index(Request $request): View
    {
        return view('master.barang.list', [
            'satuan' => Satuan::orderBy('name')->get(),
            'kategori' => Kategori::orderBy('name')->get(),
        ]);
    }
    public function getdata(Request $request){
        $barang = Barang::query();
        if($request->kategori != 'all'){$barang->where('kategori',$request->kategori);}
        return DataTables::of($barang)
        ->addIndexColumn()
        ->filter(function ($query) use ($request) {
            if ($request->has('search') && $request->search != '') {
                $query->where(function ($query2) use($request) {
                    return $query2
                    ->orWhere('nama_barang','like','%'.$request->search['value'].'%')
                    ->orWhere('kode_barang','like','%'.$request->search['value'].'%');
                }); 
            }
        })
        ->editColumn('id', function($query) {
            return Crypt::encryptString($query->id);        })
        ->make(true);
    }
    function genCode(){
        $total = Barang::withTrashed()->whereDate('created_at', date("Y-m-d"))->count();
        $nomorUrut = $total + 1;
        $newcode='BRG-'.date("ymd").str_pad($nomorUrut, 3, '0', STR_PAD_LEFT);
        return $newcode;
    }
    public function getCode(){
        return response()->json($this->genCode(), 200);
    }
    public function CekCode(Request $request){
        $barang = Barang::where('kode_barang',$request->code)->count();
        return response()->json($barang, 200);
    }
    public function Store(Request $request){
        $validatedData = $request->validate([
            'nama_barang' => 'required'
        ]);
        if($validatedData){
            if(!empty($request->idbarang)){
                $id=Crypt::decryptString($request->idbarang);
                $barang = Barang::find($id);
            }else{
                $cnbarang = Barang::where('kode_barang',$request->kode_barang)->count();
                if($cnbarang>0)
                return response()->json('Kode sudah terpakai', 500);

                $barang = new Barang;
                $barang->kode_barang = $request->kode_barang;
            }
            $barang->nama_barang = $request->nama_barang;
            $barang->harga_beli = $request->harga_beli;
            $barang->harga_jual = $request->harga_jual;
            $barang->kategori = $request->kategori;
            $barang->satuan = $request->satuan;
            $barang->save();

            if($barang){
                return response()->json('success', 200);
            }else{
                return response()->json('gagal', 500);
            }
        }
    }
    public function Hapus(Request $request)
    {
        $id=Crypt::decryptString($request->id);
        $barang = Barang::find($id);
        $barang->delete();
        if($barang){
            return response()->json('success', 200);
        }else{
            return response()->json('gagal', 500);
        }
    }
}
