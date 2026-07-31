<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;

class KategoriCicilanController extends Controller
{
    public function index()
    {
        $data = Kategori::query()
            ->where(function ($query) {
                $query->whereNull('isbengkel')
                    ->orWhere('isbengkel', '!=', '1');
            })
            ->orderBy('name')
            ->get();

        return view('master.kategori_cicilan.index', compact('data'));
    }

    public function edit($id)
    {
        $kategori = Kategori::query()
            ->where(function ($query) {
                $query->whereNull('isbengkel')
                    ->orWhere('isbengkel', '!=', '1');
            })
            ->findOrFail($id);

        return view('master.kategori_cicilan.edit', compact('kategori'));
    }

    public function update(Request $request, $id)
    {
        $kategori = Kategori::query()
            ->where(function ($query) {
                $query->whereNull('isbengkel')
                    ->orWhere('isbengkel', '!=', '1');
            })
            ->findOrFail($id);

        $kategori->update([
            'cicilan' => $request->has('cicilan') ? '1' : '0',
        ]);

        return redirect()->route('kategori.cicilan.index')
            ->with('success', 'Kategori cicilan berhasil diupdate');
    }
}
