<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Satuan;
use App\Models\StokUnit;
use App\Models\StockOpnameDTL;
use App\Models\StockOpnameHDR;
use App\Models\ModalAwal;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;
use App\Services\KartuStokService;
use App\Services\BarangNonMovingService;

class BarangBengkelController extends Controller
{
    public function index(Request $request): View
    {
        return view('master.barangbengkel.list', [
            'satuan'   => Satuan::where('isbengkel', '1')
                        ->orderBy('name')
                        ->get(),
            'kategori' => Kategori::where('isbengkel', '1')
                        ->orderBy('name')
                        ->get(),
        ]);
    }

    public function getdata(Request $request)
    {
        try {
            $barang = Barang::with(['kategoriRelation', 'satuanRelation'])
                ->where('kelompok_unit', 'bengkel')
                ->select('barang.*');

            $jenisBarang = $request->input('jenis_barang', 'normal');
            if ($jenisBarang === 'non_moving') {
                $barang->nonMoving();
            } else {
                $barang->normalMoving();
            }

            $statusProduk = $request->input('status_produk', 'aktif');
            if ($statusProduk === 'aktif') {
                $barang->aktif();
            } elseif ($statusProduk === 'nonaktif') {
                $barang->where('status_produk', 'nonaktif');
            }

            if ($request->has('kategori') && $request->kategori != 'all') {
                $barang->whereHas('kategoriRelation', function ($query) use ($request) {
                    $query->where('kategori.id', $request->kategori);
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
                        ->where('unit_id', 5)
                        ->value('stok') ?? 0;
                    return number_format($stok, 3, ',', '.');
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
                ->editColumn('harga_beli', function ($q) {
                    return 'Rp ' . number_format($q->harga_beli, 0, ',', '.');
                })
                ->editColumn('harga_jual', function ($q) {
                    return 'Rp ' . number_format($q->harga_jual, 0, ',', '.');
                })
                ->editColumn('img', function ($q) {
                    return $q->img;
                })
                ->editColumn('status_produk', function ($q) {
                    return $q->status_produk ?: 'aktif';
                })
                ->addColumn('aksi', function ($q) use ($jenisBarang) {
                    $encryptedId = Crypt::encryptString($q->id);
                    if ($jenisBarang === 'non_moving') {
                        return '
                            <button class="btn btn-sm btn-success restorebtn" data-id="' . $encryptedId . '" title="Kembalikan ke barang normal">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                        ';
                    }

                    return '
                        <button class="btn btn-sm btn-warning editbtn" data-id="' . $encryptedId . '" title="Edit">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <button class="btn btn-sm btn-danger deletebtn" data-id="' . $encryptedId . '" title="Hapus">
                            <i class="bi bi-trash3-fill"></i>
                        </button>
                    ';
                })
                ->rawColumns(['aksi'])
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

    public function getDetail(Request $request)
    {
        try {
            $id = Crypt::decryptString($request->id);
            $barang = Barang::with(['kategoriRelation', 'satuanRelation'])
                ->where('kelompok_unit', 'bengkel')
                ->findOrFail($id);
            
            $stok = StokUnit::where('barang_id', $barang->id)
                ->where('unit_id', 5)
                ->value('stok') ?? 0;
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $barang->id,
                    'kode_barang' => $barang->kode_barang,
                    'nama_barang' => $barang->nama_barang,
                    'idkategori' => $barang->idkategori,
                    'kategori' => $barang->kategoriRelation ? $barang->kategoriRelation->name : '',
                    'idsatuan' => $barang->idsatuan,
                    'satuan' => $barang->satuanRelation ? $barang->satuanRelation->name : '',
                    'harga_beli' => $barang->harga_beli,
                    'harga_jual' => $barang->harga_jual,
                    'status_produk' => $barang->status_produk ?: 'aktif',
                    'img' => $barang->img,
                    'stok' => $stok
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
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
        $count = Barang::where('kode_barang', $request->code)
            ->where('kelompok_unit', 'bengkel')
            ->count();
        return response()->json($count, 200);
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
            'status_produk' => 'nullable|in:aktif,nonaktif',
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
                $barang->status_produk = 'aktif';
                $barang->is_non_moving = false;
            }

            $barang->nama_barang = $request->nama_barang;
            $barang->harga_beli  = $request->harga_beli ?? 0;
            $barang->harga_jual  = $request->harga_jual ?? 0;
            $barang->idkategori  = $request->idkategori;
            $barang->idsatuan    = $request->idsatuan;
            $barang->status_produk = $request->input('status_produk', $barang->status_produk ?: 'aktif');
            $barang->is_non_moving = false;
            $barang->non_moving_at = null;
            $barang->non_moving_by = null;

            // Handle upload image
            if ($request->hasFile('img')) {
                if ($barang->img && Storage::disk('public')->exists('produk/bengkel/' . $barang->img)) {
                    Storage::disk('public')->delete('produk/bengkel/' . $barang->img);
                }

                $path = $request->file('img')->store('produk/bengkel', 'public');
                $barang->img = basename($path);
            } elseif ($request->has('hapus_gambar') && $request->hapus_gambar == '1') {
                if ($barang->img && Storage::disk('public')->exists('produk/bengkel/' . $barang->img)) {
                    Storage::disk('public')->delete('produk/bengkel/' . $barang->img);
                }
                $barang->img = null;
            }

            $barang->save();

            // Jika barang baru, buat stok untuk semua unit
            if (empty($request->idbarang)) {
                $units = Unit::all();
                foreach ($units as $unit) {
                    StokUnit::updateOrCreate(
                        [
                            'barang_id' => $barang->id,
                            'unit_id' => $unit->id
                        ],
                        ['stok' => 0]
                    );
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Barang bengkel berhasil disimpan',
                'data' => $barang
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

            if ($barang->img && Storage::disk('public')->exists('produk/bengkel/' . $barang->img)) {
                Storage::disk('public')->delete('produk/bengkel/' . $barang->img);
            }

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
            'idkategori'  => 'required|exists:kategori,id',
            'idsatuan'    => 'required|exists:satuan,id',
        ]);

        DB::beginTransaction();
        try {
            // Validasi harga
            if ($request->harga_jual > 0 && $request->harga_beli > 0 && $request->harga_jual < $request->harga_beli) {
                return response()->json([
                    'success' => false,
                    'message' => 'Harga jual tidak boleh kurang dari harga beli'
                ], 422);
            }

            $barang = new Barang();
            $barang->kode_barang = $request->kode_barang;
            $barang->nama_barang = $request->nama_barang;
            $barang->harga_beli = $request->harga_beli ?? 0;
            $barang->harga_jual = $request->harga_jual ?? 0;
            $barang->kelompok_unit = 'bengkel';
            $barang->idkategori = $request->idkategori;
            $barang->idsatuan = $request->idsatuan;
            $barang->status_produk = 'aktif';
            $barang->is_non_moving = false;
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
            $kartuStok = app(KartuStokService::class);
            $id = Crypt::decryptString($request->id);
            $barang = Barang::where('kelompok_unit', 'bengkel')->findOrFail($id);

            $kartuStok->setSaldo([
                'tanggal' => now(),
                'barang_id' => $barang->id,
                'unit_id' => 5,
                'saldo_akhir' => $request->stok,
                'harga_pokok' => $barang->harga_beli,
                'jenis_transaksi' => 'stock_adjustment',
                'nomor_referensi' => 'BGL-STOK-' . $barang->id,
                'referensi_tipe' => 'barang',
                'referensi_id' => $barang->id,
                'created_user' => auth()->id(),
                'keterangan' => 'Update stok manual barang bengkel',
            ]);

            $stokUnit = StokUnit::where('barang_id', $barang->id)
                ->where('unit_id', 5)
                ->first();

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
        $kategori = Kategori::where('isbengkel', 1)
                    ->orderBy('name')
                    ->get(['id', 'kode', 'name']);
        return response()->json($kategori);
    }

    public function getSatuanOptions()
    {
        $satuan = Satuan::where('isbengkel', 1)
                  ->orderBy('name')
                  ->get(['id', 'name']);
        return response()->json($satuan);
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|string',
            'status_produk' => 'required|in:aktif,nonaktif',
        ]);

        $id = Crypt::decryptString($request->id);
        $barang = Barang::where('kelompok_unit', 'bengkel')->findOrFail($id);
        $barang->status_produk = $request->status_produk;
        $barang->save();

        return response()->json([
            'success' => true,
            'message' => 'Status produk bengkel berhasil diperbarui.',
            'status_produk' => $barang->status_produk,
        ]);
    }

    public function markNonMovingCandidates(Request $request)
    {
        $nonMoving = app(BarangNonMovingService::class);
        $nonMoving->restoreBengkelWithAnyTransaction();

        $bulan = now()->format('Y-m');

        $candidateIds = Barang::query()
            ->where('barang.kelompok_unit', 'bengkel')
            ->normalMoving()
            ->whereDoesntHave('stok', function ($query) {
                $query->where('unit_id', 5)
                    ->where('stok', '>', 0);
            });

        $nonMoving->whereHasNoTransaction($candidateIds);

        $candidateIds = $candidateIds
            ->pluck('barang.id');

        if ($candidateIds->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada kandidat barang non moving baru.',
                'count' => 0,
            ]);
        }

        DB::beginTransaction();
        try {
            Barang::whereIn('id', $candidateIds)->update([
                'is_non_moving' => true,
                'non_moving_at' => now(),
                'non_moving_by' => auth()->id(),
                'updated_at' => now(),
            ]);

            StockOpnameDTL::whereIn('id_barang', $candidateIds)
                ->whereExists(function ($query) use ($bulan) {
                    $query->select(DB::raw(1))
                        ->from('stock_opname')
                        ->whereColumn('stock_opname.id', 'stock_opname_dtl.opnameid')
                        ->where('stock_opname.id_unit', 5)
                        ->whereRaw("DATE_FORMAT(stock_opname.tgl_opname, '%Y-%m') = ?", [$bulan])
                        ->where('stock_opname.status', 'pending')
                        ->whereNull('stock_opname.deleted_at');
                })
                ->delete();

            StockOpnameHDR::whereIn('id_barang', $candidateIds)
                ->where('id_unit', 5)
                ->whereRaw("DATE_FORMAT(tgl_opname, '%Y-%m') = ?", [$bulan])
                ->where('status', 'pending')
                ->delete();

            ModalAwal::whereIn('barang_id', $candidateIds)
                ->where('unit_id', 5)
                ->where('periode', $bulan)
                ->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $candidateIds->count() . ' barang berhasil dipindahkan ke Barang Non Moving.',
                'count' => $candidateIds->count(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal memindahkan barang non moving: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function restoreNonMoving(Request $request)
    {
        $request->validate([
            'id' => 'required|string',
        ]);

        $id = Crypt::decryptString($request->id);
        $barang = Barang::where('kelompok_unit', 'bengkel')->findOrFail($id);

        $barang->is_non_moving = false;
        $barang->non_moving_at = null;
        $barang->non_moving_by = null;
        $barang->save();

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil dikembalikan ke daftar normal.',
        ]);
    }
}
