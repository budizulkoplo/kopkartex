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

    // Tentukan periode bulan ini (misalnya berdasarkan tgl_opname)
    $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
    $endDate   = Carbon::now()->endOfMonth()->format('Y-m-d');

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

    return view('transaksi.StockOpnameList', compact('barang'));
}


    public function mulaiOpname()
    {
        $unitId = Auth::user()->unit_kerja;
        $userId = Auth::user()->id;
        $tglOpname = Carbon::now()->format('Y-m-d');

        DB::beginTransaction();
        try {
            $barangList = StokUnit::join('barang', 'barang.id', '=', 'stok_unit.barang_id')
                ->where('stok_unit.unit_id', $unitId)
                ->whereNull('stok_unit.deleted_at')
                ->select(
                    'barang.id as id_barang',
                    'barang.kode_barang',
                    'stok_unit.stok as stok_unit'
                )
                ->get();

            foreach ($barangList as $barang) {
                DB::table('stock_opname')->insert([
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
                ]);
            }

            DB::commit();
            return redirect()->route('stockopname.index')->with('success', 'Stock opname berhasil dimulai. Silakan input stok fisik.');
        } catch (Exception $e) {
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
