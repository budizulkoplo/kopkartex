<?php

namespace App\Http\Controllers;

use App\Models\ModalAwal;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Penerimaan;
use App\Models\PenjualanCicil;
use App\Exports\LaporanPenerimaanExport;
use App\Models\Barang;
use App\Models\Pinbrg;
use App\Models\StockAdjustmentDetail;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;

class LaporanController extends Controller
{
    public function penerimaanLaporan(Request $request)
    {
        // default bulan berjalan
        $bulan = $request->get('bulan', now()->format('Y-m'));
        $start = \Carbon\Carbon::parse($bulan . '-01')->startOfMonth();
        $end   = \Carbon\Carbon::parse($bulan . '-01')->endOfMonth();

        if ($end->gt(now())) {
            $end = now(); // jangan melebihi hari ini
        }

        return view('laporan.penerimaan', [
            'bulan' => $bulan,
            'start' => $start->toDateString(),
            'end'   => $end->toDateString(),
        ]);
    }

    public function penerimaanData(Request $request)
    {
        $bulan = $request->get('bulan', now()->format('Y-m'));
        $start = \Carbon\Carbon::parse($bulan . '-01')->startOfMonth();
        $end   = \Carbon\Carbon::parse($bulan . '-01')->endOfMonth();

        if ($end->gt(now())) {
            $end = now();
        }

        $data = DB::table('penerimaan as p')
            ->join('penerimaan_detail as d', 'p.idpenerimaan', '=', 'd.idpenerimaan')
            ->join('barang as b', 'd.barang_id', '=', 'b.id')
            ->select(
                'p.tgl_penerimaan',
                'p.nomor_invoice',
                'p.nama_supplier',
                'b.kode_barang',
                'b.nama_barang',
                'd.jumlah'
            )
            ->whereNull('p.deleted_at')
            ->whereBetween('p.tgl_penerimaan', [$start, $end])
            ->orderBy('p.tgl_penerimaan')
            ->orderBy('p.nomor_invoice')
            ->get();

        return response()->json(['data' => $data]);
    }

