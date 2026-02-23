<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Penerimaan;
use App\Models\PenjualanCicil;
use App\Exports\LaporanPenerimaanExport;
use App\Models\Barang;
use App\Models\Pinbrg;
use App\Models\Unit;
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
        return $this->getDataPinbrg($request);
    }
    
    return view('laporan.pinbrg');
}

/**
 * Get data for pinbrg report
 */
private function getDataPinbrg(Request $request)
{
    $period = $request->input('period', date('Y-m'));
    
    $query = Pinbrg::query();
    
    // Filter by period
    if ($request->filled('period')) {
        $query->where('period', $period);
    }
    
    return DataTables::eloquent($query)
        ->addIndexColumn()
        ->addColumn('TG_PIN_formatted', function($row) {
            return $row->TG_PIN ? date('d/m/Y', strtotime($row->TG_PIN)) : '-';
        })
        ->addColumn('TOTAL_HARGA_formatted', function($row) {
            return 'Rp ' . number_format($row->TOTAL_HARGA, 0, ',', '.');
        })
        ->addColumn('JUM_PIN_formatted', function($row) {
            return 'Rp ' . number_format($row->JUM_PIN, 0, ',', '.');
        })
        ->addColumn('SISA_PIN_formatted', function($row) {
            return 'Rp ' . number_format($row->SISA_PIN, 0, ',', '.');
        })
        ->addColumn('ANGS_X_formatted', function($row) {
            return 'Rp ' . number_format($row->ANGS_X, 0, ',', '.');
        })
        ->addColumn('ANGSUR1_formatted', function($row) {
            return $row->ANGSUR1 > 0 ? 'Rp ' . number_format($row->ANGSUR1, 0, ',', '.') : '-';
        })
        ->addColumn('ANGSUR2_formatted', function($row) {
            return $row->ANGSUR2 > 0 ? 'Rp ' . number_format($row->ANGSUR2, 0, ',', '.') : '-';
        })
        ->addColumn('STATUS_badge', function($row) {
            $badgeClass = $row->STATUS == '1' ? 'bg-success' : 'bg-warning';
            $statusText = $row->STATUS == '1' ? 'Aktif' : 'Non-Aktif';
            return '<span class="badge ' . $badgeClass . '">' . $statusText . '</span>';
        })
        ->rawColumns(['STATUS_badge'])
        ->make(true);
}

/**
 * Generate data pinbrg from penjualan
 */
/**
 * Generate data pinbrg from penjualan
 */
public function generatePinbrg(Request $request)
{
    try {
        DB::beginTransaction();
        
        // Validasi input
        $request->validate([
            'period' => 'required|date_format:Y-m'
        ]);
        
        // Ambil period dari request
        $period = $request->input('period');
        $tahun = substr($period, 0, 4);
        $bulan = substr($period, 5, 2);
        
        // Hapus data lama untuk periode yang sama
        Pinbrg::where('period', $period)->delete();
        
        // QUERY PERSIS SEPERTI ASLI ANDA - TANPA FILTER BULAN/TAHUN
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
        
        // Log query untuk debugging
        Log::info('SQL Query:', ['sql' => $sql, 'period' => $period]);
        
        $results = DB::select($sql, [$period]);
        
        // Log hasil query
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

/**
 * Export pinbrg to DBF
 */
/**
 * Export pinbrg to DBF - format persis seperti export Navicat
 */
public function exportPinbrgDbf(Request $request)
{
    try {
        $period = $request->input('period', date('Y-m'));
        
        // Ambil data pinbrg
        $data = Pinbrg::where('period', $period)->get();
        
        if ($data->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => "Tidak ada data untuk periode {$period}"
            ]);
        }
        
        // Buat temporary file
        $filename = "PINBRG_" . str_replace('-', '', $period) . "_" . date('YmdHis') . ".dbf";
        $tempPath = storage_path("app/temp/{$filename}");
        
        // Pastikan direktori temp ada
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0777, true);
        }
        
        // Definisikan struktur DBF persis seperti tabel di database (tanpa period)
        $dbfStruct = [
            ['field' => 'UNIT_USAHA', 'type' => 'C', 'length' => 100],
            ['field' => 'LOKASI', 'type' => 'C', 'length' => 50],
            ['field' => 'NO_AGT', 'type' => 'C', 'length' => 50],
            ['field' => 'NOPIN', 'type' => 'C', 'length' => 50],
            ['field' => 'NO_PIN', 'type' => 'C', 'length' => 50],
            ['field' => 'TG_PIN', 'type' => 'D', 'length' => 8],
            ['field' => 'TOTAL_HARGA', 'type' => 'N', 'length' => 15, 'decimal' => 2],
            ['field' => 'JUM_PIN', 'type' => 'N', 'length' => 15, 'decimal' => 2],
            ['field' => 'SISA_PIN', 'type' => 'N', 'length' => 15, 'decimal' => 2],
            ['field' => 'ANGS_X', 'type' => 'N', 'length' => 15, 'decimal' => 2],
            ['field' => 'ANGSUR1', 'type' => 'N', 'length' => 15, 'decimal' => 2],
            ['field' => 'ANGSUR2', 'type' => 'N', 'length' => 15, 'decimal' => 2],
            ['field' => 'JENIS', 'type' => 'C', 'length' => 10],
            ['field' => 'ANGS_KE', 'type' => 'N', 'length' => 5],
            ['field' => 'UNIT', 'type' => 'C', 'length' => 50],
            ['field' => 'STATUS', 'type' => 'C', 'length' => 10],
            ['field' => 'NO_BADGE', 'type' => 'C', 'length' => 50],
            ['field' => 'KEL', 'type' => 'C', 'length' => 10],
            ['field' => 'JENIS_PENJUALAN', 'type' => 'C', 'length' => 10],
        ];
        
        // Buat file DBF
        $this->createDbfFileNavicat($tempPath, $dbfStruct, $data);
        
        // Download file
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

