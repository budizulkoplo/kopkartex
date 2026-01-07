<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Penerimaan;
use App\Exports\LaporanPenerimaanExport;
use App\Models\Barang;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

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
                $total = DB::table('penjualan')
                    ->whereDate('tanggal', $date->format('Y-m-d'))
                    ->where('unit_id', $unit_id)
                    ->whereNull('deleted_at')
                    ->sum('grandtotal');

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
                'b.nama_barang',
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
        $start_date   = $request->get('start_date', date('Y-m-01'));
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

}