    public function stokBarang()
    {
        $units = DB::table('unit')->whereNull('deleted_at')->pluck('nama_unit', 'id');

        $data = DB::table('barang as b')
            ->select(
                'b.kode_barang',
                'b.nama_barang',
                ...$units->map(function ($nama, $id) {
                    return DB::raw("SUM(CASE WHEN su.unit_id = $id THEN su.stok ELSE 0 END) as `$nama`");
                })->toArray()
            )
            ->leftJoin('stok_unit as su', 'b.id', '=', 'su.barang_id')
            ->whereNull('b.deleted_at')
            ->groupBy('b.id', 'b.kode_barang', 'b.nama_barang')
            ->orderBy('b.nama_barang')
            ->get();

        return view('laporan.stok_barang', [
            'units' => $units,
            'data'  => $data,
        ]);
    }
    public function stokBarangData(Request $request)
    {
        //$unit = DB::table('unit')->whereNull('deleted_at')->pluck('nama_unit', 'id');
        $unit = Unit::pluck('nama_unit', 'id');

        $data = Barang::select(
                'barang.kode_barang',
                'barang.nama_barang',
                ...$unit->map(function ($nama, $id) {
                    return DB::raw("SUM(CASE WHEN su.unit_id = $id THEN su.stok ELSE 0 END) as `$nama`");
                })->toArray()
            )
            ->leftJoin('stok_unit as su', 'barang.id', '=', 'su.barang_id')
            ->groupBy('barang.id', 'barang.kode_barang', 'barang.nama_barang');
        return DataTables::of($data)
            ->addIndexColumn()
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value'] != '') {
                    $query->where(function ($q) use ($request) {
                        $q->orWhere('barang.kode_barang', 'like', '%' . $request->search['value'] . '%')
                          ->orWhere('barang.nama_barang', 'like', '%' . $request->search['value'] . '%');
                    });
                }
            })
            ->make(true);
        //return response()->json(['data' => $data]);
    }

    public function stokDetail(Request $request)
    {
        $bulan = $request->get('bulan', now()->format('Y-m'));
        $keyword = trim((string) $request->get('keyword', ''));
        $unitId = Auth::user()->unit_kerja;
        $unit = Unit::find($unitId);

        $summary = $this->buildStokDetailSummary($bulan, $unitId, $keyword);

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 25;
        $items = $summary->slice(($page - 1) * $perPage, $perPage)->values();

        $adjustments = new LengthAwarePaginator(
            $items,
            $summary->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('laporan.stok_detail', [
            'bulan' => $bulan,
            'keyword' => $keyword,
            'unit' => $unit,
            'rows' => $adjustments,
            'totals' => [
                'opening_stock' => $summary->sum('opening_stock'),
                'penerimaan_qty' => $summary->sum('penerimaan_qty'),
                'retur_qty' => $summary->sum('retur_qty'),
                'penjualan_qty' => $summary->sum('penjualan_qty'),
                'adjustment_qty' => $summary->sum('adjustment_qty'),
                'calculated_stock' => $summary->sum('calculated_stock'),
                'system_stock' => $summary->sum('system_stock'),
                'selisih' => $summary->sum('selisih'),
                'nominal_calculated' => $summary->sum('nominal_calculated'),
                'nominal_system' => $summary->sum('nominal_system'),
            ],
        ]);
    }

    public function stokDetailData(Request $request)
    {
        $bulan = $request->get('bulan', now()->format('Y-m'));
        $keyword = trim((string) $request->get('keyword', ''));
        $unitId = Auth::user()->unit_kerja;
        $unit = Unit::find($unitId);
        $summary = $this->buildStokDetailSummary($bulan, $unitId, $keyword)->values();

        return response()->json([
            'success' => true,
            'unit' => $unit?->nama_unit ?? '-',
            'bulan' => $bulan,
            'keyword' => $keyword,
            'rows' => $summary,
            'totals' => [
                'opening_stock' => $summary->sum('opening_stock'),
                'penerimaan_qty' => $summary->sum('penerimaan_qty'),
                'retur_qty' => $summary->sum('retur_qty'),
                'penjualan_qty' => $summary->sum('penjualan_qty'),
                'adjustment_qty' => $summary->sum('adjustment_qty'),
                'calculated_stock' => $summary->sum('calculated_stock'),
                'system_stock' => $summary->sum('system_stock'),
                'selisih' => $summary->sum('selisih'),
                'nominal_calculated' => $summary->sum('nominal_calculated'),
                'nominal_system' => $summary->sum('nominal_system'),
            ],
        ]);
    }

    public function stokDetailExport(Request $request)
    {
        $bulan = $request->get('bulan', now()->format('Y-m'));
        $keyword = trim((string) $request->get('keyword', ''));
        $unitId = Auth::user()->unit_kerja;
        $unit = Unit::find($unitId);
        $summary = $this->buildStokDetailSummary($bulan, $unitId, $keyword)->values();

        $headers = [
            'No',
            'Kode Barang',
            'Nama Barang',
            'Satuan',
            'Stok Awal',
            'Penerimaan',
            'Retur',
            'Penjualan',
            'Adjustment',
            'Stok Hitung',
            'Stok Sistem',
            'Selisih',
            'Nilai Hitung',
            'Nilai Sistem',
        ];

        $rows = $summary->values()->map(function ($row, $index) {
            return [
                $index + 1,
                $row['kode_barang'],
                $row['nama_barang'],
                $row['satuan'],
                $row['opening_stock'],
                $row['penerimaan_qty'],
                $row['retur_qty'],
                $row['penjualan_qty'],
                $row['adjustment_qty'],
                $row['calculated_stock'],
                $row['system_stock'],
                $row['selisih'],
                $row['nominal_calculated'],
                $row['nominal_system'],
            ];
        })->all();

        $totals = [[
            '',
            '',
            '',
            'TOTAL',
            $summary->sum('opening_stock'),
            $summary->sum('penerimaan_qty'),
            $summary->sum('retur_qty'),
            $summary->sum('penjualan_qty'),
            $summary->sum('adjustment_qty'),
            $summary->sum('calculated_stock'),
            $summary->sum('system_stock'),
            $summary->sum('selisih'),
            $summary->sum('nominal_calculated'),
            $summary->sum('nominal_system'),
        ]];

        return $this->downloadHtmlTableAsExcel(
            'stok_detail_' . str_replace('-', '', $bulan) . '_' . now()->format('YmdHis') . '.xls',
            'Laporan Stok Detail',
            [
                'Periode' => $bulan,
                'Unit' => $unit?->nama_unit ?? '-',
                'Keyword' => $keyword !== '' ? $keyword : 'Semua barang',
            ],
            $headers,
            $rows,
            $totals
        );
    }

    public function stokDetailHistory(Request $request, $barangId)
    {
        $bulan = $request->get('bulan', now()->format('Y-m'));
        $unitId = Auth::user()->unit_kerja;
        $start = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();
        $end = Carbon::createFromFormat('Y-m', $bulan)->endOfMonth();
        $openingPeriod = $start->copy()->subMonth()->format('Y-m');

        $barang = Barang::with('satuanRelation')->findOrFail($barangId);
        $modalAwal = ModalAwal::query()
            ->where('unit_id', $unitId)
            ->where('periode', $openingPeriod)
            ->where('barang_id', $barangId)
            ->first();

        $history = collect();
        $runningStock = (float) ($modalAwal->stok ?? 0);

        $history->push([
            'tanggal' => $start->copy()->subSecond()->format('Y-m-d H:i:s'),
            'jenis' => 'Modal Awal',
            'referensi' => 'OPNAME-' . $openingPeriod,
            'qty_masuk' => $runningStock,
            'qty_keluar' => 0,
            'saldo' => $runningStock,
            'keterangan' => 'Stok awal dari stock opname periode ' . $openingPeriod,
        ]);

        $penerimaan = DB::table('penerimaan_detail as pd')
            ->join('penerimaan as p', 'p.idpenerimaan', '=', 'pd.idpenerimaan')
            ->join('users as u', 'u.id', '=', 'p.user_id')
            ->whereNull('p.deleted_at')
            ->where('u.unit_kerja', $unitId)
            ->where('pd.barang_id', $barangId)
            ->whereBetween('p.tgl_penerimaan', [$start, $end])
            ->select([
                'p.tgl_penerimaan as tanggal',
                DB::raw("'Penerimaan' as jenis"),
                'p.nomor_invoice as referensi',
                'pd.jumlah as qty_masuk',
                DB::raw('0 as qty_keluar'),
                DB::raw("CONCAT('Supplier: ', COALESCE(p.nama_supplier, '-')) as keterangan"),
            ])
            ->get();

        $penjualan = DB::table('penjualan_detail as pd')
            ->join('penjualan as p', 'p.id', '=', 'pd.penjualan_id')
            ->whereNull('p.deleted_at')
            ->where('p.unit_id', $unitId)
            ->where('pd.barang_id', $barangId)
            ->whereBetween('p.tanggal', [$start, $end])
            ->select([
                'p.tanggal as tanggal',
                DB::raw("'Penjualan' as jenis"),
                'p.nomor_invoice as referensi',
                DB::raw('0 as qty_masuk'),
                'pd.qty as qty_keluar',
                DB::raw("CONCAT('Customer: ', COALESCE(p.customer, '-')) as keterangan"),
            ])
            ->get();

        $retur = DB::table('retur_detail as rd')
            ->join('retur as r', 'r.id', '=', 'rd.idretur')
            ->whereNull('r.deleted_at')
            ->where('r.unit_id', $unitId)
            ->where('rd.barang_id', $barangId)
            ->whereBetween('r.tgl_retur', [$start, $end])
            ->select([
                'r.tgl_retur as tanggal',
                DB::raw("'Retur' as jenis"),
                'r.nomor_retur as referensi',
                DB::raw('0 as qty_masuk'),
                'rd.qty as qty_keluar',
                DB::raw("CONCAT('Supplier: ', COALESCE(r.nama_supplier, '-')) as keterangan"),
            ])
            ->get();

        $adjustment = DB::table('stock_adjustment_details as sad')
            ->join('stock_adjustments as sa', 'sa.id', '=', 'sad.stock_adjustment_id')
            ->where('sa.unit_id', $unitId)
            ->where('sad.barang_id', $barangId)
            ->whereBetween('sa.tanggal_adjustment', [$start, $end])
            ->select([
                'sa.tanggal_adjustment as tanggal',
                DB::raw("'Adjustment' as jenis"),
                'sa.kode_adjustment as referensi',
                DB::raw('CASE WHEN (sad.new_stock - sad.old_stock) > 0 THEN (sad.new_stock - sad.old_stock) ELSE 0 END as qty_masuk'),
                DB::raw('CASE WHEN (sad.new_stock - sad.old_stock) < 0 THEN ABS(sad.new_stock - sad.old_stock) ELSE 0 END as qty_keluar'),
                'sad.note as keterangan',
            ])
            ->get();

        $details = $history
            ->merge($penerimaan)
            ->merge($penjualan)
            ->merge($retur)
            ->merge($adjustment)
            ->sortBy('tanggal')
            ->values()
            ->map(function ($row) {
                return is_array($row) ? $row : (array) $row;
            })
            ->values()
            ->map(function ($row, $index) use (&$runningStock) {
                if ($index > 0) {
                    $runningStock += (float) $row['qty_masuk'];
                    $runningStock -= (float) $row['qty_keluar'];
                }

                return [
                    'tanggal' => Carbon::parse($row['tanggal'])->format('d-m-Y H:i'),
                    'jenis' => $row['jenis'],
                    'referensi' => $row['referensi'],
                    'qty_masuk' => (float) $row['qty_masuk'],
                    'qty_keluar' => (float) $row['qty_keluar'],
                    'saldo' => $runningStock,
                    'keterangan' => $row['keterangan'] ?? '-',
                ];
            });

        $summary = $this->buildStokDetailSummary($bulan, $unitId)->firstWhere('barang_id', (int) $barangId);

        return response()->json([
            'success' => true,
            'header' => [
                'barang' => $barang->kode_barang . ' - ' . $barang->nama_barang,
                'satuan' => $barang->satuanRelation->name ?? '-',
                'bulan' => $bulan,
                'stok_awal' => (float) ($summary['opening_stock'] ?? 0),
                'stok_hitung' => (float) ($summary['calculated_stock'] ?? 0),
                'stok_sistem' => (float) ($summary['system_stock'] ?? 0),
                'selisih' => (float) ($summary['selisih'] ?? 0),
                'nominal_sistem' => (float) ($summary['nominal_system'] ?? 0),
            ],
            'details' => $details,
        ]);
    }

    protected function buildStokDetailSummary(string $bulan, int $unitId, string $keyword = '')
    {
        $start = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();
        $end = Carbon::createFromFormat('Y-m', $bulan)->endOfMonth();
        $openingPeriod = $start->copy()->subMonth()->format('Y-m');

        $openingSub = ModalAwal::query()
            ->select([
                'barang_id',
                DB::raw('SUM(stok) as opening_stock'),
                DB::raw('MAX(harga_modal) as opening_price'),
            ])
            ->where('unit_id', $unitId)
            ->where('periode', $openingPeriod)
            ->groupBy('barang_id');

        $penerimaanSub = DB::table('penerimaan_detail as pd')
            ->join('penerimaan as p', 'p.idpenerimaan', '=', 'pd.idpenerimaan')
            ->join('users as u', 'u.id', '=', 'p.user_id')
            ->whereNull('p.deleted_at')
            ->where('u.unit_kerja', $unitId)
            ->whereBetween('p.tgl_penerimaan', [$start, $end])
            ->groupBy('pd.barang_id')
            ->select('pd.barang_id', DB::raw('SUM(pd.jumlah) as penerimaan_qty'));

        $returSub = DB::table('retur_detail as rd')
            ->join('retur as r', 'r.id', '=', 'rd.idretur')
            ->whereNull('r.deleted_at')
            ->where('r.unit_id', $unitId)
            ->whereBetween('r.tgl_retur', [$start, $end])
            ->groupBy('rd.barang_id')
            ->select('rd.barang_id', DB::raw('SUM(rd.qty) as retur_qty'));

        $penjualanSub = DB::table('penjualan_detail as pd')
            ->join('penjualan as p', 'p.id', '=', 'pd.penjualan_id')
            ->whereNull('p.deleted_at')
            ->where('p.unit_id', $unitId)
            ->whereBetween('p.tanggal', [$start, $end])
            ->groupBy('pd.barang_id')
            ->select('pd.barang_id', DB::raw('SUM(pd.qty) as penjualan_qty'));

        $adjustmentSub = DB::table('stock_adjustment_details as sad')
            ->join('stock_adjustments as sa', 'sa.id', '=', 'sad.stock_adjustment_id')
            ->where('sa.unit_id', $unitId)
            ->whereBetween('sa.tanggal_adjustment', [$start, $end])
            ->groupBy('sad.barang_id')
            ->select('sad.barang_id', DB::raw('SUM(sad.new_stock - sad.old_stock) as adjustment_qty'));

        $rows = DB::table('barang as b')
            ->leftJoin('satuan as s', 's.id', '=', 'b.idsatuan')
            ->leftJoin('stok_unit as su', function ($join) use ($unitId) {
                $join->on('su.barang_id', '=', 'b.id')
                    ->where('su.unit_id', '=', $unitId)
                    ->whereNull('su.deleted_at');
            })
            ->leftJoinSub($openingSub, 'opening', function ($join) {
                $join->on('opening.barang_id', '=', 'b.id');
            })
            ->leftJoinSub($penerimaanSub, 'penerimaan', function ($join) {
                $join->on('penerimaan.barang_id', '=', 'b.id');
            })
            ->leftJoinSub($returSub, 'retur', function ($join) {
                $join->on('retur.barang_id', '=', 'b.id');
            })
            ->leftJoinSub($penjualanSub, 'penjualan', function ($join) {
                $join->on('penjualan.barang_id', '=', 'b.id');
            })
            ->leftJoinSub($adjustmentSub, 'adjustment', function ($join) {
                $join->on('adjustment.barang_id', '=', 'b.id');
            })
            ->whereNull('b.deleted_at')
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($builder) use ($keyword) {
                    $builder->where('b.kode_barang', 'like', '%' . $keyword . '%')
                        ->orWhere('b.nama_barang', 'like', '%' . $keyword . '%');
                });
            })
            ->select([
                'b.id as barang_id',
                'b.kode_barang',
                'b.nama_barang',
                'b.harga_beli',
                DB::raw('COALESCE(s.name, "-") as satuan'),
                DB::raw('COALESCE(opening.opening_stock, 0) as opening_stock'),
                DB::raw('COALESCE(opening.opening_price, b.harga_beli, 0) as opening_price'),
                DB::raw('COALESCE(penerimaan.penerimaan_qty, 0) as penerimaan_qty'),
                DB::raw('COALESCE(retur.retur_qty, 0) as retur_qty'),
                DB::raw('COALESCE(penjualan.penjualan_qty, 0) as penjualan_qty'),
                DB::raw('COALESCE(adjustment.adjustment_qty, 0) as adjustment_qty'),
                DB::raw('COALESCE(su.stok, 0) as system_stock'),
            ])
            ->orderBy('b.nama_barang')
            ->get()
            ->map(function ($row) {
                $calculatedStock = (float) $row->opening_stock
                    + (float) $row->penerimaan_qty
                    - (float) $row->retur_qty
                    - (float) $row->penjualan_qty
                    + (float) $row->adjustment_qty;

                $systemStock = (float) $row->system_stock;
                $hargaModal = (float) ($row->opening_price ?: $row->harga_beli ?: 0);

                return [
                    'barang_id' => (int) $row->barang_id,
                    'kode_barang' => $row->kode_barang,
                    'nama_barang' => $row->nama_barang,
                    'satuan' => $row->satuan,
                    'harga_modal' => $hargaModal,
                    'opening_stock' => (float) $row->opening_stock,
                    'penerimaan_qty' => (float) $row->penerimaan_qty,
                    'retur_qty' => (float) $row->retur_qty,
                    'penjualan_qty' => (float) $row->penjualan_qty,
                    'adjustment_qty' => (float) $row->adjustment_qty,
                    'calculated_stock' => $calculatedStock,
                    'system_stock' => $systemStock,
                    'selisih' => $systemStock - $calculatedStock,
                    'nominal_calculated' => $calculatedStock * $hargaModal,
                    'nominal_system' => $systemStock * $hargaModal,
                ];
            })
            ->filter(function ($row) use ($keyword) {
                if ($keyword !== '') {
                    return true;
                }

                return $row['opening_stock'] != 0
                    || $row['penerimaan_qty'] != 0
                    || $row['retur_qty'] != 0
                    || $row['penjualan_qty'] != 0
                    || $row['adjustment_qty'] != 0
                    || $row['system_stock'] != 0;
            })
            ->values();

        return $rows;
    }
    // public function stokBarangDatasss()
    // {
    //     $unit = DB::table('unit')->whereNull('deleted_at')->pluck('nama_unit', 'id');

    //     $data = DB::table('barang as b')
    //         ->select(
    //             'b.kode_barang',
    //             'b.nama_barang',
    //             ...$unit->map(function ($nama, $id) {
    //                 return DB::raw("SUM(CASE WHEN su.unit_id = $id THEN su.stok ELSE 0 END) as `$nama`");
    //             })->toArray()
    //         )
    //         ->leftJoin('stok_unit as su', 'b.id', '=', 'su.barang_id')
    //         ->whereNull('b.deleted_at')
    //         ->groupBy('b.id', 'b.kode_barang', 'b.nama_barang')
    //         ->orderBy('b.nama_barang')
    //         ->get();

    //     return response()->json(['data' => $data]);
    // }

    public function penjualan(Request $request)
    {
        // Default bulan berjalan
        $bulan = $request->input('bulan', date('Y-m'));
        $start_date = $bulan . '-01';
        $end_date   = date('Y-m-t', strtotime($start_date));

        // Kalau bulan ini, end_date dipotong sampai hari ini
        if ($bulan == date('Y-m')) {
            $end_date = date('Y-m-d');
        }

        // Ambil unit hanya yang jenis toko & bengkel
        $units = DB::table('unit')
            ->whereNull('deleted_at')
            ->whereIn('jenis', ['toko', 'bengkel'])
            ->pluck('nama_unit', 'id');

        return view('laporan.penjualan', [
            'units'      => $units,
            'bulan'      => $bulan,
            'start_date' => $start_date,
            'end_date'   => $end_date,
        ]);
    }

    public function penjualanData(Request $request)
    {
        $bulan = $request->input('bulan', date('Y-m'));
        $start_date = $bulan . '-01';
        $end_date   = date('Y-m-t', strtotime($start_date));

        if ($bulan == date('Y-m')) {
            $end_date = date('Y-m-d');
        }

        // Ambil unit hanya yang jenis toko & bengkel
        $units = DB::table('unit')
            ->whereNull('deleted_at')
            ->whereIn('jenis', ['toko', 'bengkel'])
            ->pluck('nama_unit', 'id');

        $dates = [];
        $period = new \DatePeriod(
            new \DateTime($start_date),
            new \DateInterval('P1D'),
            (new \DateTime($end_date))->modify('+1 day')
        );

        foreach ($period as $date) {
            $row = ['tanggal' => $date->format('Y-m-d')];

            foreach ($units as $unit_id => $unit_name) {
                $jenisUnit = DB::table('unit')->where('id', $unit_id)->value('jenis');

                if ($jenisUnit === 'bengkel') {
                    $total = DB::table('transaksi_bengkels')
                        ->whereDate('tanggal', $date->format('Y-m-d'))
                        ->whereNull('deleted_at')
                        ->sum('grandtotal');
                } else {
                    $total = DB::table('penjualan')
                        ->whereDate('tanggal', $date->format('Y-m-d'))
                        ->where('unit_id', $unit_id)
                        ->whereNull('deleted_at')
                        ->sum('grandtotal');
                }

                $row[$unit_name] = (float)$total;
            }

            $dates[] = $row;
        }

        // ubah jadi DataTables
        return DataTables::of($dates)->make(true);
    }

    public function retur(Request $request)
    {
        // Default tanggal hari ini
        $tanggal = $request->input('tanggal', date('Y-m-d'));

        return view('laporan.retur', [
            'tanggal' => $tanggal,
        ]);
    }

    public function returData(Request $request)
    {
        $tanggal = $request->input('tanggal', date('Y-m-d'));

        $retur = DB::table('retur')
            ->join('retur_detail', 'retur.id', '=', 'retur_detail.idretur')
            ->join('barang', 'retur_detail.barang_id', '=', 'barang.id')
            ->join('unit', 'retur.unit_id', '=', 'unit.id')
            ->select(
                'retur.id as idretur',
                'retur.tgl_retur',
                'retur.nama_supplier',
                'retur.note',
                'unit.nama_unit',
                'barang.kode_barang',
                'barang.nama_barang',
                'retur_detail.qty'
            )
            ->whereDate('retur.tgl_retur', $tanggal)
            ->whereNull('retur.deleted_at')
            ->whereNull('retur_detail.deleted_at')
            ->orderBy('retur.tgl_retur')
            ->orderBy('retur.id')
            ->get();

        // Format ulang untuk DataTables (biar gampang rowspan di blade)
        $grouped = [];
        foreach ($retur as $row) {
            $grouped[$row->idretur]['header'] = [
                'tgl_retur'    => $row->tgl_retur,
                'supplier'     => $row->nama_supplier,
                'unit'         => $row->nama_unit,
            ];
            $grouped[$row->idretur]['details'][] = [
                'kode_barang' => $row->kode_barang,
                'nama_barang' => $row->nama_barang,
                'qty'         => $row->qty,
            ];
        }

        return response()->json(['data' => array_values($grouped)]);
    }

     public function stokOpname(Request $request)
    {
        $bulan = $request->get('bulan', date('Y-m'));
        $unit  = $request->get('unit', 'all');

        // untuk dropdown filter unit
        $units = DB::table('unit')->select('id','nama_unit')->orderBy('nama_unit')->get();

        return view('laporan.stokopname', compact('bulan','unit','units'));
    }

    public function stokOpnameData(Request $request)
    {
        $bulan = $request->get('bulan', date('Y-m'));
        $unit  = $request->get('unit', 'all');

        $query = DB::table('stock_opname as so')
            ->leftJoin('unit as u', 'u.id', '=', 'so.id_unit')
            ->leftJoin('barang as b', 'b.id', '=', 'so.id_barang')
            ->leftJoin('stock_opname_dtl as d', 'd.opnameid', '=', 'so.id')
            ->select(
                'so.id',
                'so.tgl_opname',
                'u.nama_unit as unit',
                'so.kode_barang',
                'b.nama_barang',
                'so.stock_sistem',
                'so.stock_fisik',
                'so.keterangan',
                'so.status',
                DB::raw('GROUP_CONCAT(CONCAT(d.qty," (exp: ",d.expired_date,")") SEPARATOR ", ") as detail_expired')
            )
            ->whereRaw("DATE_FORMAT(so.tgl_opname, '%Y-%m') = ?", [$bulan]);

        if ($unit != 'all') {
            $query->where('so.id_unit', $unit);
        }

        $data = $query
            ->groupBy(
                'so.id',
                'so.tgl_opname',
                'u.nama_unit',
                'so.kode_barang',
                'b.nama_barang',
                'so.stock_sistem',
                'so.stock_fisik',
                'so.keterangan',
                'so.status'
            )
            ->orderBy('so.tgl_opname','asc')
            ->get();

        return response()->json(['data' => $data]);
    }

    // Controller
    public function mutasiStok(Request $request)
    {
        $start_date = $request->input('start_date', date('Y-m-d'));
        $end_date = $request->input('end_date', date('Y-m-d'));
        $unit_id = $request->input('unit', 'all');
        $status = $request->input('status', 'all');

        // Ambil unit user yang login
        $userUnitId = auth()->user()->unit->id;
        $userUnitName = auth()->user()->unit->nama_unit;
        
        // Buat array untuk dropdown
        $units = [$userUnitId => $userUnitName];

        return view('laporan.mutasi_stok', [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'unit_id' => $unit_id,
            'status' => $status,
            'units' => $units,
        ]);
    }

    public function mutasiStokData(Request $request)
    {
        $start_date = $request->input('start_date', date('Y-m-d'));
        $end_date = $request->input('end_date', date('Y-m-d'));
        $unit = $request->input('unit', 'all');
        $status = $request->input('status', 'all');

        $mutasi = DB::table('mutasi_stok as m')
            ->join('mutasi_stok_detail as d', 'm.id', '=', 'd.mutasi_id')
            ->join('barang as b', 'd.barang_id', '=', 'b.id')
            ->join('unit as u_from', 'm.dari_unit', '=', 'u_from.id')
            ->join('unit as u_to', 'm.ke_unit', '=', 'u_to.id')
            ->select(
                'm.id as idmutasi',
                'm.nomor_invoice',
                'm.tanggal',
                'm.status',
                'm.note',
                'u_from.nama_unit as dari_unit',
                'u_to.nama_unit as ke_unit',
                'b.kode_barang',
            DB::raw("
            CASE 
                WHEN d.canceled = 1 
                THEN CONCAT(
                    b.nama_barang, ' ', '<span class=\"badge bg-danger me-1\">|batal|</span>'
                )
                ELSE b.nama_barang
            END as nama_barang
        "),
        'd.qty'
    )
    ->whereDate('m.tanggal', '>=', $start_date)
    ->whereDate('m.tanggal', '<=', $end_date)
    ->whereNull('m.deleted_at')
    ->whereNull('d.deleted_at');


        if ($unit !== 'all') {
            $mutasi->where(function($query) use ($unit) {
                $query->where('m.dari_unit', $unit)
                    ->orWhere('m.ke_unit', $unit);
            });
        }

        if ($status !== 'all') {
            $mutasi->where('m.status', $status);
        }

        $mutasi = $mutasi->orderBy('m.tanggal')
                        ->orderBy('m.nomor_invoice')
                        ->orderBy('m.id')
                        ->get();

        // Grouping untuk rowspan
        $grouped = [];
        foreach ($mutasi as $row) {
            if (!isset($grouped[$row->idmutasi])) {
                $grouped[$row->idmutasi] = [
                    'header' => [
                        'nomor_invoice' => $row->nomor_invoice,
                        'tanggal'       => $row->tanggal,
                        'dari_unit'     => $row->dari_unit,
                        'ke_unit'       => $row->ke_unit,
                        'status'        => $row->status,
                        'note'          => $row->note,
                    ],
                    'details' => [],
                    'total_qty' => 0
                ];
            }
            
            $grouped[$row->idmutasi]['details'][] = [
                'kode_barang' => $row->kode_barang,
                'nama_barang' => $row->nama_barang,
                'qty'         => $row->qty,
            ];
            $grouped[$row->idmutasi]['total_qty'] += $row->qty;
        }

        return response()->json(['data' => array_values($grouped)]);
    }

    public function penjualanDetail(Request $request)
    {
        // default filter
        $start_date   = $request->get('start_date', date('Y-m-d'));
        $end_date     = $request->get('end_date', date('Y-m-d'));
        $unit_id      = $request->get('unit', 'all');
        $metode_bayar = $request->get('metode', 'all');

        // list unit untuk filter dropdown
        $units = DB::table('unit')->whereNull('deleted_at')->pluck('nama_unit', 'id');

        return view('laporan.penjualan_detail', compact(
            'start_date', 'end_date', 'unit_id', 'metode_bayar', 'units'
        ));
    }

    public function penjualanDetailData(Request $request)
    {
        $start_date   = $request->input('start_date', date('Y-m-01'));
        $end_date     = $request->input('end_date', date('Y-m-d'));
        $unit_id      = $request->input('unit', 'all');
        $metode_bayar = $request->input('metode', 'all');

        $query = DB::table('penjualan as p')
            ->join('penjualan_detail as d', 'p.id', '=', 'd.penjualan_id')
            ->join('barang as b', 'd.barang_id', '=', 'b.id')
            ->join('unit as u', 'p.unit_id', '=', 'u.id')
            ->select(
                'p.tanggal',
                'p.nomor_invoice',
                'p.customer',
                'p.metode_bayar',
                'u.nama_unit',
                'b.kode_barang',
                'b.nama_barang',
                'd.qty',
                'd.harga',
                DB::raw('(d.qty * d.harga) as total')
            )
            ->whereBetween(DB::raw('DATE(p.tanggal)'), [$start_date, $end_date])
            ->whereNull('p.deleted_at')
            ->whereNull('d.deleted_at');

        // filter unit
        if ($unit_id != 'all') {
            $query->where('p.unit_id', $unit_id);
        }

        // filter metode bayar
        if ($metode_bayar != 'all') {
            $query->where('p.metode_bayar', $metode_bayar);
        }

        // kondisi offline vs mobile
        $query->where(function ($q) {
            $q->where('p.type_order', 'offline')
            ->orWhere(function ($q2) {
                $q2->where('p.type_order', 'mobile')
                    ->where('p.status_ambil', 'finish');
            });
        });

        $data = $query->orderBy('p.tanggal')->get();

        return DataTables::of($data)->make(true);
    }

    public function penjualanTagihan(Request $request)
    {
        // default filter bulan berjalan (format YYYYMM)
        $periode = $request->get('periode', date('Ym'));
        
        // konversi untuk tampilan di input month (YYYY-MM)
        $bulan_tampil = strlen($periode) == 6 
            ? substr($periode, 0, 4) . '-' . substr($periode, 4, 2)
            : date('Y-m');
        
        // list unit untuk filter dropdown - hanya unit yang memiliki penjualan cicilan
        $units = DB::table('unit')
            ->join('penjualan', 'unit.id', '=', 'penjualan.unit_id')
            ->join('penjualan_cicilan', 'penjualan.id', '=', 'penjualan_cicilan.penjualan_id')
            ->whereNull('unit.deleted_at')
            ->whereNull('penjualan.deleted_at')
            ->whereNull('penjualan_cicilan.deleted_at')
            ->where('penjualan.metode_bayar', 'cicilan')
            ->whereIn('unit.jenis', ['toko', 'bengkel'])
            ->select('unit.id', 'unit.nama_unit')
            ->distinct()
            ->pluck('unit.nama_unit', 'unit.id');

        return view('laporan.tagihan', compact('periode', 'bulan_tampil', 'units'));
    }

    public function penjualanTagihanData(Request $request)
    {
        $periode = $request->input('periode', date('Ym'));
        $unit_id = $request->input('unit', 'all');

        // Validasi format periode
        if (!preg_match('/^\d{6}$/', $periode)) {
            $periode = date('Ym');
        }

        $query = PenjualanCicil::select(
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
                'users.name as nama_anggota',
                'users.nik',
                'penjualan.nomor_invoice',
                'penjualan.tanggal as tanggal_penjualan',
                'penjualan.metode_bayar',
                'penjualan.total as total_penjualan', // dari struktur: total decimal
                'penjualan.dibayar as dp_awal', // menggunakan dibayar sebagai DP awal
                'penjualan.tenor',
                'penjualan.grandtotal',
                'penjualan.subtotal',
                'penjualan.bunga_barang',
                'unit.nama_unit',
                'unit.jenis as jenis_unit',
                DB::raw("CONCAT(SUBSTR(penjualan_cicilan.periode_tagihan, 1, 4), '-', SUBSTR(penjualan_cicilan.periode_tagihan, 5, 2)) as periode_format"),
                // Hitung sisa bayar
                DB::raw("(penjualan.total - penjualan.dibayar) as sisa_bayar")
            )
            ->join('users', 'penjualan_cicilan.anggota_id', '=', 'users.id')
            ->join('penjualan', 'penjualan_cicilan.penjualan_id', '=', 'penjualan.id')
            ->leftJoin('unit', 'penjualan.unit_id', '=', 'unit.id')
            ->whereNull('penjualan_cicilan.deleted_at')
            ->whereNull('penjualan.deleted_at')
            ->where('penjualan.metode_bayar', 'cicilan')
            ->where('penjualan_cicilan.periode_tagihan', $periode);

        // Filter unit jika dipilih
        if ($unit_id != 'all') {
            $query->where('penjualan.unit_id', $unit_id);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function($row) {
                if ($row->status != 'lunas') {
                    return '<button class="btn btn-sm btn-success btn-pelunasan" 
                            data-id="'.$row->id.'" 
                            data-invoice="'.$row->nomor_invoice.'"
                            data-nama="'.$row->nama_anggota.'"
                            data-total="Rp ' . number_format($row->total_cicilan, 0, ',', '.') . '">
                            <i class="bi bi-check-circle"></i> Lunas
                        </button>';
                }
                return '<span class="badge bg-success">Lunas</span>';
            })
            ->addColumn('sisa_tenor', function($row) {
                // Hitung sisa tenor berdasarkan data penjualan
                $tenor = $row->tenor ?? 0;
                $cicilan_ke = $row->cicilan ?? 0;
                $sisa = max(0, $tenor - $cicilan_ke);
                
                return '<span class="badge bg-info">' . $sisa . ' kali lagi</span>';
            })
            ->addColumn('detail_penjualan', function($row) {
                $totalPenjualan = isset($row->total_penjualan) 
                    ? number_format($row->total_penjualan, 0, ',', '.') 
                    : '0';
                
                $dpAwal = isset($row->dp_awal) 
                    ? number_format($row->dp_awal, 0, ',', '.') 
                    : '0';
                
                $sisaBayar = isset($row->sisa_bayar) 
                    ? number_format($row->sisa_bayar, 0, ',', '.') 
                    : '0';
                
                return '<small class="text-muted">
                        Total: <strong>Rp ' . $totalPenjualan . '</strong><br>
                        DP: <strong>Rp ' . $dpAwal . '</strong><br>
                        Sisa: <strong>Rp ' . $sisaBayar . '</strong>
                    </small>';
            })
            ->addColumn('progress_cicilan', function($row) {
                $tenor = $row->tenor ?? 1;
                $cicilan_ke = $row->cicilan ?? 0;
                $percentage = $tenor > 0 ? min(100, ($cicilan_ke / $tenor) * 100) : 0;
                
                return '<div class="progress" style="height: 20px;">
                    <div class="progress-bar bg-success" role="progressbar" 
                         style="width: ' . $percentage . '%" 
                         aria-valuenow="' . $percentage . '" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                        ' . round($percentage) . '%
                    </div>
                </div>';
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
            ->editColumn('total_penjualan', function($row) {
                return 'Rp ' . number_format($row->total_penjualan, 0, ',', '.');
            })
            ->editColumn('dp_awal', function($row) {
                return 'Rp ' . number_format($row->dp_awal, 0, ',', '.');
            })
            ->editColumn('sisa_bayar', function($row) {
                return 'Rp ' . number_format($row->sisa_bayar, 0, ',', '.');
            })
            ->editColumn('status', function($row) {
                $badge = $row->status == 'lunas' ? 'success' : 'warning';
                $text = $row->status == 'lunas' ? 'Lunas' : 'Belum Lunas';
                return '<span class="badge bg-'.$badge.'">'.$text.'</span>';
            })
            ->editColumn('status_bayar', function($row) {
                $badge = $row->status_bayar == '1' ? 'success' : 'secondary';
                $text = $row->status_bayar == '1' ? 'Sudah Bayar' : 'Belum Bayar';
                return '<span class="badge bg-'.$badge.'">'.$text.'</span>';
            })
            ->editColumn('created_at', function($row) {
                return date('d/m/Y H:i', strtotime($row->created_at));
            })
            ->editColumn('tanggal_penjualan', function($row) {
                return date('d/m/Y', strtotime($row->tanggal_penjualan));
            })
            ->editColumn('periode_tagihan', function($row) {
                if (strlen($row->periode_tagihan) == 6) {
                    $tahun = substr($row->periode_tagihan, 0, 4);
                    $bulan = substr($row->periode_tagihan, 4, 2);
                    $nama_bulan = [
                        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                        '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                        '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
                        '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                    ];
                    return $nama_bulan[$bulan] . ' ' . $tahun;
                }
                return $row->periode_tagihan;
            })
            ->editColumn('metode_bayar', function($row) {
                $badge = $row->metode_bayar == 'cicilan' ? 'primary' : 'success';
                return '<span class="badge bg-'.$badge.'">'.ucfirst($row->metode_bayar).'</span>';
            })
            ->editColumn('cicilan', function($row) {
                $tenor = $row->tenor ?? 0;
                return 'Cicilan ke-' . $row->cicilan . ($tenor > 0 ? ' dari ' . $tenor : '');
            })
            ->editColumn('jenis_unit', function($row) {
                if (!$row->jenis_unit) return '-';
                $badge = $row->jenis_unit == 'toko' ? 'primary' : 'success';
                return '<span class="badge bg-'.$badge.'">'.ucfirst($row->jenis_unit).'</span>';
            })
            ->rawColumns([
                'action', 'status', 'status_bayar', 'jenis_unit', 
                'periode_tagihan', 'metode_bayar', 'sisa_tenor', 
                'detail_penjualan', 'progress_cicilan'
            ])
            ->make(true);
    }

    public function getStatistics(Request $request)
    {
        $periode = $request->get('periode', date('Ym'));
        $unit_id = $request->get('unit', 'all');

        // Validasi format periode
        if (!preg_match('/^\d{6}$/', $periode)) {
            $periode = date('Ym');
        }

        // Query untuk statistik
        $query = PenjualanCicil::select(
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN penjualan_cicilan.status = "lunas" THEN 1 ELSE 0 END) as lunas_count'),
                DB::raw('SUM(CASE WHEN penjualan_cicilan.status != "lunas" THEN 1 ELSE 0 END) as belum_lunas_count'),
                DB::raw('SUM(penjualan_cicilan.total_cicilan) as total_nominal'),
                DB::raw('SUM(CASE WHEN penjualan_cicilan.status = "lunas" THEN penjualan_cicilan.total_cicilan ELSE 0 END) as nominal_lunas'),
                DB::raw('SUM(CASE WHEN penjualan_cicilan.status != "lunas" THEN penjualan_cicilan.total_cicilan ELSE 0 END) as nominal_belum_lunas'),
                DB::raw('SUM(penjualan_cicilan.pokok) as total_pokok'),
                DB::raw('SUM(penjualan_cicilan.bunga) as total_bunga')
            )
            ->join('penjualan', 'penjualan_cicilan.penjualan_id', '=', 'penjualan.id')
            ->whereNull('penjualan_cicilan.deleted_at')
            ->whereNull('penjualan.deleted_at')
            ->where('penjualan.metode_bayar', 'cicilan')
            ->where('penjualan_cicilan.periode_tagihan', $periode);

        if ($unit_id != 'all') {
            $query->where('penjualan.unit_id', $unit_id);
        }

        $statistics = $query->first();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $statistics->total ?? 0,
                'lunas_count' => $statistics->lunas_count ?? 0,
                'belum_lunas_count' => $statistics->belum_lunas_count ?? 0,
                'total_nominal' => $statistics->total_nominal ?? 0,
                'nominal_lunas' => $statistics->nominal_lunas ?? 0,
                'nominal_belum_lunas' => $statistics->nominal_belum_lunas ?? 0,
                'total_pokok' => $statistics->total_pokok ?? 0,
                'total_bunga' => $statistics->total_bunga ?? 0,
                'periode' => $periode
            ]
        ]);
    }

    public function pelunasanTagihan(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:penjualan_cicilan,id'
        ]);

        try {
            DB::beginTransaction();

            $cicilan = PenjualanCicil::findOrFail($request->id);
            
            // Ambil data penjualan untuk validasi
            $penjualan = $cicilan->penjualan;

            // Cek apakah sudah lunas
            if ($cicilan->status == 'lunas') {
                return response()->json([
                    'success' => false,
                    'message' => 'Tagihan sudah lunas'
                ], 400);
            }

            $cicilan->update([
                'status' => 'lunas',
                'status_bayar' => '1',
                'updated_at' => now()
            ]);

            DB::commit(); 

            return response()->json([
                'success' => true,
                'message' => 'Tagihan berhasil dilunaskan'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Error pelunasan tagihan: ' . $e->getMessage(), [
                'id' => $request->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function pelunasanSemuaTagihan(Request $request)
    {
        $request->validate([
            'periode' => 'required|regex:/^\d{6}$/',
            'unit' => 'nullable'
        ]);

        try {
            DB::beginTransaction();

            $periode = $request->periode;
            $unit_id = $request->unit;

            // Query untuk mendapatkan semua cicilan yang belum lunas berdasarkan periode_tagihan
            // Hanya ambil yang metode_bayar = 'cicilan'
            $query = PenjualanCicil::select('penjualan_cicilan.id')
                ->join('penjualan', 'penjualan_cicilan.penjualan_id', '=', 'penjualan.id')
                ->whereNull('penjualan_cicilan.deleted_at')
                ->whereNull('penjualan.deleted_at')
                ->where('penjualan.metode_bayar', 'cicilan') // Hanya yang cicilan
                ->where('penjualan_cicilan.status', '!=', 'lunas')
                ->where('penjualan_cicilan.periode_tagihan', $periode);

            if ($unit_id && $unit_id != 'all') {
                $query->where('penjualan.unit_id', $unit_id);
            }

            $vouchers = $query->get();

            if ($vouchers->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada tagihan cicilan yang belum lunas untuk periode ini'
                ]);
            }

            $ids = $vouchers->pluck('id')->toArray();

            // Update status semua voucher
            $updated = PenjualanCicil::whereIn('id', $ids)
                ->update([
                    'status' => 'lunas',
                    'status_bayar' => '1',
                    'updated_at' => now()
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil melunasi ' . $updated . ' tagihan cicilan',
                'count' => $updated
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error pelunasan semua tagihan: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportTagihan(Request $request)
    {
        $periode = $request->get('periode', date('Ym'));
        $unit_id = $request->get('unit', 'all');

        // Query untuk data export
        $query = PenjualanCicil::select(
                'penjualan_cicilan.periode_tagihan',
                'users.nik',
                'users.name as nama_anggota',
                'penjualan.nomor_invoice',
                'penjualan_cicilan.cicilan',
                'penjualan.tenor',
                'penjualan_cicilan.pokok',
                'penjualan_cicilan.bunga',
                'penjualan_cicilan.total_cicilan',
                'penjualan_cicilan.status',
                'penjualan_cicilan.status_bayar',
                'penjualan_cicilan.created_at',
                'penjualan.tanggal as tanggal_penjualan',
                'penjualan.total as total_penjualan',
                'penjualan.dibayar as dp_awal',
                DB::raw('(penjualan.total - penjualan.dibayar) as sisa_bayar'),
                'unit.nama_unit'
            )
            ->join('users', 'penjualan_cicilan.anggota_id', '=', 'users.id')
            ->join('penjualan', 'penjualan_cicilan.penjualan_id', '=', 'penjualan.id')
            ->leftJoin('unit', 'penjualan.unit_id', '=', 'unit.id')
            ->whereNull('penjualan_cicilan.deleted_at')
            ->whereNull('penjualan.deleted_at')
            ->where('penjualan.metode_bayar', 'cicilan')
            ->where('penjualan_cicilan.periode_tagihan', $periode);

        if ($unit_id != 'all') {
            $query->where('penjualan.unit_id', $unit_id);
        }

        $data = $query->orderBy('penjualan_cicilan.created_at', 'asc')->get();

        // Format nama file
        $tahun = substr($periode, 0, 4);
        $bulan = substr($periode, 4, 2);
        $filename = 'tagihan_cicilan_' . $periode . '_' . date('YmdHis') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Tambahkan BOM untuk Excel UTF-8
        fwrite($output, "\xEF\xBB\xBF");
        
        // Header CSV
        fputcsv($output, [
            'Periode', 'NIK', 'Nama Anggota', 'Nomor Invoice', 'Cicilan ke-', 'Tenor',
            'Pokok', 'Bunga', 'Total Cicilan', 'Status', 'Status Bayar',
            'Tanggal Transaksi', 'Tanggal Penjualan', 'Total Penjualan', 'DP Awal', 'Sisa Bayar', 'Unit'
        ]);
        
        foreach ($data as $row) {
            fputcsv($output, [
                $row->periode_tagihan,
                $row->nik,
                $row->nama_anggota,
                $row->nomor_invoice,
                'Cicilan ke-' . $row->cicilan,
                $row->tenor,
                number_format($row->pokok, 0, ',', '.'),
                number_format($row->bunga, 0, ',', '.'),
                number_format($row->total_cicilan, 0, ',', '.'),
                $row->status == 'lunas' ? 'Lunas' : 'Belum Lunas',
                $row->status_bayar == '1' ? 'Sudah Bayar' : 'Belum Bayar',
                date('d/m/Y H:i', strtotime($row->created_at)),
                date('d/m/Y', strtotime($row->tanggal_penjualan)),
                number_format($row->total_penjualan, 0, ',', '.'),
                number_format($row->dp_awal, 0, ',', '.'),
                number_format($row->sisa_bayar, 0, ',', '.'),
                $row->nama_unit
            ]);
        }
        
        fclose($output);
        exit;
    }

    // Untuk belanja anggota (sesuaikan juga dengan struktur)
    public function belanjaAnggota(Request $request)
    {
        // default filter bulan berjalan (format YYYYMM)
        $periode = $request->get('periode', date('Ym'));
        
        // Konversi periode ke date range
        $tahun = substr($periode, 0, 4);
        $bulan = substr($periode, 4, 2);
        $start_date = date('Y-m-01', strtotime($tahun . '-' . $bulan . '-01'));
        $end_date = date('Y-m-t', strtotime($start_date));
        
        // Konversi untuk tampilan
        $bulan_tampil = $tahun . '-' . $bulan;
        
        // list unit untuk filter dropdown - hanya unit dengan penjualan cicilan
        $units = DB::table('unit')
            ->join('penjualan', 'unit.id', '=', 'penjualan.unit_id')
            ->join('penjualan_cicilan', 'penjualan.id', '=', 'penjualan_cicilan.penjualan_id')
            ->whereNull('unit.deleted_at')
            ->whereNull('penjualan.deleted_at')
            ->whereNull('penjualan_cicilan.deleted_at')
            ->where('penjualan.metode_bayar', 'cicilan')
            ->whereIn('unit.jenis', ['toko', 'bengkel'])
            ->select('unit.id', 'unit.nama_unit')
            ->distinct()
            ->pluck('unit.nama_unit', 'unit.id');

        return view('laporan.belanja_anggota', compact('periode', 'bulan_tampil', 'start_date', 'end_date', 'units'));
    }

    public function belanjaAnggotaData(Request $request)
    {
        $periode = $request->input('periode', date('Ym'));
        $unit_id = $request->input('unit', 'all');

        // Validasi format periode
        if (!preg_match('/^\d{6}$/', $periode)) {
            $periode = date('Ym');
        }

        // Konversi periode ke date range untuk filter created_at
        $tahun = substr($periode, 0, 4);
        $bulan = substr($periode, 4, 2);
        $start_date = date('Y-m-01', strtotime($tahun . '-' . $bulan . '-01'));
        $end_date = date('Y-m-t', strtotime($start_date));

        $query = PenjualanCicil::select(
                'users.id as anggota_id',
                'users.nik',
                'users.name as nama_anggota',
                'users.jabatan',
                'users.email',
                'users.telepon',
                DB::raw('COUNT(DISTINCT penjualan_cicilan.penjualan_id) as jumlah_transaksi'),
                DB::raw('SUM(penjualan_cicilan.total_cicilan) as total_belanja'),
                DB::raw('SUM(penjualan_cicilan.pokok) as total_pokok'),
                DB::raw('SUM(penjualan_cicilan.bunga) as total_bunga'),
                DB::raw('MAX(penjualan_cicilan.created_at) as terakhir_belanja'),
                DB::raw("CONCAT(users.nik, ' - ', users.name) as nama_lengkap"),
                DB::raw('COUNT(penjualan_cicilan.id) as jumlah_cicilan'),
                DB::raw('GROUP_CONCAT(DISTINCT unit.nama_unit SEPARATOR ", ") as daftar_unit'),
                DB::raw('SUM(penjualan.total) as total_nilai_penjualan'),
                DB::raw('SUM(penjualan.dibayar) as total_dp')
            )
            ->join('users', 'penjualan_cicilan.anggota_id', '=', 'users.id')
            ->join('penjualan', 'penjualan_cicilan.penjualan_id', '=', 'penjualan.id')
            ->leftJoin('unit', 'penjualan.unit_id', '=', 'unit.id')
            ->whereNull('penjualan_cicilan.deleted_at')
            ->whereNull('penjualan.deleted_at')
            ->where('penjualan.metode_bayar', 'cicilan')
            ->where('penjualan_cicilan.periode_tagihan', $periode)
            ->whereBetween(DB::raw('DATE(penjualan_cicilan.created_at)'), [$start_date, $end_date]);

        // Filter unit jika dipilih
        if ($unit_id != 'all') {
            $query->where('penjualan.unit_id', $unit_id);
        }

        $query->groupBy('users.id', 'users.nik', 'users.name', 'users.jabatan', 'users.email', 'users.telepon');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function($row) use ($periode) {
                return '<a href="' . route('laporan.belanja-anggota.detail', ['anggota_id' => $row->anggota_id, 'periode' => $periode]) . '" 
                        class="btn btn-sm btn-info">
                        <i class="bi bi-eye"></i> Detail
                    </a>';
            })
            ->editColumn('total_belanja', function($row) {
                return 'Rp ' . number_format($row->total_belanja, 0, ',', '.');
            })
            ->editColumn('total_pokok', function($row) {
                return 'Rp ' . number_format($row->total_pokok, 0, ',', '.');
            })
            ->editColumn('total_bunga', function($row) {
                return 'Rp ' . number_format($row->total_bunga, 0, ',', '.');
            })
            ->editColumn('total_nilai_penjualan', function($row) {
                return 'Rp ' . number_format($row->total_nilai_penjualan, 0, ',', '.');
            })
            ->editColumn('total_dp', function($row) {
                return 'Rp ' . number_format($row->total_dp, 0, ',', '.');
            })
            ->editColumn('terakhir_belanja', function($row) {
                return $row->terakhir_belanja ? date('d/m/Y H:i', strtotime($row->terakhir_belanja)) : '-';
            })
            ->editColumn('jabatan', function($row) {
                return $row->jabatan ?: '-';
            })
            ->editColumn('daftar_unit', function($row) {
                return $row->daftar_unit ?: '-';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

public function pinbrg(Request $request)
{
    if ($request->ajax()) {
        // Cek apakah ini request untuk check_data atau get_totals
        if ($request->has('check_data')) {
            return $this->getDataPinbrg($request);
        }
        if ($request->has('get_totals')) {
            return $this->getDataPinbrg($request);
        }
        return $this->getDataPinbrg($request);
    }
    
    return view('laporan.pinbrg');
}

/**
 * Get data for pinbrg report
 */
/**
 * Get data for pinbrg report
 */
private function getDataPinbrg(Request $request)
{
    $period = $request->input('period', date('Y-m'));
    $search = trim((string) ($request->input('search.value') ?? $request->input('search') ?? $request->input('search_term') ?? ''));
    
    $query = $this->buildPinbrgQuery($period, $search);
    
    // Untuk cek data
    if ($request->input('check_data') == 'true') {
        return response()->json([
            'total_data' => $query->count()
        ]);
    }
    
    // Untuk get totals
    if ($request->input('get_totals') == 'true') {
        $totals = [
            'total_jum_pin' => $query->sum('JUM_PIN'),
            'total_sisa_pin' => $query->sum('SISA_PIN'),
            'aktif' => (clone $query)->where('STATUS', '1')->count(),
            'non_aktif' => (clone $query)->where('STATUS', '!=', '1')->count()
        ];
        return response()->json(['totals' => $totals]);
    }

    if ($request->input('all_data') == 'true') {
        return response()->json([
            'data' => $query->orderByDesc('TG_PIN')->get()->map(function ($row) {
                return $this->transformPinbrgRow($row);
            })->values(),
        ]);
    }
    
    return DataTables::eloquent($query)
        ->addIndexColumn()
        ->addColumn('TG_PIN_formatted', fn ($row) => $this->transformPinbrgRow($row)['TG_PIN_formatted'])
        ->addColumn('TOTAL_HARGA_formatted', fn ($row) => $this->transformPinbrgRow($row)['TOTAL_HARGA_formatted'])
        ->addColumn('JUM_PIN_formatted', fn ($row) => $this->transformPinbrgRow($row)['JUM_PIN_formatted'])
        ->addColumn('SISA_PIN_formatted', fn ($row) => $this->transformPinbrgRow($row)['SISA_PIN_formatted'])
        ->addColumn('ANGS_X_formatted', fn ($row) => $this->transformPinbrgRow($row)['ANGS_X_formatted'])
        ->addColumn('ANGSUR1_formatted', fn ($row) => $this->transformPinbrgRow($row)['ANGSUR1_formatted'])
        ->addColumn('ANGSUR2_formatted', fn ($row) => $this->transformPinbrgRow($row)['ANGSUR2_formatted'])
        ->addColumn('STATUS_badge', fn ($row) => $this->transformPinbrgRow($row)['STATUS_badge'])
        ->rawColumns(['STATUS_badge'])
        ->make(true);
}

public function generatePinbrg(Request $request)
{
    try {
        DB::beginTransaction();
        
        $request->validate([
            'period' => 'required|date_format:Y-m'
        ]);
        
        $period = $request->input('period');
        $tahun = substr($period, 0, 4);
        $bulan = substr($period, 5, 2);
        
        Pinbrg::where('period', $period)->delete();
        
        $sql = "
            SELECT
                ? AS period,
                u.unit_usaha AS unit_usaha,
                u.id AS lokasi,
                usr.nomor_anggota AS NO_AGT,
                p.nomor_invoice AS NOPIN,
                CONCAT(LEFT(u.nama_unit, 3), RIGHT(p.nomor_invoice, 5)) AS NO_PIN,
                DATE(p.tanggal) AS TG_PIN,
                p.grandtotal AS TOTAL_HARGA,
                SUM(pc.total_cicilan) AS JUM_PIN,
                SUM(CASE WHEN pc.status_bayar = '0' THEN pc.total_cicilan ELSE 0 END) AS SISA_PIN,
                MAX(pc.cicilan) AS ANGS_X,
                SUM(CASE WHEN pc.cicilan = 1 THEN pc.total_cicilan ELSE 0 END) AS ANGSUR1,
                SUM(CASE WHEN pc.cicilan = 2 THEN pc.total_cicilan ELSE 0 END) AS ANGSUR2,
                CASE 
                    WHEN pc.kategori = 0 THEN '1' 
                    WHEN pc.kategori = 1 THEN '2' 
                END AS JENIS,
                MIN(CASE WHEN pc.status_bayar = '0' THEN pc.cicilan END) AS ANGS_KE,
                u.id AS UNIT,
                '1' AS STATUS,
                usr.nik AS NO_BADGE,
                CASE 
                    WHEN pc.kategori = 0 THEN 'NB' 
                    WHEN pc.kategori = 1 THEN 'BP' 
                END AS KEL,
                '2' AS jenis_penjualan
            FROM
                penjualan p
                LEFT JOIN users usr ON usr.id = p.anggota_id
                LEFT JOIN unit u ON u.id = p.unit_id
                LEFT JOIN penjualan_cicilan pc ON pc.penjualan_id = p.id
            WHERE
                p.metode_bayar = 'cicilan'
                AND p.STATUS = 'hutang'
            GROUP BY
                p.id, u.id, u.unit_usaha, u.nama_unit, usr.nomor_anggota, usr.nik, 
                p.nomor_invoice, p.tanggal, p.grandtotal, pc.kategori
        ";
        
        Log::info('SQL Query:', ['sql' => $sql, 'period' => $period]);
        
        $results = DB::select($sql, [$period]);
        
        Log::info('Query Results:', [
            'count' => count($results),
            'first_row' => count($results) > 0 ? (array)$results[0] : null
        ]);
        
        $inserted = 0;
        foreach ($results as $row) {
            $data = [
                'period' => $period,
                'unit_usaha' => $row->unit_usaha,
                'lokasi' => $row->lokasi,
                'NO_AGT' => $row->NO_AGT,
                'NOPIN' => $row->NOPIN,
                'NO_PIN' => $row->NO_PIN,
                'TG_PIN' => $row->TG_PIN,
                'TOTAL_HARGA' => $row->TOTAL_HARGA ?? 0,
                'JUM_PIN' => $row->JUM_PIN ?? 0,
                'SISA_PIN' => $row->SISA_PIN ?? 0,
                'ANGS_X' => $row->ANGS_X ?? 0,
                'ANGSUR1' => $row->ANGSUR1 ?? 0,
                'ANGSUR2' => $row->ANGSUR2 ?? 0,
                'JENIS' => $row->JENIS,
                'ANGS_KE' => $row->ANGS_KE,
                'UNIT' => $row->UNIT,
                'STATUS' => $row->STATUS,
                'NO_BADGE' => $row->NO_BADGE,
                'KEL' => $row->KEL,
                'jenis_penjualan' => $row->jenis_penjualan,
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            Pinbrg::create($data);
            $inserted++;
        }
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => "Data pinbrg periode {$period} berhasil digenerate. Total: {$inserted} data",
            'total' => $inserted,
            'debug' => [
                'query_result_count' => count($results),
                'sample' => count($results) > 0 ? (array)$results[0] : null
            ]
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error generating pinbrg:', [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Gagal generate data: ' . $e->getMessage(),
            'error_detail' => $e->getMessage()
        ], 500);
    }
}

public function exportPinbrgDbf(Request $request)
{
    try {
        $period = $request->input('period', date('Y-m'));
        
        $data = Pinbrg::where('period', $period)->get();
        
        if ($data->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => "Tidak ada data untuk periode {$period}"
            ]);
        }
        
        $filename = "PINBRG_" . str_replace('-', '', $period) . "_" . date('YmdHis') . ".dbf";
        $tempPath = storage_path("app/temp/{$filename}");

        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0777, true);
        }

        $this->createDbfFileNavicat($tempPath, $data);
        
        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ])->deleteFileAfterSend(true);
        
    } catch (\Exception $e) {
        Log::error('Error exporting pinbrg to DBF:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Gagal export ke DBF: ' . $e->getMessage()
        ], 500);
    }
}