/**
 * Create DBF file dengan format persis Navicat
 */
private function createDbfFileNavicat($filePath, $struct, $data)
{
    // Cek apakah fungsi dbase tersedia
    if (!function_exists('dbase_create')) {
        throw new \Exception('Extension dbase tidak tersedia. Install dengan: pecl install dbase');
    }
    
    // Buat file DBF
    $dbf = dbase_create($filePath, $struct);
    
    if (!$dbf) {
        throw new \Exception('Gagal membuat file DBF');
    }
    
    // Set karakter encoding (CP850 untuk kompatibilitas dengan Windows/Excel)
    // dbase extension tidak mendukung encoding secara langsung, jadi kita biarkan default
    
    foreach ($data as $row) {
        // Format tanggal untuk TG_PIN (DBF menggunakan format Ymd tanpa separator)
        $tglPinjam = '';
        if ($row->TG_PIN) {
            $date = new \DateTime($row->TG_PIN);
            $tglPinjam = $date->format('Ymd'); // Format: YYYYMMDD
        }
        
        // Siapkan record dengan urutan yang sama persis dengan struct
        $record = [
            $this->formatDbfField($row->unit_usaha ?? '', 100),      // UNIT_USAHA
            $this->formatDbfField($row->lokasi ?? '', 50),            // LOKASI
            $this->formatDbfField($row->NO_AGT ?? '', 50),            // NO_AGT
            $this->formatDbfField($row->NOPIN ?? '', 50),             // NOPIN
            $this->formatDbfField($row->NO_PIN ?? '', 50),            // NO_PIN
            $tglPinjam,                                                // TG_PIN (sudah format Ymd)
            floatval($row->TOTAL_HARGA ?? 0),                          // TOTAL_HARGA
            floatval($row->JUM_PIN ?? 0),                              // JUM_PIN
            floatval($row->SISA_PIN ?? 0),                             // SISA_PIN
            floatval($row->ANGS_X ?? 0),                               // ANGS_X
            floatval($row->ANGSUR1 ?? 0),                              // ANGSUR1
            floatval($row->ANGSUR2 ?? 0),                              // ANGSUR2
            $this->formatDbfField($row->JENIS ?? '', 10),              // JENIS
            intval($row->ANGS_KE ?? 0),                                // ANGS_KE
            $this->formatDbfField($row->UNIT ?? '', 50),               // UNIT
            $this->formatDbfField($row->STATUS ?? '1', 10),            // STATUS
            $this->formatDbfField($row->NO_BADGE ?? '', 50),           // NO_BADGE
            $this->formatDbfField($row->KEL ?? '', 10),                // KEL
            $this->formatDbfField($row->jenis_penjualan ?? '2', 10),   // JENIS_PENJUALAN
        ];
        
        // Tambahkan record ke DBF
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
}
