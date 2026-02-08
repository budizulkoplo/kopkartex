<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Satuan;
use App\Models\StokUnit;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class BarangBengkelController extends Controller
{
    public function index(Request $request): View
    {
        return view('master.barangbengkel.list', [
            'satuan'   => Satuan::orderBy('name')->get(),
            'kategori' => Kategori::orderBy('name')->get(),
        ]);
    }

    public function getdata(Request $request)
    {
        try {
            $barang = Barang::with(['kategoriRelation', 'satuanRelation'])
                ->where('kelompok_unit', 'bengkel')
                ->select('barang.*');

            if ($request->has('kategori') && $request->kategori != 'all') {
                $barang->whereHas('kategoriRelation', function ($query) use ($request) {
                    $query->where('name', $request->kategori);
                });
            }

            return DataTables::of($barang)
                ->addIndexColumn()
                ->addColumn('DT_RowIndex', function($row) use ($request) {
                    static $index = 0;
                    return ++$index + ($request->input('start', 0));
                })
                ->addColumn('stok', function($row) {
                    $stok = StokUnit::where('barang_id', $row->id)
                        ->where('unit_id', 5) // Unit bengkel
                        ->value('stok') ?? 0;
                    return number_format($stok, 0, ',', '.');
                })
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->search['value'])) {
                        $search = $request->search['value'];
                        $query->where(function ($q) use ($search) {
                            $q->where('nama_barang', 'like', '%' . $search . '%')
                              ->orWhere('kode_barang', 'like', '%' . $search . '%')
                              ->orWhereHas('kategoriRelation', function ($query) use ($search) {
                                  $query->where('name', 'like', '%' . $search . '%');
                              });
                        });
                    }
                })
                ->editColumn('id', function ($q) {
                    return Crypt::encryptString($q->id);
                })
                ->addColumn('kategori_nama', function ($row) {
                    return $row->kategoriRelation ? $row->kategoriRelation->name : '-';
                })
                ->addColumn('satuan_nama', function ($row) {
                    return $row->satuanRelation ? $row->satuanRelation->name : '-';
                })
                ->addColumn('harga_beli_format', function ($q) {
                    return 'Rp ' . number_format($q->harga_beli, 0, ',', '.');
                })
                ->addColumn('harga_jual_format', function ($q) {
                    return 'Rp ' . number_format($q->harga_jual, 0, ',', '.');
                })
                ->addColumn('foto', function ($q) {
                    if ($q->img) {
                        return '<img src="' . asset('storage/produk/bengkel/' . $q->img) . '" class="img-thumbnail" style="max-height:50px; max-width:60px;">';
                    }
                    return '<span class="badge bg-secondary">No Image</span>';
                })
                ->addColumn('aksi', function ($q) {
                    $encryptedId = Crypt::encryptString($q->id);
                    return '
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-warning btn-edit" data-id="' . $encryptedId . '" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-outline-danger btn-delete" data-id="' . $encryptedId . '" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['foto', 'aksi'])
                ->make(true);
                
        } catch (\Exception $e) {
            return response()->json([
                'draw' => $request->input('draw', 0),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getSingleData(Request $request)
    {
        try {
            $id = Crypt::decryptString($request->id);
            $barang = Barang::with(['kategoriRelation', 'satuanRelation'])
                ->where('kelompok_unit', 'bengkel')
                ->findOrFail($id);
            
            // Ambil stok dari unit 5 (bengkel)
            $stok = StokUnit::where('barang_id', $barang->id)
                ->where('unit_id', 5)
                ->value('stok') ?? 0;
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => Crypt::encryptString($barang->id),
                    'kode_barang' => $barang->kode_barang,
                    'nama_barang' => $barang->nama_barang,
                    'idkategori' => $barang->idkategori,
                    'kategori' => $barang->kategoriRelation ? $barang->kategoriRelation->name : '',
                    'idsatuan' => $barang->idsatuan,
                    'satuan' => $barang->satuanRelation ? $barang->satuanRelation->name : '',
                    'harga_beli' => $barang->harga_beli,
                    'harga_jual' => $barang->harga_jual,
                    'img' => $barang->img,
                    'stok' => $stok,
                    'kelompok_unit' => $barang->kelompok_unit
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan: ' . $e->getMessage()
            ], 404);
        }
    }

    private function genCode()
    {
        $total = Barang::withTrashed()
            ->where('kelompok_unit', 'bengkel')
            ->whereDate('created_at', date("Y-m-d"))
            ->count();
        $nomorUrut = $total + 1;
        return 'BGL-' . date("ymd") . str_pad($nomorUrut, 3, '0', STR_PAD_LEFT);
    }

    public function getCode()
    {
        return response()->json($this->genCode(), 200);
    }

    public function CekCode(Request $request)
    {
        $barang = Barang::where('kode_barang', $request->code)
            ->where('kelompok_unit', 'bengkel')
            ->count();
        return response()->json($barang, 200);
    }

    public function Store(Request $request)
    {
        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'kode_barang' => 'required|string|max:50',
            'harga_beli'  => 'nullable|numeric|min:0',
            'harga_jual'  => 'nullable|numeric|min:0',
            'idkategori'  => 'required|exists:kategori,id',
            'idsatuan'    => 'required|exists:satuan,id',
            'img'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        DB::beginTransaction();
        try {
            // Validasi harga jual harus >= harga beli
            if ($request->harga_jual > 0 && $request->harga_beli > 0 && $request->harga_jual < $request->harga_beli) {
                return response()->json([
                    'success' => false,
                    'message' => 'Harga jual tidak boleh kurang dari harga beli'
                ], 422);
            }

            if (!empty($request->idbarang)) {
                // update
                $id = Crypt::decryptString($request->idbarang);
                $barang = Barang::where('kelompok_unit', 'bengkel')->findOrFail($id);
                
                // Validasi kode unik jika diubah
                if ($barang->kode_barang != $request->kode_barang) {
                    $existing = Barang::where('kode_barang', $request->kode_barang)
                        ->where('kelompok_unit', 'bengkel')
                        ->where('id', '!=', $id)
                        ->exists();
                    if ($existing) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Kode barang sudah terpakai'
                        ], 422);
                    }
                }
            } else {
                // insert
                $existing = Barang::where('kode_barang', $request->kode_barang)
                    ->where('kelompok_unit', 'bengkel')
                    ->exists();
                    
                if ($existing) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Kode barang sudah terpakai'
                    ], 422);
                }
                
                $barang = new Barang;
                $barang->kode_barang = $request->kode_barang;
                $barang->kelompok_unit = 'bengkel';
            }

            $barang->nama_barang = $request->nama_barang;
            $barang->harga_beli  = $request->harga_beli ?? 0;
            $barang->harga_jual  = $request->harga_jual ?? 0;
            $barang->idkategori  = $request->idkategori;
            $barang->idsatuan    = $request->idsatuan;

            // Handle upload image
            if ($request->hasFile('img')) {
                // Hapus gambar lama (kalau ada)
                if ($barang->img && Storage::disk('public')->exists('produk/bengkel/' . $barang->img)) {
                    Storage::disk('public')->delete('produk/bengkel/' . $barang->img);
                }

                $path = $request->file('img')->store('produk/bengkel', 'public');
                $barang->img = basename($path);
            } elseif ($request->has('hapus_gambar') && $request->hapus_gambar == '1') {
                // Hapus gambar jika diminta
                if ($barang->img && Storage::disk('public')->exists('produk/bengkel/' . $barang->img)) {
                    Storage::disk('public')->delete('produk/bengkel/' . $barang->img);
                }
                $barang->img = null;
            }

            $barang->save();

            // Jika barang baru, buat stok untuk semua unit (termasuk unit 5)
            if (empty($request->idbarang)) {
                $units = Unit::all();
                foreach ($units as $unit) {
                    StokUnit::updateOrCreate(
                        [
                            'barang_id' => $barang->id,
                            'unit_id' => $unit->id
                        ],
                        [
                            'stok' => 0
                        ]
                    );
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Barang bengkel berhasil disimpan'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan barang: ' . $e->getMessage()
            ], 500);
        }
    }

    public function Hapus(Request $request)
    {
        DB::beginTransaction();
        try {
            $id = Crypt::decryptString($request->id);
            $barang = Barang::where('kelompok_unit', 'bengkel')->findOrFail($id);

            // Pastikan hanya barang bengkel yang dihapus
            if ($barang->kelompok_unit != 'bengkel') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya barang bengkel yang dapat dihapus dari menu ini'
                ], 403);
            }

            // Hapus file gambar juga
            if ($barang->img && Storage::disk('public')->exists('produk/bengkel/' . $barang->img)) {
                Storage::disk('public')->delete('produk/bengkel/' . $barang->img);
            }

            // Hapus stok terkait
            StokUnit::where('barang_id', $barang->id)->delete();
            
            $barang->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Barang bengkel berhasil dihapus'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus barang: ' . $e->getMessage()
            ], 500);
        }
    }

    public function quickAdd(Request $request)
    {
        $request->validate([
            'kode_barang' => 'required|string|max:50|unique:barang,kode_barang',
            'nama_barang' => 'required|string|max:255',
            'harga_beli'  => 'nullable|numeric|min:0',
            'harga_jual'  => 'nullable|numeric|min:0',
            'kategori'    => 'nullable|string|max:100',
            'satuan'      => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            // Cari atau buat kategori
            $kategoriId = null;
            if ($request->kategori) {
                $kategori = Kategori::firstOrCreate(
                    ['name' => $request->kategori],
                    ['name' => $request->kategori]
                );
                $kategoriId = $kategori->id;
            } else {
                // Default kategori untuk bengkel
                $kategori = Kategori::where('name', 'Sparepart')->first();
                if (!$kategori) {
                    $kategori = Kategori::create(['name' => 'Sparepart']);
                }
                $kategoriId = $kategori->id;
            }

            // Cari atau buat satuan
            $satuanId = null;
            if ($request->satuan) {
                $satuan = Satuan::firstOrCreate(
                    ['name' => $request->satuan],
                    ['name' => $request->satuan]
                );
                $satuanId = $satuan->id;
            } else {
                // Default satuan untuk bengkel
                $satuan = Satuan::where('name', 'PCS')->first();
                if (!$satuan) {
                    $satuan = Satuan::create(['name' => 'PCS']);
                }
                $satuanId = $satuan->id;
            }

            $barang = new Barang();
            $barang->kode_barang = $request->kode_barang;
            $barang->nama_barang = $request->nama_barang;
            $barang->harga_beli = $request->harga_beli ?? 0;
            $barang->harga_jual = $request->harga_jual ?? 0;
            $barang->kelompok_unit = 'bengkel';
            $barang->idkategori = $kategoriId;
            $barang->idsatuan = $satuanId;
            $barang->save();

            // Tambah ke stok unit untuk semua unit
            $units = Unit::all();
            foreach ($units as $unit) {
                StokUnit::create([
                    'barang_id' => $barang->id,
                    'unit_id' => $unit->id,
                    'stok' => 0
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Barang bengkel berhasil ditambahkan',
                'data' => [
                    'id' => $barang->id,
                    'kode_barang' => $barang->kode_barang,
                    'nama_barang' => $barang->nama_barang,
                    'harga_beli' => $barang->harga_beli,
                    'harga_jual' => $barang->harga_jual
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan barang: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStok(Request $request)
    {
        $request->validate([
            'id' => 'required|string',
            'stok' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $id = Crypt::decryptString($request->id);
            $barang = Barang::where('kelompok_unit', 'bengkel')->findOrFail($id);
            
            // Update stok untuk unit 5 (bengkel)
            $stokUnit = StokUnit::updateOrCreate(
                [
                    'barang_id' => $barang->id,
                    'unit_id' => 5
                ],
                [
                    'stok' => $request->stok,
                    'updated_at' => now()
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stok barang berhasil diperbarui',
                'stok' => $stokUnit->stok
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui stok: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getKategoriOptions()
    {
        $kategori = Kategori::orderBy('name')->get();
        return response()->json($kategori);
    }

    public function getSatuanOptions()
    {
        $satuan = Satuan::orderBy('name')->get();
        return response()->json($satuan);
    }

    public function createKategori(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:kategori,name',
        ]);

        $kategori = Kategori::create([
            'name' => $request->name
        ]);

        return response()->json([
            'success' => true,
            'kategori' => $kategori,
            'message' => 'Kategori berhasil ditambahkan'
        ]);
    }

    public function createSatuan(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:satuan,name',
        ]);

        $satuan = Satuan::create([
            'name' => $request->name
        ]);

        return response()->json([
            'success' => true,
            'satuan' => $satuan,
            'message' => 'Satuan berhasil ditambahkan'
        ]);
    }
}