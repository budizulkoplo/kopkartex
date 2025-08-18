<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Satuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class BarangController extends Controller
{
    public function index(Request $request): View
    {
        return view('master.barang.list', [
            'satuan'   => Satuan::orderBy('name')->get(),
            'kategori' => Kategori::orderBy('name')->get(),
        ]);
    }

    public function getdata(Request $request)
    {
        $barang = Barang::query();

        if ($request->kategori != 'all') {
            $barang->where('kategori', $request->kategori);
        }

        return DataTables::of($barang)
            ->addIndexColumn()
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value'] != '') {
                    $query->where(function ($q) use ($request) {
                        $q->orWhere('nama_barang', 'like', '%' . $request->search['value'] . '%')
                          ->orWhere('kode_barang', 'like', '%' . $request->search['value'] . '%');
                    });
                }
            })
            ->editColumn('id', function ($q) {
                return Crypt::encryptString($q->id);
            })
            ->make(true);
    }

    private function genCode()
    {
        $total = Barang::withTrashed()->whereDate('created_at', date("Y-m-d"))->count();
        $nomorUrut = $total + 1;
        return 'BRG-' . date("ymd") . str_pad($nomorUrut, 3, '0', STR_PAD_LEFT);
    }

    public function getCode()
    {
        return response()->json($this->genCode(), 200);
    }

    public function CekCode(Request $request)
    {
        $barang = Barang::where('kode_barang', $request->code)->count();
        return response()->json($barang, 200);
    }

    public function Store(Request $request)
    {
        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'kode_barang' => 'required|string|max:50',
            'harga_beli'  => 'nullable|numeric',
            'harga_jual'  => 'nullable|numeric',
            'kategori'    => 'required|string',
            'satuan'      => 'required|string',
            'img'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if (!empty($request->idbarang)) {
            // update
            $id     = Crypt::decryptString($request->idbarang);
            $barang = Barang::findOrFail($id);
        } else {
            // insert
            if (Barang::where('kode_barang', $request->kode_barang)->exists()) {
                return response()->json('Kode sudah terpakai', 500);
            }
            $barang = new Barang;
            $barang->kode_barang = $request->kode_barang;
        }

        $barang->nama_barang = $request->nama_barang;
        $barang->harga_beli  = $request->harga_beli;
        $barang->harga_jual  = $request->harga_jual;
        $barang->kategori    = $request->kategori;
        $barang->satuan      = $request->satuan;

        // Handle upload image
        if ($request->hasFile('img')) {
            // Hapus gambar lama (kalau ada)
            if ($barang->img && Storage::disk('public')->exists('produk/' . $barang->img)) {
                Storage::disk('public')->delete('produk/' . $barang->img);
            }

            $path = $request->file('img')->store('produk', 'public');
            $barang->img = basename($path);
        }

        $barang->save();

        return response()->json('success', 200);
    }

    public function Hapus(Request $request)
    {
        $id     = Crypt::decryptString($request->id);
        $barang = Barang::findOrFail($id);

        // Hapus file gambar juga
        if ($barang->img && Storage::disk('public')->exists('produk/' . $barang->img)) {
            Storage::disk('public')->delete('produk/' . $barang->img);
        }

        $barang->delete();

        return response()->json('success', 200);
    }
}
