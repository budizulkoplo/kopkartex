<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class AdminDashboardController extends Controller
{
    public function dashboard()
    {
        $context = $this->dashboardContext();

        $bulanan = $this->baseTransactionQuery($context)
            ->selectRaw('DATE_FORMAT(transaksi.tanggal, "%b") as bulan, SUM(transaksi.grandtotal) as total')
            ->groupBy('bulan')
            ->orderByRaw('MIN(transaksi.tanggal)')
            ->get();

        $metode = $this->baseTransactionQuery($context)
            ->select('transaksi.metode_bayar', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('transaksi.metode_bayar')
            ->pluck('jumlah', 'metode_bayar');

        $status = $this->baseTransactionQuery($context)
            ->select('transaksi.status', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('transaksi.status')
            ->pluck('jumlah', 'status');

        $topBarang = $this->topBarangQuery($context)->get();

        $pesananTerbaru = $this->todayTransactionQuery($context)
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('dashboard', compact(
            'bulanan',
            'metode',
            'status',
            'topBarang',
            'pesananTerbaru',
            'context'
        ));
    }

    public function pesananHariIni()
    {
        $context = $this->dashboardContext();

        $pesananTerbaru = $this->todayTransactionQuery($context)
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('partials._pesananHariIni', compact('pesananTerbaru', 'context'));
    }

    public function pesananHariIniData(Request $request)
    {
        $context = $this->dashboardContext();
        $pesananTerbaru = $this->todayTransactionQuery($context)
            ->orderByDesc('id')
            ->limit(10);

        return DataTables::of($pesananTerbaru)
            ->addIndexColumn()
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value'] != '') {
                    $query->where(function ($q) use ($request) {
                        $q->orWhere('customer', 'like', '%' . $request->search['value'] . '%')
                          ->orWhere('nomor_invoice', 'like', '%' . $request->search['value'] . '%');
                    });
                }
            })
            ->make(true);
    }

    private function dashboardContext(): array
    {
        $user = Auth::user();
        $unit = $user?->unit;
        $unitJenis = $unit->jenis ?? 'toko';
        $isBengkel = $unitJenis === 'bengkel';

        return [
            'unit_id' => $unit->id ?? null,
            'unit_name' => $unit->nama_unit ?? 'Semua Unit',
            'unit_jenis' => $unitJenis,
            'transaction_label' => $isBengkel ? 'Transaksi Bengkel' : 'Penjualan Toko',
            'today_label' => $isBengkel ? 'Daftar Transaksi Bengkel Hari Ini' : 'Daftar Ambil Pesanan Hari Ini',
            'stock_label' => $isBengkel ? 'Top 10 Barang Bengkel Stok Terbanyak' : 'Top 10 Barang Toko Stok Terbanyak',
            'today_route' => $isBengkel ? route('bengkel.riwayat') : url('/ambilbarang'),
            'today_action_label' => $isBengkel ? 'Riwayat Bengkel' : 'Ambil Pesanan',
            'show_mobile_only' => ! $isBengkel,
            'item_group' => $isBengkel ? 'bengkel' : 'toko',
        ];
    }

    private function baseTransactionQuery(array $context)
    {
        if ($context['unit_jenis'] === 'bengkel') {
            return DB::table('transaksi_bengkels as transaksi')
                ->join('users as kasir', 'kasir.id', '=', 'transaksi.created_user')
                ->whereNull('transaksi.deleted_at')
                ->when($context['unit_id'], fn ($query) => $query->where('kasir.unit_kerja', $context['unit_id']));
        }

        return DB::table('penjualan as transaksi')
            ->whereNull('transaksi.deleted_at')
            ->when($context['unit_id'], fn ($query) => $query->where('transaksi.unit_id', $context['unit_id']));
    }

    private function todayTransactionQuery(array $context)
    {
        return $this->baseTransactionQuery($context)
            ->whereDate('transaksi.tanggal', Carbon::today())
            ->when(
                $context['show_mobile_only'],
                fn ($query) => $query->where('transaksi.type_order', 'mobile')
            )
            ->select([
                'transaksi.id',
                'transaksi.nomor_invoice',
                'transaksi.tanggal',
                'transaksi.customer',
                'transaksi.grandtotal',
                'transaksi.status',
                'transaksi.metode_bayar',
            ]);
    }

    private function topBarangQuery(array $context)
    {
        return DB::table('stok_unit as s')
            ->join('barang as b', 'b.id', '=', 's.barang_id')
            ->where('s.unit_id', $context['unit_id'])
            ->where('b.kelompok_unit', $context['item_group'])
            ->select('b.nama_barang', DB::raw('SUM(s.stok) as total_stok'))
            ->groupBy('b.nama_barang')
            ->orderByDesc('total_stok')
            ->limit(10);
    }
}
