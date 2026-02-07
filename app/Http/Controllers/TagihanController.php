<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\PenjualanCicilan;
use App\Models\Anggota;
use App\Models\Barang;
use App\Models\Unit;
use App\Models\StokUnit;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class TagihanController extends Controller
{
    /**
     * Halaman transaksi penjualan voucher
     */
    public function index(Request $request)
    {
        $units = Unit::whereIn('jenis', ['toko', 'bengkel'])
                    ->whereNull('deleted_at')
                    ->pluck('nama_unit', 'id');
        
        // Default unit dari user yang login
        $defaultUnit = auth()->user()->unit_id ?? null;
        
        return view('transaksi.penjualan_voucher.index', compact('units', 'defaultUnit'));
    }

    /**
     * Ambil data barang voucher untuk transaksi
     */
    public function getBarangVoucher(Request $request)
    {
        $unitId = $request->unit_id;
        $search = $request->search;
        
        $barang = Barang::select('barang.id', 'barang.kode_barang', 'barang.nama_barang', 
                    DB::raw('COALESCE(stok_unit.stok, 0) as stok'),
                    'barang.harga_jual', 'barang.satuan')
                ->leftJoin('stok_unit', function($join) use ($unitId) {
                    $join->on('barang.id', '=', 'stok_unit.barang_id')
                         ->where('stok_unit.unit_id', $unitId);
                })
                ->where('barang.kategori', 'voucher') // Filter hanya barang kategori voucher
                ->whereNull('barang.deleted_at')
                ->where(function($query) use ($search) {
                    if (!empty($search)) {
                        $query->where('barang.kode_barang', 'like', "%{$search}%")
                              ->orWhere('barang.nama_barang', 'like', "%{$search}%");
                    }
                })
                ->orderBy('barang.nama_barang')
                ->limit(50)
                ->get();
        
        return response()->json($barang);
    }

    /**
     * Ambil data anggota untuk transaksi
     */
    public function getAnggota(Request $request)
    {
        $search = $request->search;
        
        $anggota = Anggota::select('id', 'nik', 'nama', 'alamat', 'no_hp')
                    ->whereNull('deleted_at')
                    ->where(function($query) use ($search) {
                        if (!empty($search)) {
                            $query->where('nik', 'like', "%{$search}%")
                                  ->orWhere('nama', 'like', "%{$search}%");
                        }
                    })
                    ->orderBy('nama')
                    ->limit(50)
                    ->get();
        
        return response()->json($anggota);
    }

    /**
     * Generate nomor invoice untuk voucher
     */
    public function getInvoice(Request $request)
    {
        $unitId = $request->unit_id;
        $tanggal = $request->tanggal ?? date('Y-m-d');
        
        $unit = Unit::find($unitId);
        if (!$unit) {
            return response()->json(['error' => 'Unit tidak ditemukan'], 404);
        }
        
        // Format: VCHR/{unit_code}/{tanggal}/XXX
        $prefix = 'VCHR/' . strtoupper(substr($unit->nama_unit, 0, 3)) . '/' . date('ymd', strtotime($tanggal));
        
        // Cari nomor terakhir hari ini
        $lastInvoice = Penjualan::where('nomor_invoice', 'like', $prefix . '/%')
                        ->whereDate('tanggal', $tanggal)
                        ->orderBy('id', 'desc')
                        ->first();
        
        if ($lastInvoice) {
            $lastNumber = intval(substr($lastInvoice->nomor_invoice, -3));
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }
        
        $invoice = $prefix . '/' . $newNumber;
        
        return response()->json(['invoice' => $invoice]);
    }

    /**
     * Proses transaksi penjualan voucher
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'unit_id' => 'required|exists:unit,id',
            'anggota_id' => 'required|exists:anggota,id',
            'tanggal' => 'required|date',
            'nomor_invoice' => 'required|unique:penjualan,nomor_invoice',
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:barang,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.harga' => 'required|numeric|min:0',
            'items.*.subtotal' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'metode_bayar' => 'required|in:tunai,cicilan',
            'keterangan' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $unitId = $request->unit_id;
            $anggotaId = $request->anggota_id;
            $total = $request->total;
            $metodeBayar = $request->metode_bayar;
            
            // 1. Simpan data penjualan
            $penjualan = Penjualan::create([
                'nomor_invoice' => $request->nomor_invoice,
                'tanggal' => $request->tanggal,
                'anggota_id' => $anggotaId,
                'unit_id' => $unitId,
                'customer' => Anggota::find($anggotaId)->nama,
                'subtotal' => $total,
                'diskon' => 0,
                'grandtotal' => $total,
                'metode_bayar' => $metodeBayar,
                'type_order' => 'offline',
                'status_ambil' => 'pending',
                'status_cicilan' => $metodeBayar == 'cicilan' ? 'belum_lunas' : 'lunas',
                'keterangan' => $request->keterangan ?? 'Penjualan Voucher',
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);

            // 2. Simpan detail penjualan dan kurangi stok
            foreach ($request->items as $item) {
                $barangId = $item['barang_id'];
                $qty = $item['qty'];
                $harga = $item['harga'];
                
                // Validasi stok
                $stok = StokUnit::where('barang_id', $barangId)
                        ->where('unit_id', $unitId)
                        ->first();
                
                if (!$stok || $stok->stok < $qty) {
                    throw new \Exception("Stok tidak cukup untuk barang: " . Barang::find($barangId)->nama_barang);
                }

                // Kurangi stok
                $stok->decrement('stok', $qty);
                $stok->save();

                // Simpan detail penjualan
                PenjualanDetail::create([
                    'penjualan_id' => $penjualan->id,
                    'barang_id' => $barangId,
                    'qty' => $qty,
                    'harga' => $harga,
                    'subtotal' => $item['subtotal'],
                    'created_at' => now(),
                ]);
            }

            // 3. Jika metode cicilan, buat data cicilan voucher
            if ($metodeBayar == 'cicilan') {
                $cicilanData = $this->generateCicilanVoucher($penjualan, $request->items);
                
                PenjualanCicilan::create([
                    'anggota_id' => $anggotaId,
                    'penjualan_id' => $penjualan->id,
                    'cicilan' => 1,
                    'pokok' => $total,
                    'bunga' => 0,
                    'total_cicilan' => $total,
                    'status' => 'belum_lunas',
                    'kategori' => 'voucher',
                    'periode_tagihan' => date('Ym'),
                    'status_bayar' => 0,
                    'created_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi voucher berhasil disimpan',
                'invoice' => $penjualan->nomor_invoice,
                'penjualan_id' => $penjualan->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate data cicilan untuk voucher
     */
    private function generateCicilanVoucher($penjualan, $items)
    {
        // Logika untuk menentukan cicilan voucher
        // Bisa disesuaikan dengan kebijakan perusahaan
        return [
            'total' => $penjualan->grandtotal,
            'tenor' => 1, // Default tenor 1 bulan untuk voucher
            'bunga' => 0, // Bunga 0% untuk voucher
        ];
    }

    /**
     * Cetak nota voucher
     */
    public function nota($invoice)
    {
        $penjualan = Penjualan::with(['anggota', 'unit', 'details.barang'])
                    ->where('nomor_invoice', $invoice)
                    ->firstOrFail();
        
        return view('transaksi.penjualan_voucher.nota', compact('penjualan'));
    }

    /**
     * Halaman riwayat penjualan voucher
     */
    public function riwayat(Request $request)
    {
        $units = Unit::whereIn('jenis', ['toko', 'bengkel'])
                    ->whereNull('deleted_at')
                    ->pluck('nama_unit', 'id');
        
        $startDate = $request->start_date ?? date('Y-m-01');
        $endDate = $request->end_date ?? date('Y-m-d');
        
        return view('transaksi.penjualan_voucher.riwayat', compact('units', 'startDate', 'endDate'));
    }

    /**
     * Data untuk riwayat penjualan voucher (DataTables)
     */
    public function riwayatData(Request $request)
    {
        $query = Penjualan::with(['anggota', 'unit', 'details.barang'])
                ->whereHas('details.barang', function($q) {
                    $q->where('kategori', 'voucher');
                })
                ->whereNull('deleted_at')
                ->orderBy('tanggal', 'desc')
                ->orderBy('id', 'desc');

        // Filter tanggal
        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->whereDate('tanggal', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->whereDate('tanggal', '<=', $request->end_date);
        }

        // Filter unit
        if ($request->has('unit_id') && !empty($request->unit_id) && $request->unit_id != 'all') {
            $query->where('unit_id', $request->unit_id);
        }

        // Filter status cicilan
        if ($request->has('status_cicilan') && !empty($request->status_cicilan) && $request->status_cicilan != 'all') {
            $query->where('status_cicilan', $request->status_cicilan);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('aksi', function($row) {
                $btn = '<div class="btn-group btn-group-sm">';
                $btn .= '<a href="' . route('penjualan_voucher.nota', $row->nomor_invoice) . '" 
                         class="btn btn-info" target="_blank" title="Cetak Nota">
                         <i class="bi bi-printer"></i></a>';
                
                if ($row->status_cicilan == 'belum_lunas') {
                    $btn .= '<button class="btn btn-success btn-lunasi" 
                             data-id="' . $row->id . '" 
                             data-invoice="' . $row->nomor_invoice . '"
                             title="Lunasi Voucher">
                             <i class="bi bi-check-circle"></i></button>';
                }
                
                $btn .= '</div>';
                return $btn;
            })
            ->editColumn('tanggal', function($row) {
                return date('d/m/Y', strtotime($row->tanggal));
            })
            ->editColumn('grandtotal', function($row) {
                return 'Rp ' . number_format($row->grandtotal, 0, ',', '.');
            })
            ->editColumn('status_cicilan', function($row) {
                $badge = $row->status_cicilan == 'lunas' ? 'success' : 'warning';
                $text = $row->status_cicilan == 'lunas' ? 'Lunas' : 'Belum Lunas';
                return '<span class="badge bg-' . $badge . '">' . $text . '</span>';
            })
            ->addColumn('detail_barang', function($row) {
                $detail = '';
                foreach ($row->details as $detailItem) {
                    $detail .= $detailItem->barang->nama_barang . ' (' . $detailItem->qty . ')<br>';
                }
                return $detail;
            })
            ->rawColumns(['aksi', 'status_cicilan', 'detail_barang'])
            ->make(true);
    }

    /**
     * Proses pelunasan voucher
     */
    public function pelunasan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'penjualan_id' => 'required|exists:penjualan,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $penjualanId = $request->penjualan_id;
            
            // Update status penjualan
            $penjualan = Penjualan::find($penjualanId);
            $penjualan->update([
                'status_cicilan' => 'lunas',
                'updated_at' => now(),
            ]);

            // Update data cicilan jika ada
            $cicilan = PenjualanCicilan::where('penjualan_id', $penjualanId)
                        ->where('kategori', 'voucher')
                        ->first();
            
            if ($cicilan) {
                $cicilan->update([
                    'status' => 'lunas',
                    'status_bayar' => 1,
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Voucher berhasil dilunasi'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal melunasi voucher: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Halaman laporan penjualan voucher (mirip dengan permintaan awal)
     */
    public function laporan(Request $request)
    {
        $bulan = $request->get('bulan', date('Y-m'));
        
        $units = Unit::whereIn('jenis', ['toko', 'bengkel'])
                    ->whereNull('deleted_at')
                    ->pluck('nama_unit', 'id');

        return view('transaksi.penjualan_voucher.laporan', compact('bulan', 'units'));
    }

    /**
     * Data untuk laporan penjualan voucher (DataTables)
     */
    public function laporanData(Request $request)
    {
        $bulan = $request->input('bulan', date('Y-m'));
        $unitId = $request->input('unit', 'all');

        $query = PenjualanCicilan::select(
                'penjualan_cicilan.id',
                'penjualan_cicilan.anggota_id',
                'penjualan_cicilan.penjualan_id',
                'penjualan_cicilan.cicilan',
                'penjualan_cicilan.pokok',
                'penjualan_cicilan.bunga',
                'penjualan_cicilan.total_cicilan',
                'penjualan_cicilan.status',
                'penjualan_cicilan.kategori',
                'penjualan_cicilan.periode_tagihan',
                'penjualan_cicilan.status_bayar',
                'penjualan_cicilan.created_at',
                'penjualan_cicilan.updated_at',
                'anggota.nama as nama_anggota',
                'anggota.nik',
                'penjualan.nomor_invoice',
                'penjualan.tanggal as tanggal_penjualan',
                'unit.nama_unit',
                DB::raw("DATE_FORMAT(penjualan_cicilan.created_at, '%Y-%m') as bulan_transaksi")
            )
            ->join('anggota', 'penjualan_cicilan.anggota_id', '=', 'anggota.id')
            ->join('penjualan', 'penjualan_cicilan.penjualan_id', '=', 'penjualan.id')
            ->leftJoin('unit', 'penjualan.unit_id', '=', 'unit.id')
            ->whereNull('penjualan_cicilan.deleted_at')
            ->whereNull('penjualan.deleted_at')
            ->where('penjualan_cicilan.kategori', 'voucher')
            ->whereRaw("DATE_FORMAT(penjualan_cicilan.created_at, '%Y-%m') = ?", [$bulan]);

        if ($unitId != 'all') {
            $query->where('penjualan.unit_id', $unitId);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function($row) {
                $btn = '';
                if ($row->status != 'lunas') {
                    $btn = '<button class="btn btn-sm btn-success btn-pelunasan" 
                            data-id="'.$row->id.'" 
                            data-invoice="'.$row->nomor_invoice.'"
                            data-nama="'.$row->nama_anggota.'">
                            <i class="bi bi-check-circle"></i> Lunas
                        </button>';
                } else {
                    $btn = '<span class="badge bg-success">Lunas</span>';
                }
                return $btn;
            })
            ->editColumn('total_cicilan', function($row) {
                return 'Rp ' . number_format($row->total_cicilan, 0, ',', '.');
            })
            ->editColumn('pokok', function($row) {
                return 'Rp ' . number_format($row->pokok, 0, ',', '.');
            })
            ->editColumn('bunga', function($row) {
                return 'Rp ' . number_format($row->bunga, 0, ',', '.');
            })
            ->editColumn('status', function($row) {
                $badge = $row->status == 'lunas' ? 'success' : 'warning';
                return '<span class="badge bg-'.$badge.'">'.ucfirst($row->status).'</span>';
            })
            ->editColumn('created_at', function($row) {
                return date('d/m/Y H:i', strtotime($row->created_at));
            })
            ->editColumn('tanggal_penjualan', function($row) {
                return date('d/m/Y', strtotime($row->tanggal_penjualan));
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }

    /**
     * Export laporan penjualan voucher ke Excel
     */
    public function exportExcel(Request $request)
    {
        $bulan = $request->bulan ?? date('Y-m');
        $unitId = $request->unit ?? 'all';
        
        $data = PenjualanCicilan::with(['anggota', 'penjualan.unit'])
                ->where('kategori', 'voucher')
                ->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$bulan])
                ->when($unitId != 'all', function($query) use ($unitId) {
                    $query->whereHas('penjualan', function($q) use ($unitId) {
                        $q->where('unit_id', $unitId);
                    });
                })
                ->get();
        
        // Generate Excel (gunakan library Maatwebsite/Laravel-Excel)
        // Atau return data untuk diproses di view
        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $data->sum('total_cicilan')
        ]);
    }
}