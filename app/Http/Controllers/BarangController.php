<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Satuan;
use App\Models\Penerimaan;
use App\Models\PenerimaanDtl;
use App\Models\StokUnit;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $barang = Barang::with(['kategoriRelation', 'satuanRelation'])
            ->select('barang.*');

        // Filter berdasarkan kategori (menggunakan idkategori yang baru)
        if ($request->kategori != 'all') {
            $barang->whereHas('kategoriRelation', function ($query) use ($request) {
                $query->where('name', $request->kategori);
            });
        }

        // Filter berdasarkan kelompok unit jika diperlukan
        if ($request->has('kelompok_unit') && $request->kelompok_unit != 'all') {
            $barang->where('kelompok_unit', $request->kelompok_unit);
        }

        return DataTables::of($barang)
            ->addIndexColumn()
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value'] != '') {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->orWhere('nama_barang', 'like', '%' . $search . '%')
                        ->orWhere('kode_barang', 'like', '%' . $search . '%')
                        ->orWhere('type', 'like', '%' . $search . '%');
                    });
                }
            })
            ->addColumn('kategori_nama', function ($barang) {
                return $barang->kategoriRelation ? $barang->kategoriRelation->name : '-';
            })
            ->addColumn('satuan_nama', function ($barang) {
                return $barang->satuanRelation ? $barang->satuanRelation->name : '-';
            })
            ->editColumn('harga_beli', function ($barang) {
                // Kembalikan format string "Rp X.XXX"
                return 'Rp ' . number_format($barang->harga_beli, 0, ',', '.');
            })
            ->editColumn('harga_jual', function ($barang) {
                // Kembalikan format string "Rp X.XXX"
                return 'Rp ' . number_format($barang->harga_jual, 0, ',', '.');
            })
            ->editColumn('harga_jual_umum', function ($barang) {
                // Kembalikan format string "Rp X.XXX"
                return 'Rp ' . number_format($barang->harga_jual_umum ?? 0, 0, ',', '.');
            })
            ->editColumn('img', function ($barang) {
                return $barang->img;
            })
            ->editColumn('id', function ($barang) {
                return $barang->id;
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

    public function getBarangForPenerimaan(Request $request)
    {
        $searchTerm = $request->q ?? '';
        $barang = Barang::where(function($query) use ($searchTerm) {
                $query->where('kode_barang', 'like', "%{$searchTerm}%")
                      ->orWhere('nama_barang', 'like', "%{$searchTerm}%");
            })
            ->select('id', 'kode_barang as code', 'nama_barang as text', 'harga_beli', 'harga_jual', 'harga_jual_umum')
            ->limit(50)
            ->get();
        
        return response()->json($barang);
    }

    public function getBarangByCodeForPenerimaan(Request $request)
    {
        $kode = $request->kode;
        $barang = Barang::where('kode_barang', $kode)
            ->select('id', 'kode_barang as code', 'nama_barang as text', 'harga_beli', 'harga_jual', 'harga_jual_umum')
            ->first();
        
        if ($barang) {
            return response()->json($barang);
        } else {
            // Barang tidak ditemukan, kirim data untuk quick add
            return response()->json([
                'not_found' => true,
                'suggested_code' => $kode
            ], 404);
        }
    }

    public function createQuickBarang(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'kode_barang' => 'required|string|max:50|unique:barang,kode_barang',
                'nama_barang' => 'required|string|max:100',
                'harga_beli' => 'required|numeric|min:0',
                'harga_jual' => 'required|numeric|min:0',
                'harga_jual_umum' => 'nullable|numeric|min:0',
                'satuan' => 'nullable|string|max:20',
                'kategori' => 'nullable|string|max:50',
            ]);

            // Cari atau buat satuan
            $satuanId = null;
            if ($request->satuan) {
                $satuan = Satuan::firstOrCreate(['name' => $request->satuan], ['name' => $request->satuan]);
                $satuanId = $satuan->id;
            }

            // Cari atau buat kategori
            $kategoriId = null;
            if ($request->kategori) {
                $kategori = Kategori::firstOrCreate(['name' => $request->kategori], ['name' => $request->kategori]);
                $kategoriId = $kategori->id;
            }

            $barang = new Barang();
            $barang->kode_barang = $request->kode_barang;
            $barang->nama_barang = $request->nama_barang;
            $barang->harga_beli = $request->harga_beli;
            $barang->harga_jual = $request->harga_jual;
            $barang->harga_jual_umum = $request->harga_jual_umum ?? $request->harga_jual; // Default sama dengan harga_jual jika kosong
            $barang->satuan = $request->satuan ?? 'PCS';
            $barang->idkategori = $kategoriId;
            $barang->idsatuan = $satuanId;
            $barang->kelompok_unit = 'toko'; // Default
            $barang->type = $request->type ?? '';
            $barang->save();

            // Buat stok awal
            DB::table('stok_unit')->insert([
                'barang_id' => $barang->id,
                'unit_id' => 1, // Asumsi unit_id = 1 untuk toko utama
                'stok' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'barang' => [
                    'id' => $barang->id,
                    'code' => $barang->kode_barang,
                    'text' => $barang->nama_barang,
                    'harga_beli' => $barang->harga_beli,
                    'harga_jual' => $barang->harga_jual,
                    'harga_jual_umum' => $barang->harga_jual_umum,
                ],
                'message' => 'Barang berhasil ditambahkan'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan barang: ' . $e->getMessage()
            ], 500);
        }
    }

    public function Store(Request $request)
    {
        $request->validate([
            'nama_barang' => 'required|string|max:100',
            'kode_barang' => 'required|string|max:50',
            'type'        => 'nullable|string|max:50',
            'harga_beli'  => 'nullable|numeric|min:0',
            'harga_jual'  => 'nullable|numeric|min:0',
            'harga_jual_umum' => 'nullable|numeric|min:0',
            'kategori'    => 'required|string',
            'satuan'      => 'required|string',
            'kelompok_unit' => 'nullable|in:toko,bengkel,air',
            'img'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Cari ID kategori berdasarkan nama
        $kategori = Kategori::where('name', $request->kategori)->first();
        if (!$kategori) {
            return response()->json('Kategori tidak ditemukan', 422);
        }

        // Cari ID satuan berdasarkan nama
        $satuan = Satuan::where('name', $request->satuan)->first();
        if (!$satuan) {
            return response()->json('Satuan tidak ditemukan', 422);
        }

        if (!empty($request->idbarang)) {
            try {
                // Coba decrypt ID - hanya jika masih encrypted
                $id = Crypt::decryptString($request->idbarang);
                $barang = Barang::findOrFail($id);
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                // Jika gagal decrypt, mungkin ID sudah dalam bentuk plain
                $id = $request->idbarang;
                $barang = Barang::findOrFail($id);
            }
        } else {
            // Create new barang
            if (Barang::where('kode_barang', $request->kode_barang)->exists()) {
                return response()->json('Kode barang sudah terpakai', 422);
            }
            $barang = new Barang();
            $barang->kode_barang = $request->kode_barang;
        }

        $barang->nama_barang = $request->nama_barang;
        $barang->type = $request->type;
        $barang->harga_beli = $request->harga_beli ?? 0;
        $barang->harga_jual = $request->harga_jual ?? 0;
        $barang->harga_jual_umum = $request->harga_jual_umum ?? $request->harga_jual ?? 0;
        $barang->idkategori = $kategori->id;
        $barang->idsatuan = $satuan->id;
        $barang->kelompok_unit = $request->kelompok_unit ?? 'toko';

        // Handle upload image
        if ($request->hasFile('img')) {
            // Hapus gambar lama jika ada
            if ($barang->img && Storage::disk('public')->exists('produk/' . $barang->img)) {
                Storage::disk('public')->delete('produk/' . $barang->img);
            }

            $path = $request->file('img')->store('produk', 'public');
            $barang->img = basename($path);
        } elseif ($request->has('hapus_gambar') && $request->hapus_gambar == '1') {
            // Hapus gambar jika diminta
            if ($barang->img && Storage::disk('public')->exists('produk/' . $barang->img)) {
                Storage::disk('public')->delete('produk/' . $barang->img);
            }
            $barang->img = null;
        }

        $barang->save();

        return response()->json(['message' => 'Berhasil disimpan', 'data' => $barang], 200);
    }

    public function Hapus(Request $request)
    {
        $id = Crypt::decryptString($request->id);
        $barang = Barang::findOrFail($id);

        // Hapus file gambar
        if ($barang->img && Storage::disk('public')->exists('produk/' . $barang->img)) {
            Storage::disk('public')->delete('produk/' . $barang->img);
        }

        $barang->delete();

        return response()->json(['message' => 'Berhasil dihapus'], 200);
    }

    public function getDetail(Request $request)
    {
        try {
            $id = $request->id;
            $barang = Barang::with(['kategoriRelation', 'satuanRelation'])
                ->where('id', $id)
                ->firstOrFail();
                
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $barang->id,
                    'kode_barang' => $barang->kode_barang,
                    'nama_barang' => $barang->nama_barang,
                    'type' => $barang->type,
                    'kelompok_unit' => $barang->kelompok_unit,
                    'kategori' => $barang->kategoriRelation ? $barang->kategoriRelation->name : '',
                    'satuan' => $barang->satuanRelation ? $barang->satuanRelation->name : '',
                    'harga_beli' => $barang->harga_beli,
                    'harga_jual' => $barang->harga_jual,
                    'harga_jual_umum' => $barang->harga_jual_umum,
                    'img' => $barang->img
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }
    }
}