<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\MutasiStok;
use App\Models\MutasiStokDetail;
use App\Models\StokUnit;
use App\Models\Unit;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class MutasiStockController extends Controller
{
    public function index(Request $request): View
    {
        return view('transaksi.MutasiStokList', [
            'unit' => Unit::all(), // Tambahkan ini
        ]);
    }
    
    public function FormMutasi(Request $request): View
    {
        return view('transaksi.MutasiStok', [
            'unit' => Unit::all(),
        ]);
    }
    
    public function GetData(Request $request){
        $barang = MutasiStok::join('users','users.id','mutasi_stok.created_user')
            ->join('unit as unit1','unit1.id','mutasi_stok.dari_unit')
            ->join('unit as unit2','unit2.id','mutasi_stok.ke_unit')
            ->whereBetween('mutasi_stok.tanggal', [$request->startdate, $request->enddate])
            ->select(
                'mutasi_stok.id',
                'mutasi_stok.nomor_invoice',
                'mutasi_stok.tanggal',
                'mutasi_stok.status',
                'users.name as petugas',
                'unit1.nama_unit as NamaUnit1',
                'unit2.nama_unit as NamaUnit2',
                'mutasi_stok.note'
            );
            
        return DataTables::of($barang)
            ->addIndexColumn()
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search != '') {
                    $query->where(function ($query2) use($request) {
                        return $query2
                            ->orWhere('users.name','like','%'.$request->search['value'].'%')
                            ->orWhere('unit1.nama_unit','like','%'.$request->search['value'].'%')
                            ->orWhere('unit2.nama_unit','like','%'.$request->search['value'].'%');
                    }); 
                }
            })
            ->make(true);
    }
    
    public function GetDataDTL(Request $request){
        $barang = MutasiStok::join('mutasi_stok_detail','mutasi_stok_detail.mutasi_id','mutasi_stok.id')
            ->join('barang','barang.id','mutasi_stok_detail.barang_id')
            ->where('mutasi_stok.id',$request->id)
            ->select(
                'mutasi_stok.id',
                'mutasi_stok.tanggal',
                'mutasi_stok_detail.qty',
                'barang.nama_barang',
                'barang.kode_barang',
                'mutasi_stok_detail.canceled',
                'mutasi_stok_detail.barang_id'
            )->get();
               
        if($barang){
            return response()->json($barang);
        }else{
            return response()->json('error',404);
        }
    }
    
    public function getBarangByCode(Request $request){
        $barang = StokUnit::join('barang','barang.id','stok_unit.barang_id')
            ->where("barang.kode_barang", "=",$request->kode)
            ->where('stok_unit.unit_id',$request->unit)
            ->select('barang.id','barang.kode_barang as code','barang.nama_barang as text','stok_unit.stok')
            ->first();
            
        if($barang){
            return response()->json($barang);
        }else{
            return response()->json(['error' => 'Barang tidak ditemukan'], 404);
        }
    }
    
    public function getBarang(Request $request){
        $barang = StokUnit::join('barang','barang.id','stok_unit.barang_id')
            ->whereRaw("CONCAT(barang.kode_barang, barang.nama_barang) LIKE ?", ["%{$request->q}%"])
            ->where('stok_unit.unit_id',$request->unit)
            ->select('barang.id','barang.kode_barang as code','barang.nama_barang as text','stok_unit.stok')
            ->get();
            
        if($barang){
            return response()->json($barang);
        }else{
            return response()->json(['error' => 'Barang tidak ditemukan'], 404);
        }
    }
    public function store(Request $request){
        DB::beginTransaction();
        try {
            $formattedDate = Carbon::parse($request->date)->format('Y-m-d');
            $hdr=new MutasiStok;
            $hdr->dari_unit = $request->unit1;
            $hdr->ke_unit = $request->unit2;
            $hdr->tanggal = $formattedDate;
            $hdr->status = 1;
            $hdr->note = $request->note;
            $hdr->created_user = auth()->user()->id;
            $hdr->save();
            $idhdr = $hdr->id;

            $quantities = $request->input('qty');
            $barang = $request->input('id');
            foreach ($barang as $index => $id) {
                $dtl = new MutasiStokDetail;
                $dtl->mutasi_id = $idhdr;
                $dtl->barang_id = $barang[$index];
                $dtl->qty = $quantities[$index];
                $dtl->save();
                DB::statement("
                    INSERT INTO stok_unit (barang_id, unit_id, stok, updated_at,created_at) VALUES (?, ?, ? ,NOW(),NOW())
                    ON DUPLICATE KEY UPDATE stok = stok - ?,updated_at=VALUES(updated_at),created_at=VALUES(created_at)", 
                    [$barang[$index], $request->unit1, $quantities[$index], $quantities[$index]]);
                DB::statement("
                    INSERT INTO stok_unit (barang_id, unit_id, stok, updated_at,created_at) VALUES (?, ?, ? ,NOW(),NOW())
                    ON DUPLICATE KEY UPDATE stok = stok + ?,updated_at=VALUES(updated_at),created_at=VALUES(created_at)", 
                    [$barang[$index], $request->unit2, $quantities[$index], $quantities[$index]]);
            }
            DB::commit();
            return response()->json($hdr);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }

    public function detail($id)
    {
        try {
            $hdr = MutasiStok::join('users','users.id','mutasi_stok.created_user')
                ->join('unit as unit_asal', 'unit_asal.id', '=', 'mutasi_stok.dari_unit')
                ->join('unit as unit_tujuan', 'unit_tujuan.id', '=', 'mutasi_stok.ke_unit')
                ->select(
                    'mutasi_stok.*',
                    'users.name as petugas',
                    'unit_asal.nama_unit as nama_unit_asal',
                    'unit_tujuan.nama_unit as nama_unit_tujuan'
                )
                ->where('mutasi_stok.id', $id)
                ->first();

            if (!$hdr) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mutasi tidak ditemukan'
                ], 404);
            }

            // Format data
            $hdr->tanggal_formatted = \Carbon\Carbon::parse($hdr->tanggal)->format('d/m/Y');

            return response()->json([
                'success' => true,
                'data' => $hdr
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail mutasi: ' . $e->getMessage()
            ], 500);
        }
    }
    public function batalkan(Request $request)
    {
        DB::beginTransaction();
        try {
            $mutasi = MutasiStok::find($request->id);
            
            if (!$mutasi) {
                throw new Exception('Mutasi tidak ditemukan.');
            }
            
            // Cek apakah mutasi sudah dibatalkan
            if ($mutasi->status == 'dibatalkan') {
                throw new Exception('Mutasi sudah dibatalkan.');
            }
            
            // Ambil semua detail mutasi
            $details = MutasiStokDetail::where('mutasi_id', $mutasi->id)
                ->where('canceled', 0)
                ->get();
            
            // Kembalikan stok untuk setiap barang
            foreach ($details as $detail) {
                // Kembalikan ke unit asal (tambah stok)
                DB::table('stok_unit')
                    ->where('barang_id', $detail->barang_id)
                    ->where('unit_id', $mutasi->dari_unit)
                    ->update([
                        'stok' => DB::raw("stok + {$detail->qty}")
                    ]);
                    
                // Kurangi dari unit tujuan
                DB::table('stok_unit')
                    ->where('barang_id', $detail->barang_id)
                    ->where('unit_id', $mutasi->ke_unit)
                    ->update([
                        'stok' => DB::raw("stok - {$detail->qty}")
                    ]);
                    
                // Update status detail menjadi canceled
                $detail->canceled = 1;
                $detail->save();
            }
            
            // Update status mutasi menjadi dibatalkan
            $mutasi->status = 'dibatalkan';
            $mutasi->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Mutasi berhasil dibatalkan'
            ]);
            
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function nota($id): View
    {   
        $hdr = MutasiStok::join('users','users.id','mutasi_stok.created_user')
            ->join('unit as unit_asal', 'unit_asal.id', '=', 'mutasi_stok.dari_unit')
            ->join('unit as unit_tujuan', 'unit_tujuan.id', '=', 'mutasi_stok.ke_unit')
            ->select(
                'mutasi_stok.*',
                'users.name as petugas',
                'unit_asal.nama_unit as nama_unit_asal',
                'unit_tujuan.nama_unit as nama_unit_tujuan'
            )
            ->where('mutasi_stok.id', $id)
            ->firstOrFail();

        // Ambil detail dengan join ke tabel barang
        $dtl = MutasiStokDetail::join('barang', 'barang.id', '=', 'mutasi_stok_detail.barang_id')
            ->where('mutasi_stok_detail.mutasi_id', $hdr->id)
            ->where('mutasi_stok_detail.canceled', 0)
            ->select(
                'mutasi_stok_detail.*',
                'barang.nama_barang',
                'barang.type',
                'barang.kode_barang'
            )
            ->get();

        // Format nomor mutasi
        $hdr->nomor_mutasi = 'MUT-' . str_pad($hdr->id, 6, '0', STR_PAD_LEFT);

        return view('transaksi.mutasi-nota', [
            'hdr' => $hdr,
            'dtl' => $dtl,
        ]);
    }

    public function updateStatus(Request $request)
    {
        DB::beginTransaction();
        try {
            $mutasi = MutasiStok::find($request->id);
            
            if (!$mutasi) {
                throw new Exception('Mutasi tidak ditemukan.');
            }
            
            $mutasi->status = $request->status;
            $mutasi->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Status mutasi berhasil diupdate'
            ]);
            
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function Kembalikan(Request $request){
    DB::beginTransaction();
    try {
        $cekhdr = MutasiStok::find($request->idmutasi);
        
        if (!$cekhdr) {
            throw new Exception('Mutasi tidak ditemukan.');
        }
        
        $dtl = MutasiStokDetail::where([
            'mutasi_id' => $request->idmutasi,
            'barang_id' => $request->idbarang
        ])->first();
        
        if (!$dtl) {
            throw new Exception('Detail barang tidak ditemukan.');
        }
        
        // Cek apakah barang sudah dikembalikan
        if ($dtl->canceled == 1) {
            throw new Exception('Barang sudah dikembalikan sebelumnya.');
        }
        
        $qty = (int) $dtl->qty;

        // Kembalikan stok ke unit asal
        DB::table('stok_unit')
            ->where('barang_id', $request->idbarang)
            ->where('unit_id', $cekhdr->dari_unit)
            ->update([
                'stok' => DB::raw("stok + {$qty}")
            ]);
            
        // Kurangi stok dari unit tujuan
        DB::table('stok_unit')
            ->where('barang_id', $request->idbarang)
            ->where('unit_id', $cekhdr->ke_unit)
            ->update([
                'stok' => DB::raw("stok - {$qty}")
            ]);
            
        // Update status detail menjadi canceled
        $dtl->canceled = 1;
        $dtl->save();
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil dikembalikan'
        ]);
        
    } catch (Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}
}
