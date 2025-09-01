<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Penjualan;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class AdminDashboardController extends Controller
{
    public function dashboard()
    {
        // Total per bulan (6 bulan terakhir)
        $bulanan = DB::table('penjualan')
            ->selectRaw('DATE_FORMAT(tanggal, "%b") as bulan, SUM(grandtotal) as total')
            ->groupBy('bulan')
            ->orderByRaw('MIN(tanggal)')
            ->get();

        // Metode bayar
        $metode = DB::table('penjualan')
            ->select('metode_bayar', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('metode_bayar')
            ->pluck('jumlah','metode_bayar');

        // Status transaksi
        $status = DB::table('penjualan')
            ->select('status', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('status')
            ->pluck('jumlah','status');

        // Top 10 barang stok terbanyak (khusus unit_id sesuai user)
        $topBarang = DB::table('stok_unit as s')
            ->join('barang as b', 'b.id', '=', 's.barang_id')
            ->where('s.unit_id', Auth::user()->unit_kerja)
            ->select('b.nama_barang', DB::raw('SUM(s.stok) as total_stok'))
            ->groupBy('b.nama_barang')
            ->orderByDesc('total_stok')
            ->limit(10)
            ->get();

        $pesananTerbaru = DB::table('penjualan')
            ->whereDate('tanggal', Carbon::today())
            ->where('unit_id', Auth::user()->unit_kerja)
            ->where('type_order', 'mobile')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('dashboard', compact(
            'bulanan',
            'metode',
            'status',
            'topBarang',
            'pesananTerbaru' // <-- kirim ke view
        ));
    }

    public function pesananHariIni()
    {
        $pesananTerbaru = DB::table('penjualan')
            ->whereDate('tanggal', Carbon::today())
            ->where('unit_id', Auth::user()->unit_kerja)
            ->where('type_order', 'mobile')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('partials._pesananHariIni', compact('pesananTerbaru'));
    }

    public function pesananHariIniData(Request $request)
    {
        $pesananTerbaru = DB::table('penjualan')
            ->whereDate('tanggal', Carbon::today())
            ->where('unit_id', Auth::user()->unit_kerja)
            ->where('type_order', 'mobile')
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

}
