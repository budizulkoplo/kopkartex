<?php

namespace App\Http\Controllers;

use App\Models\ReturBarang;
use App\Models\ReturBarangDetail;
use App\Models\StokUnit;
use App\Models\Supplier;
use App\Models\Barang;
use App\Models\Satuan;
use App\Models\Kategori;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\Facades\DataTables;

class ReturController extends Controller
{
    public function index(Request $request): View
    {
        // Ambil data satuan dan kategori untuk dropdown
        $satuans = Satuan::select('id', 'name')->get();
        $kategoris = Kategori::select('id', 'name')->get();
        
        return view('transaksi.ReturBarang', [
            'invoice' => $this->genCode(),
            'satuans' => $satuans,
            'kategoris' => $kategoris,
        ]);
    }
    
    // Fungsi generate kode retur otomatis
    private function genCode()
    {
        $total = ReturBarang::withTrashed()->whereDate('created_at', date("Y-m-d"))->count();
        $nomorUrut = $total + 1;
        return 'RTR-' . date("ymd") . str_pad($nomorUrut, 3, '0', STR_PAD_LEFT);
    }
    
    // Method untuk mendapatkan invoice baru
    public function getInvoice()
    {
        return response()->json($this->genCode());
    }
    
    public function ListData(Request $request): View
    {
        return view('transaksi.ReturBarangList');
    }
    
    public function getBarang(Request $request)
    {
        $unitId = Auth::user()->unit_kerja;
        
        $barang = StokUnit::join('barang', 'barang.id', 'stok_unit.barang_id')
            ->where('stok_unit.unit_id', $unitId)
            ->where(function($query) use ($request) {
                $query->where('barang.kode_barang', 'LIKE', "%{$request->q}%")
                      ->orWhere('barang.nama_barang', 'LIKE', "%{$request->q}%");
            })
            ->select(
                'barang.id',
                'barang.kode_barang as code',
                'barang.nama_barang as text',
                'stok_unit.stok',
                'barang.harga_beli',
                'barang.harga_jual',
                'barang.type',
                'barang.idsatuan',
                'barang.idkategori'
            )
            ->get()
            ->map(function($item) {
                $satuan = Satuan::find($item->idsatuan);
                $kategori = Kategori::find($item->idkategori);
                $item->satuan = $satuan ? $satuan->name : '';
                $item->kategori = $kategori ? $kategori->name : '';
                return $item;
            });
            
        return response()->json($barang);
    }
    
    public function getBarangByCode(Request $request)
    {
        $unitId = Auth::user()->unit_kerja;
        
        $barang = StokUnit::join('barang', 'barang.id', 'stok_unit.barang_id')
            ->where("barang.kode_barang", "=", $request->kode)
            ->where("stok_unit.unit_id", "=", $unitId)
            ->select(
                'barang.id',
                'barang.kode_barang as code',
                'barang.nama_barang as text',
                'stok_unit.stok',
                'barang.harga_beli',
                'barang.harga_jual',
                'barang.type',
                'barang.idsatuan',
                'barang.idkategori'
            )
            ->first();
            
        if ($barang) {
            $satuan = Satuan::find($barang->idsatuan);
            $kategori = Kategori::find($barang->idkategori);
            $barang->satuan = $satuan ? $satuan->name : '';
            $barang->kategori = $kategori ? $kategori->name : '';
            return response()->json($barang);
        } else {
            return response()->json(['error' => 'Barang tidak ditemukan'], 404);
        }
    }
    
    public function getSupplier(Request $request)
    {
        $q = $request->q;
        $supplier = Supplier::where('nama_supplier', 'LIKE', "%$q%")
            ->select('id', 'kode_supplier', 'nama_supplier as text')
            ->get();

        return response()->json($supplier);
    }
    
