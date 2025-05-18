<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PenerimaanController extends Controller
{
    public function index(Request $request): View
    {
        return view('transaksi.penerimaan', [
            // 'roles' => Role::with('permissions')->get(),
            // 'allroles' => Role::all(),
            // 'unit' => Unit::all(),
        ]);
    }
    public function getBarang(Request $request){
        $barang = Barang::whereRaw("CONCAT(kode_barang, nama_barang) LIKE ?", ["%{$request->q}%"])
        ->select('kode_barang as id','nama_barang as text')
        ->get();
        return response()->json($barang);
    }
}
