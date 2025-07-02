<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PpobController extends Controller
{
    public function index()
    {
        return view('transaksi.ppob');
    }

    public function transaksi(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date_format:d-m-Y',
            'kategori' => 'required|string',
            'id_pelanggan' => 'required|string',
            'nominal' => 'required|numeric|min:1000',
            'admin' => 'required|numeric|min:0',
        ]);

        // Simpan transaksi atau kirim ke API PPOB
        // Contoh simpan:
        // PpobTransaction::create([...]);

        return response()->json(['status' => 'success']);
    }
}