    public function storeSupplier(Request $request)
    {
        try {
            $request->validate([
                'nama_supplier' => 'required|string|max:255',
            ]);

            // Generate kode supplier otomatis
            $kodeSupplier = 'SUP-' . date('ymd') . str_pad(Supplier::count() + 1, 3, '0', STR_PAD_LEFT);

            $supplier = new Supplier();
            $supplier->kode_supplier = $kodeSupplier;
            $supplier->nama_supplier = $request->nama_supplier;
            $supplier->alamat = $request->alamat ?? null;
            $supplier->telp = $request->telp ?? null;
            $supplier->kontak_person = $request->kontak_person ?? null;
            $supplier->email = $request->email ?? null;
            $supplier->save();

            return response()->json([
                'success' => true,
                'supplier' => [
                    'id' => $supplier->id,
                    'kode_supplier' => $supplier->kode_supplier,
                    'text' => $supplier->nama_supplier
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan supplier: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function storeBarang(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'kode_barang' => 'required|string|max:50|unique:barang,kode_barang',
                'nama_barang' => 'required|string|max:100',
                'harga_beli' => 'required|numeric|min:0',
                'harga_jual' => 'required|numeric|min:0',
            ]);

            // Cek apakah kode barang sudah ada
            $existingBarang = Barang::where('kode_barang', $request->kode_barang)->first();
            if ($existingBarang) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode barang sudah terdaftar'
                ], 400);
            }

            $barang = new Barang();
            $barang->kode_barang = $request->kode_barang;
            $barang->nama_barang = $request->nama_barang;
            $barang->type = $request->type ?? null;
            $barang->idkategori = $request->idkategori ?? null;
            $barang->idsatuan = $request->idsatuan ?? null;
            $barang->harga_beli = $request->harga_beli;
            $barang->harga_jual = $request->harga_jual;
            $barang->kelompok_unit = $request->kelompok_unit ?? 'toko';
            $barang->deskripsi = $request->deskripsi ?? null;
            $barang->save();

            // Tambahkan stok awal 0 untuk semua unit
            $unitIds = [1, 2, 3, 4]; // Sesuaikan dengan unit yang ada
            foreach ($unitIds as $unitId) {
                DB::statement("
                    INSERT INTO stok_unit (barang_id, unit_id, stok, updated_at, created_at) 
                    VALUES (?, ?, ?, NOW(), NOW())
                    ON DUPLICATE KEY UPDATE 
                        updated_at = VALUES(updated_at)",
                    [$barang->id, $unitId, 0]
                );
            }

            DB::commit();

            // Ambil nama satuan dan kategori
            $satuan = Satuan::find($request->idsatuan);
            $kategori = Kategori::find($request->idkategori);

            return response()->json([
                'success' => true,
                'barang' => [
                    'id' => $barang->id,
                    'code' => $barang->kode_barang,
                    'text' => $barang->nama_barang,
                    'nama_barang' => $barang->nama_barang,
                    'harga_beli' => $barang->harga_beli,
                    'harga_jual' => $barang->harga_jual,
                    'satuan' => $satuan ? $satuan->name : '',
                    'kategori' => $kategori ? $kategori->name : '',
                    'stok' => 0
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
    
    public function getKategori()
    {
        $kategoris = Kategori::select('id', 'name')->get();
        return response()->json($kategoris);
    }
    
    public function Store(Request $request)
    {
        DB::beginTransaction();
        try {
            $date = Carbon::parse($request->tgl_retur);
            $unitId = Auth::user()->unit_kerja;
            
            // Validasi supplier
            if (!$request->supplier_id) {
                throw new Exception('Supplier harus dipilih.');
            }
            
            // Cari supplier berdasarkan ID
            $supplier = Supplier::find($request->supplier_id);
            if (!$supplier) {
                throw new Exception('Supplier tidak ditemukan.');
            }
            
            // Validasi ada barang yang diretur
            $quantities = $request->input('qty', []);
            $barangIds = $request->input('barang_id', []);
            
            if (empty($barangIds) || empty($quantities)) {
                throw new Exception('Minimal ada 1 barang yang harus diretur.');
            }

            $hdr = new ReturBarang;
            $hdr->nomor_retur = $request->invoice ?? $this->genCode();
            $hdr->idsupplier = $supplier->id;
            $hdr->kode_supplier = $supplier->kode_supplier;
            $hdr->nama_supplier = $supplier->nama_supplier;
            $hdr->note = $request->note;
            $hdr->tgl_retur = $date->format('Y-m-d');
            $hdr->unit_id = $unitId;
            $hdr->created_user = Auth::user()->id;
            $hdr->save();
            
            $idhdr = $hdr->id;
            
            $hargaBeliArr = $request->input('harga_beli', []);
            $hargaJualArr = $request->input('harga_jual', []);
            $kodeBarangArr = $request->input('kode_barang', []);
            $namaBarangArr = $request->input('nama_barang', []);
            $satuanArr = $request->input('satuan', []);
            $kategoriArr = $request->input('kategori', []);
            
            $grandTotal = 0;
            
            foreach ($barangIds as $index => $barangId) {
                // Validasi quantity
                if (empty($quantities[$index]) || $quantities[$index] <= 0) {
                    throw new Exception("Quantity barang ke-" . ($index + 1) . " harus lebih dari 0.");
                }
                
                // Validasi stok
                $stok = StokUnit::where('barang_id', $barangId)
                    ->where('unit_id', $unitId)
                    ->value('stok');
                    
                if ($stok < $quantities[$index]) {
                    throw new Exception("Stok tidak mencukupi untuk barang: " . ($namaBarangArr[$index] ?? 'Unknown'));
                }
                
                // Validasi harga beli
                if (empty($hargaBeliArr[$index]) || $hargaBeliArr[$index] < 0) {
                    throw new Exception("Harga beli barang ke-" . ($index + 1) . " tidak valid.");
                }
                
                // Validasi harga jual
                if (empty($hargaJualArr[$index]) || $hargaJualArr[$index] < 0) {
                    throw new Exception("Harga jual barang ke-" . ($index + 1) . " tidak valid.");
                }
                
                // Cek apakah ini barang baru (dimulai dengan 'new-')
                if (str_starts_with($barangId, 'new-')) {
                    throw new Exception('Barang baru harus disimpan terlebih dahulu sebelum bisa diretur.');
                }
                
                $subtotal = $quantities[$index] * ($hargaBeliArr[$index] ?? 0);
                $grandTotal += $subtotal;
                
                $dtl = new ReturBarangDetail;
                $dtl->idretur = $idhdr;
                $dtl->barang_id = $barangId;
                $dtl->qty = $quantities[$index];
                $dtl->harga_beli = $hargaBeliArr[$index] ?? 0;
                $dtl->harga_jual = $hargaJualArr[$index] ?? 0;
                $dtl->subtotal = $subtotal;
                $dtl->save();
                
                // Kurangi stok
                StokUnit::where('unit_id', $unitId)
                    ->where('barang_id', $barangId)
                    ->decrement('stok', $quantities[$index]);
            }
            
            // Update grand total di header
            $hdr->grandtotal = $grandTotal;
            $hdr->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Retur berhasil disimpan',
                'invoice' => $hdr->nomor_retur,
                'id' => $hdr->id
            ]);
            
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function getDataTable(Request $request)
    {
        $retur = ReturBarang::join('unit', 'unit.id', 'retur.unit_id')
            ->join('users', 'users.id', 'retur.created_user')
            ->select(
                'retur.id',
                'retur.nomor_retur',
                'retur.nama_supplier',
                'retur.tgl_retur',
                'retur.grandtotal',
                'retur.note',
                'unit.nama_unit',
                'users.name as userinput'
            );
            
        return DataTables::of($retur)
            ->addIndexColumn()
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search != '') {
                    $query->where(function ($query2) use($request) {
                        return $query2
                            ->orWhere('retur.nomor_retur', 'like', '%'.$request->search['value'].'%')
                            ->orWhere('retur.nama_supplier', 'like', '%'.$request->search['value'].'%');
                    }); 
                }
            })
            ->editColumn('tgl_retur', function($query) {
                return Carbon::parse($query->tgl_retur)->format('d-m-Y');
            })
            ->editColumn('grandtotal', function($query) {
                return 'Rp ' . number_format($query->grandtotal, 0, ',', '.');
            })
            ->editColumn('id', function($query) {
                return Crypt::encryptString($query->id);
            })
            ->make(true);
    }
    
    public function getDetail($id)
    {
        try {
            $retur = ReturBarang::with(['details.barang', 'user', 'supplier'])
                ->where('id', $id)
                ->first();
            
            if (!$retur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Retur tidak ditemukan'
                ], 404);
            }

            $detail = $retur->details->map(function ($d) {
                return [
                    'id' => $d->id,
                    'barang_id' => $d->barang_id,
                    'kode_barang' => $d->barang->kode_barang ?? 'N/A',
                    'nama_barang' => $d->barang->nama_barang ?? 'Tidak ditemukan',
                    'qty' => $d->qty,
                    'harga_beli' => $d->harga_beli,
                    'harga_jual' => $d->harga_jual,
                    'subtotal' => $d->subtotal ?? ($d->qty * $d->harga_beli),
                ];
            });

            return response()->json([
                'success' => true,
                'retur' => [
                    'id' => $retur->id,
                    'nomor_retur' => $retur->nomor_retur,
                    'tgl_retur' => Carbon::parse($retur->tgl_retur)->format('d-m-Y'),
                    'idsupplier' => $retur->idsupplier,
                    'kode_supplier' => $retur->kode_supplier,
                    'nama_supplier' => $retur->nama_supplier,
                    'note' => $retur->note ?? '',
                    'grandtotal' => $retur->grandtotal,
                    'user_name' => $retur->user->name ?? '-'
                ],
                'detail' => $detail,
                'grand_total' => $retur->grandtotal
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail retur: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function nota($invoice): View
    {
        $hdr = ReturBarang::join('users', 'users.id', 'retur.created_user')
            ->select('retur.*', 'users.name as petugas')
            ->where('retur.nomor_retur', $invoice)
            ->firstOrFail();

        $dtl = ReturBarangDetail::join('barang', 'barang.id', 'retur_detail.barang_id')
            ->select(
                'barang.nama_barang',
                'barang.kode_barang',
                'retur_detail.qty',
                'retur_detail.harga_beli',
                'retur_detail.harga_jual',
                'retur_detail.subtotal'
            )
            ->where('idretur', $hdr->id)
            ->get();

        return view('transaksi.retur-nota', [
            'hdr' => $hdr,
            'dtl' => $dtl,
        ]);
    }
    
    public function batalkanRetur($id)
    {
        DB::beginTransaction();
        try {
            $retur = ReturBarang::with('details')->findOrFail($id);
            $unitId = $retur->unit_id;
            
            // Kembalikan semua stok
            foreach ($retur->details as $detail) {
                StokUnit::where('unit_id', $unitId)
                    ->where('barang_id', $detail->barang_id)
                    ->increment('stok', $detail->qty);
            }
            
            // Hapus detail
            $retur->details()->delete();
            
            // Hapus header
            $retur->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Retur berhasil dibatalkan'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan retur: ' . $e->getMessage()
            ], 500);
        }
    }
}