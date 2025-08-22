<?php

namespace App\Http\Controllers;

use App\Models\StockOpnameDTL;
use App\Models\StockOpnameHDR;
use App\Models\StokUnit;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockOpnameController extends Controller
{
    public function index(Request $request): View
    {
        $unitId = Auth::user()->unit_kerja;

        // Filter bulan (default bulan ini)
        $bulan = $request->bulan ?? Carbon::now()->format('Y-m');
        $startDate = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth()->format('Y-m-d');
        $endDate   = Carbon::createFromFormat('Y-m', $bulan)->endOfMonth()->format('Y-m-d');

        $barang = DB::table('stock_opname')
            ->join('barang', 'barang.id', '=', 'stock_opname.id_barang')
            ->where('stock_opname.id_unit', $unitId)
            ->whereBetween('stock_opname.tgl_opname', [$startDate, $endDate])
            ->whereNull('stock_opname.deleted_at')
            ->select(
                'stock_opname.id as opname_id',
                'barang.id',
                'barang.kode_barang',
                'barang.nama_barang',
                'stock_opname.stock_sistem',
                'stock_opname.stock_fisik',
                'stock_opname.status',
                'stock_opname.keterangan'
            )
            ->orderBy('barang.nama_barang')
            ->get();

        return view('transaksi.StockOpnameList', compact('barang', 'bulan'));
    }

    public function mulaiOpname(Request $request)
    {
        $unitId = Auth::user()->unit_kerja;
        $userId = Auth::user()->id;
        $tglOpname = $request->tgl_opname ?? Carbon::now()->format('Y-m-d');

        $bulanOpname = Carbon::parse($tglOpname)->format('Y-m');
        $startDate = Carbon::createFromFormat('Y-m', $bulanOpname)->startOfMonth();
        $endDate   = Carbon::createFromFormat('Y-m', $bulanOpname)->endOfMonth();

        DB::beginTransaction();
        try {
            // 1. Hapus data lama (lebih efisien)
            DB::table('stock_opname')
                ->where('id_unit', $unitId)
                ->whereBetween('tgl_opname', [$startDate, $endDate])
                ->delete();

            // 2. Query semua barang + stok unit
            $barangQuery = DB::table('barang')
                ->leftJoin('stok_unit', function ($join) use ($unitId) {
                    $join->on('barang.id', '=', 'stok_unit.barang_id')
                        ->where('stok_unit.unit_id', '=', $unitId)
                        ->whereNull('stok_unit.deleted_at');
                })
                ->select(
                    'barang.id as id_barang',
                    'barang.kode_barang',
                    DB::raw('IFNULL(stok_unit.stok, 0) as stok_unit')
                );

            // 3. Proses per chunk agar hemat memory & lebih cepat
            $barangQuery->orderBy('barang.id')->chunk(1000, function ($barangList) use ($tglOpname, $unitId, $userId) {
                $dataInsert = [];
                foreach ($barangList as $barang) {
                    $dataInsert[] = [
                        'tgl_opname'   => $tglOpname,
                        'id_unit'      => $unitId,
                        'id_barang'    => $barang->id_barang,
                        'kode_barang'  => $barang->kode_barang,
                        'stock_sistem' => $barang->stok_unit,
                        'stock_fisik'  => null,
                        'keterangan'   => null,
                        'user'         => $userId,
                        'status'       => 'pending',
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ];
                }

                // Sekali insert 1000 row
                if (!empty($dataInsert)) {
                    DB::table('stock_opname')->insert($dataInsert);
                }
            });

            DB::commit();
            return redirect()->route('stockopname.index', ['bulan' => $bulanOpname])
                ->with('success', 'Stock opname bulan ' . $bulanOpname . ' berhasil dimulai.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function updateOpname(Request $request, $id)
    {
        $request->validate([
            'stock_fisik' => 'required|integer|min:0'
        ]);

        DB::table('stock_opname')->where('id', $id)->update([
            'stock_fisik' => $request->stock_fisik,
            'keterangan'  => $request->keterangan,
            'status'      => 'sukses',
            'updated_at'  => now()
        ]);

        return redirect()->route('stockopname.index')->with('success', 'Stock opname berhasil disimpan.');
    }

    public function form(Request $request): View
    {
        $selectedBarang = null;

        if ($request->has('barang_id')) {
            $selectedBarang = StokUnit::join('barang','barang.id','stok_unit.barang_id')
                ->where('stok_unit.unit_id', Auth::user()->unit_kerja)
                ->where('barang.id', $request->barang_id)
                ->select('barang.id','barang.kode_barang as code','barang.nama_barang as text','stok_unit.stok','barang.harga_beli','barang.harga_jual')
                ->first();
        }

        return view('transaksi.StockOpname', compact('selectedBarang'));
    }

    public function indexold(Request $request): View
    {
        return view('transaksi.StockOpname');
    }
    public function getBarang(Request $request){
        $barang = StokUnit::join('barang','barang.id','stok_unit.barang_id')
        ->where('stok_unit.unit_id',Auth::user()->unit_kerja)
        ->whereRaw("CONCAT(barang.kode_barang, barang.nama_barang) LIKE ?", ["%{$request->q}%"])
        ->select('barang.id','barang.kode_barang as code','barang.nama_barang as text','stok_unit.stok','barang.harga_beli','barang.harga_jual')
        ->get();
        return response()->json($barang);
    }
    public function getBarangByCode(Request $request){
        $barang = StokUnit::join('barang','barang.id','stok_unit.barang_id')
        ->where("barang.kode_barang", "=",$request->kode)
        ->where("stok_unit.unit_id", "=",Auth::user()->unit_kerja)
        ->select('barang.id','barang.kode_barang as code','barang.nama_barang as text','stok_unit.stok','barang.harga_beli','barang.harga_jual')
        ->first();
        if($barang){
            return response()->json($barang);
        }else{
            return response()->json('error',404);
        }
        
    }
    public function Store(Request $request)
{
    DB::beginTransaction();
    try {
        // Validasi sederhana
        if (!$request->filled('tgl_opname') || 
            !is_array($request->id) || count($request->id) === 0 ||
            !is_array($request->qty) || count($request->qty) === 0 ||
            !is_array($request->exp) || count($request->exp) === 0 ||
            !is_array($request->code) || count($request->code) === 0) {
            return response()->json(['error' => 'Incomplete data'], 400);
        }

        $date = Carbon::parse($request->tgl_opname);
        $unitId = Auth::user()->unit_kerja;
        $userId = Auth::user()->id;

        $dataGrouped = [];

        foreach ($request->id as $index => $idBarang) {
            $dataGrouped[$idBarang]['code'] = $request->code[$index] ?? null;
            $dataGrouped[$idBarang]['items'][] = [
                'qty' => $request->qty[$index] ?? 0,
                'exp' => $request->exp[$index] ?? null,
            ];
        }

        foreach ($dataGrouped as $idBarang => $group) {
            $totalQty = array_sum(array_column($group['items'], 'qty'));

            $stoksys = StokUnit::where([
                'barang_id' => $idBarang,
                'unit_id' => $unitId
            ])->first();

            if (!$stoksys) {
                throw new Exception("Stok untuk barang ID {$idBarang} tidak ditemukan.");
            }

            // 🔑 Cek apakah sudah ada HDR opname pada tanggal yg sama
            $hdr = StockOpnameHDR::where([
                'id_unit' => $unitId,
                'id_barang' => $idBarang,
                'tgl_opname' => $date->format('Y-m-d'),
            ])->first();

            if (!$hdr) {
                $hdr = new StockOpnameHDR();
                $hdr->id_unit = $unitId;
                $hdr->id_barang = $idBarang;
                $hdr->kode_barang = $group['code'];
                $hdr->tgl_opname = $date->format('Y-m-d');
                $hdr->user = $userId;
            }

            $hdr->stock_sistem = $stoksys->stok;
            $hdr->stock_fisik = $totalQty;
            $hdr->status = "sukses";
            $hdr->save();

            // 🔑 Hapus DTL lama kalau ada, biar tidak duplikat
            StockOpnameDTL::where('opnameid', $hdr->id)->delete();

            foreach ($group['items'] as $item) {
                $dtl = new StockOpnameDTL();
                $dtl->opnameid = $hdr->id;
                $dtl->id_barang = $idBarang;
                $dtl->qty = $item['qty'];
                $dtl->expired_date = $item['exp'];
                $dtl->save();
            }

            // Update stok sistem
            $stoksys->stok = $totalQty;
            $stoksys->save();
        }

        DB::commit();
        return response()->json([
            'success' => true,
            'redirect' => url('/stock'),
            'message' => 'Stock opname berhasil disimpan.'
        ]);
    } catch (Exception $e) {
        DB::rollBack();
        return response()->json([
            'error' => 'Gagal menyimpan stok opname',
            'message' => $e->getMessage()
        ], 500);
    }
}


}
