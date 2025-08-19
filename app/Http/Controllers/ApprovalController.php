<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ApprovalController extends Controller
{
    public function index(Request $request): View
    {
        return view('transaksi.Approval', []);
    }
    public function getHutang(Request $request)
    {
        $jual = Penjualan::where(['metode_bayar'=>'cicilan','unit_id'=>Auth::user()->unit_kerja])->whereBetween(DB::raw('DATE(tanggal)'), [$request->startdate, $request->enddate]);
        return DataTables::of($jual)->addIndexColumn()->make(true);
        
    }
}