private function createDbfFileNavicat($filePath, $data)
{

    if (!function_exists('dbase_create')) {
        throw new \Exception('Extension dbase tidak tersedia. Install dengan: pecl install dbase');
    }
    
    $dbfDefinition = [
        ['UNIT_USAHA', 'C', 20],    
        ['LOKASI', 'C', 10],        
        ['NO_AGT', 'C', 15],        
        ['NOPIN', 'C', 15],         
        ['NO_PIN', 'C', 15],       
        ['TG_PIN', 'D', 8],         
        ['TOTAL_HRG', 'N', 15, 2],  
        ['JUM_PIN', 'N', 15, 2],   
        ['SISA_PIN', 'N', 15, 2],   
        ['ANGS_X', 'N', 15, 2],    
        ['ANGSUR1', 'N', 15, 2],    
        ['ANGSUR2', 'N', 15, 2],    
        ['JENIS', 'C', 5],          
        ['ANGS_KE', 'N', 5, 0],     
        ['UNIT', 'C', 10],           
        ['STATUS', 'C', 5],         
        ['NO_BADGE', 'C', 15],       
        ['KEL', 'C', 5],             
        ['JENIS_PJ', 'C', 10],       
    ];
    
    $dbf = dbase_create($filePath, $dbfDefinition);
    
    if (!$dbf) {
        throw new \Exception('Gagal membuat file DBF');
    }
    
    foreach ($data as $row) {

        $tglPinjam = '';
        if ($row->TG_PIN) {
            $date = new \DateTime($row->TG_PIN);
            $tglPinjam = $date->format('Ymd');
        }
        
        $record = [
            $this->formatDbfField($row->unit_usaha ?? '', 20),
            $this->formatDbfField($row->lokasi ?? '', 10),
            $this->formatDbfField($row->NO_AGT ?? '', 15),
            $this->formatDbfField($row->NOPIN ?? '', 15),
            $this->formatDbfField($row->NO_PIN ?? '', 15),
            $tglPinjam,
            floatval($row->TOTAL_HARGA ?? 0),
            floatval($row->JUM_PIN ?? 0),
            floatval($row->SISA_PIN ?? 0),
            floatval($row->ANGS_X ?? 0),
            floatval($row->ANGSUR1 ?? 0),
            floatval($row->ANGSUR2 ?? 0),
            $this->formatDbfField($row->JENIS ?? '', 5),
            intval($row->ANGS_KE ?? 0),
            $this->formatDbfField($row->UNIT ?? '', 10),
            $this->formatDbfField($row->STATUS ?? '1', 5),
            $this->formatDbfField($row->NO_BADGE ?? '', 15),
            $this->formatDbfField($row->KEL ?? '', 5),
            $this->formatDbfField($row->jenis_penjualan ?? '2', 10),
        ];
        
        if (!dbase_add_record($dbf, $record)) {
            throw new \Exception('Gagal menambah record ke DBF');
        }
    }
    
    dbase_close($dbf);
}

