<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;

class KategoriBengkelController extends Controller
{
    public function index()
    {
        $data = Kategori::where('isbengkel', '1')->get();
        return view('master.kategori_bengkel.index', compact('data'));
    }

    public function edit($id)
    {
        $kategori = Kategori::findOrFail($id);
        return view('master.kategori_bengkel.edit', compact('kategori'));
    }

    public function update(Request $request, $id)
    {
        $kategori = Kategori::findOrFail($id);

        $kategori->update([
            'cicilan' => $request->has('cicilan') ? '1' : '0'
        ]);

        return redirect()->route('kategori.bengkel.index')
            ->with('success', 'Kategori berhasil diupdate');
    }
}