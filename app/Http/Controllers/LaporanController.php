<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Penerimaan;
use App\Exports\LaporanPenerimaanExport;

class LaporanController extends Controller
{
    /**
     * Halaman laporan penerimaan barang
     */
    public function penerimaan(Request $request)
    {
        $query = Penerimaan::with(['details.barang']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tgl_penerimaan', [$request->start_date, $request->end_date]);
        }

        $data = $query->orderBy('tgl_penerimaan', 'desc')->get();

        return view('laporan.penerimaan.index', compact('data'));
    }

    /**
     * Export Excel penerimaan barang
     */
    public function exportPenerimaanExcel(Request $request)
    {
        return Excel::download(
            new LaporanPenerimaanExport($request->start_date, $request->end_date),
            'laporan_penerimaan.xlsx'
        );
    }

    /**
     * Export PDF penerimaan barang
     */
    public function exportPenerimaanPdf(Request $request)
    {
        $query = Penerimaan::with(['details.barang']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tgl_penerimaan', [$request->start_date, $request->end_date]);
        }

        $data = $query->orderBy('tgl_penerimaan', 'desc')->get();

        $pdf = Pdf::loadView('laporan.penerimaan.pdf', compact('data'))
                  ->setPaper('a4', 'portrait');

        return $pdf->download('laporan_penerimaan.pdf');
    }
}