/**
 * Format field untuk DBF (memotong string sesuai panjang maksimal)
 */
private function formatDbfField($value, $maxLength)
{
    if (empty($value)) {
        return '';
    }
    
    // Konversi ke string dan potong jika terlalu panjang
    $stringValue = (string)$value;
    if (strlen($stringValue) > $maxLength) {
        $stringValue = substr($stringValue, 0, $maxLength);
    }
    
    return $stringValue;
}

private function buildPinbrgQuery(string $period, string $search = '')
{
    return Pinbrg::query()
        ->when($period !== '', function ($query) use ($period) {
            $query->where('period', $period);
        })
        ->when($search !== '', function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('NO_AGT', 'like', "%{$search}%")
                    ->orWhere('NOPIN', 'like', "%{$search}%")
                    ->orWhere('NO_PIN', 'like', "%{$search}%")
                    ->orWhere('NO_BADGE', 'like', "%{$search}%")
                    ->orWhere('unit_usaha', 'like', "%{$search}%")
                    ->orWhere('lokasi', 'like', "%{$search}%");
            });
        });
}

private function transformPinbrgRow($row): array
{
    $statusActive = (string) $row->STATUS === '1';

    return [
        'period' => $row->period,
        'unit_usaha' => $row->unit_usaha,
        'lokasi' => $row->lokasi,
        'NO_AGT' => $row->NO_AGT,
        'NOPIN' => $row->NOPIN,
        'NO_PIN' => $row->NO_PIN,
        'TG_PIN_formatted' => $row->TG_PIN ? date('d/m/Y', strtotime($row->TG_PIN)) : '-',
        'TOTAL_HARGA_formatted' => 'Rp ' . number_format($row->TOTAL_HARGA, 0, ',', '.'),
        'JUM_PIN_formatted' => 'Rp ' . number_format($row->JUM_PIN, 0, ',', '.'),
        'SISA_PIN_formatted' => 'Rp ' . number_format($row->SISA_PIN, 0, ',', '.'),
        'ANGS_X_formatted' => 'Rp ' . number_format($row->ANGS_X, 0, ',', '.'),
        'ANGSUR1_formatted' => $row->ANGSUR1 > 0 ? 'Rp ' . number_format($row->ANGSUR1, 0, ',', '.') : '-',
        'ANGSUR2_formatted' => $row->ANGSUR2 > 0 ? 'Rp ' . number_format($row->ANGSUR2, 0, ',', '.') : '-',
        'JENIS' => $row->JENIS,
        'NO_BADGE' => $row->NO_BADGE,
        'KEL' => $row->KEL,
        'STATUS_badge' => '<span class="badge ' . ($statusActive ? 'bg-success' : 'bg-warning') . '">' . ($statusActive ? 'Aktif' : 'Non-Aktif') . '</span>',
        'STATUS_text' => $statusActive ? 'Aktif' : 'Non-Aktif',
    ];
}

