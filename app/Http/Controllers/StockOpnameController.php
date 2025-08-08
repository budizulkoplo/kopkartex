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

        $barang = DB::table('stok_unit')
            ->join('barang', 'barang.id', '=', 'stok_unit.barang_id')
            ->leftJoin(DB::raw('(
                SELECT so1.*
                FROM stock_opname AS so1
                INNER JOIN (
                    SELECT id_barang, MAX(created_at) as max_created
                    FROM stock_opname
                    WHERE deleted_at IS NULL
                    GROUP BY id_barang
                ) AS so2 ON so1.id_barang = so2.id_barang AND so1.created_at = so2.max_created
            ) AS opname'), function ($join) use ($unitId) {
                $join->on('stok_unit.barang_id', '=', 'opname.id_barang')
                    ->where('opname.id_unit', '=', $unitId);
            })
            ->where('stok_unit.unit_id', $unitId)
            ->whereNull('stok_unit.deleted_at')
            ->select(
                'barang.id',
                'barang.kode_barang',
                'barang.nama_barang',
                'stok_unit.stok as stok_unit',
                'opname.stock_sistem',
                'opname.stock_fisik',
                'opname.id as opname_id'
            )
            ->orderBy('barang.nama_barang')
            ->get();

        return view('transaksi.StockOpnameList', compact('barang'));
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
            if (!$request->has(['tgl_opname', 'id', 'qty', 'exp', 'code'])) {
                return response()->json(['error' => 'Incomplete data'], 400);
            }

            $date = Carbon::parse($request->tgl_opname);
            $unitId = Auth::user()->unit_kerja;
            $userId = Auth::user()->id;

            // Gabungkan dan kelompokkan berdasarkan ID barang
            $dataGrouped = [];

            foreach ($request->id as $index => $idBarang) {
                $dataGrouped[$idBarang]['code'] = $request->code[$index];
                $dataGrouped[$idBarang]['items'][] = [
                    'qty' => $request->qty[$index],
                    'exp' => $request->exp[$index],
                ];
            }

            // Simpan HDR dan DTL
            foreach ($dataGrouped as $idBarang => $group) {
                $totalQty = array_sum(array_column($group['items'], 'qty'));

                $stoksys = StokUnit::where([
                    'barang_id' => $idBarang,
                    'unit_id' => $unitId
                ])->first();

                if (!$stoksys) {
                    throw new Exception("Stok untuk barang ID {$idBarang} tidak ditemukan.");
                }

                // Simpan HDR
                $hdr = new StockOpnameHDR();
                $hdr->id_unit = $unitId;
                $hdr->id_barang = $idBarang;
                $hdr->kode_barang = $group['code'];
                $hdr->tgl_opname = $date->format('Y-m-d');
                $hdr->user = $userId;
                $hdr->stock_sistem = $stoksys->stok;
                $hdr->stock_fisik = $totalQty;
                $hdr->save();

                // Simpan DTL
                foreach ($group['items'] as $item) {
                    $dtl = new StockOpnameDTL();
                    $dtl->opnameid = $hdr->id;
                    $dtl->id_barang = $idBarang;
                    $dtl->qty = $item['qty'];
                    $dtl->expired_date = $item['exp'];
                    $dtl->save();
                }

                // (Opsional) Update stok berdasarkan hasil opname
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