private function downloadHtmlTableAsExcel(string $filename, string $title, array $filters, array $headers, array $rows, array $footerRows = [])
{
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"{$filename}\"");
    header("Cache-Control: max-age=0");

    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    echo '<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
    echo '<style>
        body { font-family: Arial, sans-serif; }
        table { border-collapse: collapse; width: 100%; }
        th { background-color: #f2f2f2; border: 1px solid #000; padding: 5px; text-align: center; font-weight: bold; }
        td { border: 1px solid #000; padding: 4px; vertical-align: top; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row { background-color: #d1e7ff; font-weight: bold; }
        .meta td { border: none; padding: 2px 0; }
        .title { font-size: 18px; font-weight: bold; margin-bottom: 12px; }
    </style></head><body>';
    echo '<div class="title">' . e($title) . '</div>';
    echo '<table class="meta">';
    foreach ($filters as $label => $value) {
        echo '<tr><td style="width:120px;"><strong>' . e($label) . '</strong></td><td>: ' . e((string) $value) . '</td></tr>';
    }
    echo '</table><br>';
    echo '<table><thead><tr>';
    foreach ($headers as $header) {
        echo '<th>' . e($header) . '</th>';
    }
    echo '</tr></thead><tbody>';
    foreach ($rows as $row) {
        echo '<tr>';
        foreach ($row as $value) {
            $class = is_numeric($value) ? 'text-right' : '';
            echo '<td class="' . $class . '">' . e((string) $value) . '</td>';
        }
        echo '</tr>';
    }
    foreach ($footerRows as $row) {
        echo '<tr class="total-row">';
        foreach ($row as $value) {
            $class = is_numeric($value) ? 'text-right' : '';
            echo '<td class="' . $class . '">' . e((string) $value) . '</td>';
        }
        echo '</tr>';
    }
    echo '</tbody></table></body></html>';
    exit;
}

    public function penjualanBengkelDetail(Request $request)
    {
        // default filter
        $start_date   = $request->get('start_date', date('Y-m-01'));
        $end_date     = $request->get('end_date', date('Y-m-d'));
        $metode_bayar = $request->get('metode', 'all');
        $jenis_item   = $request->get('jenis_item', 'all');

        return view('laporan.penjualan_bengkel_detail', compact(
            'start_date', 'end_date', 'metode_bayar', 'jenis_item'
        ));
    }

    /**
     * Data untuk Laporan Penjualan Bengkel Detail
     */
    public function penjualanBengkelDetailData(Request $request)
    {
        $start_date   = $request->input('start_date', date('Y-m-01'));
        $end_date     = $request->input('end_date', date('Y-m-d'));
        $metode_bayar = $request->input('metode', 'all');
        $jenis_item   = $request->input('jenis_item', 'all');

        // Query untuk data BARANG - TANPA JOIN KE UNIT
        $barangQuery = DB::table('transaksi_bengkels as tb')
            ->join('transaksi_bengkel_details as tbd', 'tb.id', '=', 'tbd.transaksi_bengkel_id')
            ->leftJoin('barang as b', 'tbd.barang_id', '=', 'b.id')
            ->select(
                'tb.tanggal',
                'tb.nomor_invoice',
                'tb.customer',
                'tb.metode_bayar',
                DB::raw('COALESCE(tb.subtotal, 0) as subtotal_nota'),
                DB::raw('COALESCE(tb.diskon, 0) as diskon_nota'),
                DB::raw('COALESCE(tb.grandtotal, 0) as grandtotal_nota'),
                DB::raw("'Bengkel' as nama_unit"), // Fixed value
                DB::raw("'BARANG' as tipe_item"),
                'b.kode_barang',
                'b.nama_barang',
                'tbd.qty',
                'tbd.harga',
                DB::raw('(tbd.qty * tbd.harga) as total')
            )
            ->whereBetween(DB::raw('DATE(tb.tanggal)'), [$start_date, $end_date])
            ->whereNull('tb.deleted_at')
            ->whereNull('tbd.deleted_at')
            ->where('tbd.jenis', 'barang');

        // Query untuk data JASA - TANPA JOIN KE UNIT
        $jasaQuery = DB::table('transaksi_bengkels as tb')
            ->join('transaksi_bengkel_details as tbd', 'tb.id', '=', 'tbd.transaksi_bengkel_id')
            ->leftJoin('jasa_bengkel as jb', 'tbd.jasa_id', '=', 'jb.id')
            ->select(
                'tb.tanggal',
                'tb.nomor_invoice',
                'tb.customer',
                'tb.metode_bayar',
                DB::raw('COALESCE(tb.subtotal, 0) as subtotal_nota'),
                DB::raw('COALESCE(tb.diskon, 0) as diskon_nota'),
                DB::raw('COALESCE(tb.grandtotal, 0) as grandtotal_nota'),
                DB::raw("'Bengkel' as nama_unit"), // Fixed value
                DB::raw("'JASA' as tipe_item"),
                'jb.kode_jasa as kode_barang',
                'jb.nama_jasa as nama_barang',
                'tbd.qty',
                'tbd.harga',
                DB::raw('(tbd.qty * tbd.harga) as total')
            )
            ->whereBetween(DB::raw('DATE(tb.tanggal)'), [$start_date, $end_date])
            ->whereNull('tb.deleted_at')
            ->whereNull('tbd.deleted_at')
            ->where('tbd.jenis', 'jasa');

        // Filter metode bayar
        if ($metode_bayar != 'all') {
            $barangQuery->where('tb.metode_bayar', $metode_bayar);
            $jasaQuery->where('tb.metode_bayar', $metode_bayar);
        }

        // Filter jenis item
        if ($jenis_item == 'barang') {
            $data = $barangQuery->orderBy('tb.tanggal')->orderBy('tb.nomor_invoice')->get();
        } elseif ($jenis_item == 'jasa') {
            $data = $jasaQuery->orderBy('tb.tanggal')->orderBy('tb.nomor_invoice')->get();
        } else {
            // Ambil kedua data dan gabungkan
            $barangData = $barangQuery->get();
            $jasaData = $jasaQuery->get();
            $data = $barangData->concat($jasaData)->sortBy(function($item) {
                return $item->tanggal . ' ' . $item->nomor_invoice;
            })->values();
        }

        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('tanggal', function($row) {
                return date('d/m/Y', strtotime($row->tanggal));
            })
            ->editColumn('qty', function($row) {
                return number_format($row->qty, 3, ',', '.');
            })
            ->editColumn('harga', function($row) {
                return number_format($row->harga, 0, ',', '.');
            })
            ->editColumn('total', function($row) {
                return number_format($row->total, 0, ',', '.');
            })
            ->editColumn('metode_bayar', function($row) {
                return ucfirst($row->metode_bayar);
            })
            ->editColumn('customer', function($row) {
                return $row->customer ?: 'Umum';
            })
            ->make(true);
    }

    /**
     * Laporan Modal Awal
     */
    public function modalAwal(Request $request)
    {
        $bulan = $request->get('bulan', date('Y-m'));
        
        // Untuk dropdown filter unit
        $units = DB::table('unit')->select('id', 'nama_unit')->orderBy('nama_unit')->get();
        
        return view('laporan.modal_awal', compact('bulan', 'units'));
    }

    /**
     * Data untuk Laporan Modal Awal
     */
    public function modalAwalData(Request $request)
    {
        $bulan = $request->get('bulan', date('Y-m'));
        $unit_id = $request->get('unit', 'all');

        $query = DB::table('modal_awal as ma')
            ->leftJoin('unit as u', 'u.id', '=', 'ma.unit_id')
            ->leftJoin('barang as b', 'b.id', '=', 'ma.barang_id')
            ->leftJoin('stok_unit as su', function ($join) {
                $join->on('su.barang_id', '=', 'ma.barang_id')
                    ->on('su.unit_id', '=', 'ma.unit_id')
                    ->whereNull('su.deleted_at');
            })
            ->leftJoin('satuan as s', 'b.idsatuan', '=', 's.id') // Join ke tabel satuan
            ->select(
                'ma.id',
                'ma.periode',
                'ma.kode_barang',
                'ma.nama_barang',
                'ma.harga_modal',
                'ma.unit_id',
                'u.nama_unit as unit',
                'ma.stok as stok_awal',
                'ma.nilai_total_barang as nilai_modal_awal',
                DB::raw('COALESCE(su.stok, ma.stok) as stok_realtime'),
                DB::raw('(COALESCE(su.stok, ma.stok) * ma.harga_modal) as nilai_realtime'),
                DB::raw('(COALESCE(su.stok, ma.stok) - ma.stok) as selisih_stok'),
                DB::raw('((COALESCE(su.stok, ma.stok) - ma.stok) * ma.harga_modal) as selisih_nominal'),
                'ma.created_at',
                'ma.updated_at',
                's.name as satuan' // Ambil name dari tabel satuan
            )
            ->where('ma.periode', $bulan);

        if ($unit_id != 'all') {
            $query->where('ma.unit_id', $unit_id);
        }

        $data = $query->orderBy('ma.kode_barang', 'asc')->get();

        // Hitung total untuk footer
        $totalModalAwal = $data->sum('nilai_modal_awal');
        $totalStokAwal = $data->sum('stok_awal');
        $totalModalRealtime = $data->sum('nilai_realtime');
        $totalStokRealtime = $data->sum('stok_realtime');
        $totalSelisihNominal = $data->sum('selisih_nominal');
        $totalSelisihStok = $data->sum('selisih_stok');

        return response()->json([
            'data' => $data,
            'totals' => [
                'total_stok_awal' => $totalStokAwal,
                'total_modal_awal' => $totalModalAwal,
                'total_stok_realtime' => $totalStokRealtime,
                'total_modal_realtime' => $totalModalRealtime,
                'total_selisih_stok' => $totalSelisihStok,
                'total_selisih_nominal' => $totalSelisihNominal,
            ]
        ]);
    }

    /**
     * Export Excel Laporan Modal Awal
     */
    public function modalAwalExport(Request $request)
    {
        $bulan = $request->get('bulan', date('Y-m'));
        $unit_id = $request->get('unit', 'all');
        
        $query = DB::table('modal_awal as ma')
            ->leftJoin('unit as u', 'u.id', '=', 'ma.unit_id')
            ->leftJoin('barang as b', 'b.id', '=', 'ma.barang_id')
            ->leftJoin('stok_unit as su', function ($join) {
                $join->on('su.barang_id', '=', 'ma.barang_id')
                    ->on('su.unit_id', '=', 'ma.unit_id')
                    ->whereNull('su.deleted_at');
            })
            ->leftJoin('satuan as s', 'b.idsatuan', '=', 's.id') // Join ke tabel satuan
            ->select(
                'ma.periode',
                'ma.kode_barang',
                'ma.nama_barang',
                'ma.harga_modal',
                'u.nama_unit as unit',
                'ma.stok as stok_awal',
                'ma.nilai_total_barang as nilai_modal_awal',
                DB::raw('COALESCE(su.stok, ma.stok) as stok_realtime'),
                DB::raw('(COALESCE(su.stok, ma.stok) * ma.harga_modal) as nilai_realtime'),
                DB::raw('(COALESCE(su.stok, ma.stok) - ma.stok) as selisih_stok'),
                DB::raw('((COALESCE(su.stok, ma.stok) - ma.stok) * ma.harga_modal) as selisih_nominal'),
                's.name as satuan' // Ambil name dari tabel satuan
            )
            ->where('ma.periode', $bulan);

        if ($unit_id != 'all') {
            $query->where('ma.unit_id', $unit_id);
        }

        $data = $query->orderBy('ma.kode_barang', 'asc')->get();

        // Hitung total
        $totalStokAwal = $data->sum('stok_awal');
        $totalModalAwal = $data->sum('nilai_modal_awal');
        $totalStokRealtime = $data->sum('stok_realtime');
        $totalModalRealtime = $data->sum('nilai_realtime');
        $totalSelisihStok = $data->sum('selisih_stok');
        $totalSelisihNominal = $data->sum('selisih_nominal');

        // Generate Excel menggunakan array
        $filename = "modal_awal_{$bulan}_" . date('YmdHis') . ".xls";
        
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Cache-Control: max-age=0");
        
        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        echo '<head>';
        echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
        echo '<style>';
        echo 'table { border-collapse: collapse; width: 100%; }';
        echo 'th { background-color: #f2f2f2; border: 1px solid #000; padding: 5px; text-align: center; }';
        echo 'td { border: 1px solid #000; padding: 3px; }';
        echo '.text-right { text-align: right; }';
        echo '.text-center { text-align: center; }';
        echo '.total-row { background-color: #d1e7ff; font-weight: bold; }';
        echo '</style>';
        echo '</head>';
        echo '<body>';
        
        echo '<table>';
        
        // Header
        echo '<tr>';
        echo '<th>No</th>';
        echo '<th>Periode</th>';
        echo '<th>Kode Barang</th>';
        echo '<th>Nama Barang</th>';
        echo '<th>Satuan</th>';
        echo '<th>Harga Modal</th>';
        echo '<th>Unit</th>';
        echo '<th>Stok Awal</th>';
        echo '<th>Nilai Awal</th>';
        echo '<th>Stok Realtime</th>';
        echo '<th>Nilai Realtime</th>';
        echo '<th>Selisih Stok</th>';
        echo '<th>Selisih Nominal</th>';
        echo '</tr>';
        
        // Data
        $no = 1;
        foreach ($data as $row) {
            echo '<tr>';
            echo '<td class="text-center">' . $no++ . '</td>';
            echo '<td class="text-center">' . $row->periode . '</td>';
            echo '<td>' . $row->kode_barang . '</td>';
            echo '<td>' . $row->nama_barang . '</td>';
            echo '<td class="text-center">' . ($row->satuan ?? '-') . '</td>';
            echo '<td class="text-right">' . number_format($row->harga_modal, 2, ',', '.') . '</td>';
            echo '<td>' . ($row->unit ?? '-') . '</td>';
            echo '<td class="text-right">' . number_format($row->stok_awal, 3, ',', '.') . '</td>';
            echo '<td class="text-right">' . number_format($row->nilai_modal_awal, 2, ',', '.') . '</td>';
            echo '<td class="text-right">' . number_format($row->stok_realtime, 3, ',', '.') . '</td>';
            echo '<td class="text-right">' . number_format($row->nilai_realtime, 2, ',', '.') . '</td>';
            echo '<td class="text-right">' . number_format($row->selisih_stok, 3, ',', '.') . '</td>';
            echo '<td class="text-right">' . number_format($row->selisih_nominal, 2, ',', '.') . '</td>';
            echo '</tr>';
        }
        
        // Total
        echo '<tr class="total-row">';
        echo '<td colspan="7" class="text-right">TOTAL</td>';
        echo '<td class="text-right">' . number_format($totalStokAwal, 3, ',', '.') . '</td>';
        echo '<td class="text-right">' . number_format($totalModalAwal, 2, ',', '.') . '</td>';
        echo '<td class="text-right">' . number_format($totalStokRealtime, 3, ',', '.') . '</td>';
        echo '<td class="text-right">' . number_format($totalModalRealtime, 2, ',', '.') . '</td>';
        echo '<td class="text-right">' . number_format($totalSelisihStok, 3, ',', '.') . '</td>';
        echo '<td class="text-right">' . number_format($totalSelisihNominal, 2, ',', '.') . '</td>';
        echo '</tr>';
        
        echo '</table>';
        echo '</body>';
        echo '</html>';
        
        exit;
    }

}
