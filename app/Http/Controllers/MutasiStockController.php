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
            // 'roles' => Role::with('permissions')->get(),
            // 'allroles' => Role::all(),
            //'unit' => Unit::all(),
        ]);
    }
    public function FormMutasi(Request $request): View
    {
        return view('transaksi.MutasiStok', [
            // 'roles' => Role::with('permissions')->get(),
            // 'allroles' => Role::all(),
            'unit' => Unit::all(),
        ]);
    }
    public function GetData(Request $request){
        $barang = MutasiStok::join('users','users.id','mutasi_stok.created_user')
        ->join('unit as unit1','unit1.id','mutasi_stok.dari_unit')
        ->join('unit as unit2','unit2.id','mutasi_stok.ke_unit')
        ->whereBetween('mutasi_stok.tanggal', [$request->startdate, $request->enddate])
        ->select('mutasi_stok.id','mutasi_stok.tanggal','mutasi_stok.status','users.name as petugas','unit1.nama_unit as NamaUnit1','unit2.nama_unit as NamaUnit2');
        return DataTables::of($barang)
        ->addIndexColumn()
        ->filter(function ($query) use ($request) {
            if ($request->has('search') && $request->search != '') {
                $query->where(function ($query2) use($request) {
                    return $query2
                    ->orWhere('users.name','like','%'.$request->search['value'].'%');
                }); 
            }
        })
        ->make(true);
    }
    public function GetDataDTL(Request $request){
        $barang = MutasiStok::join('mutasi_stok_detail','mutasi_stok_detail.mutasi_id','mutasi_stok.id')
        ->join('barang','barang.id','mutasi_stok_detail.barang_id')
        // ->join('unit as unit1','unit1.id','mutasi_stok.dari_unit')
        // ->join('unit as unit2','unit2.id','mutasi_stok.ke_unit')
        ->where('mutasi_stok.id',$request->id)
        ->select('mutasi_stok.id',
        'mutasi_stok.tanggal',
        'mutasi_stok_detail.qty',
        'barang.nama_barang',
        'barang.kode_barang',
        'mutasi_stok_detail.canceled',
        'mutasi_stok_detail.barang_id',
        )->get();
               // $sql = vsprintf(str_replace('?', '"%s"', $barang->toSql()), $barang->getBindings());return response()->json($sql);

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
            return response()->json('error',404);
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
            return response()->json('error',404);
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
    public function Kembalikan(Request $request){
        $cekhdr = MutasiStok::find($request->idmutasi);
        $dtl = MutasiStokDetail::where(['mutasi_id'=>$request->idmutasi,'barang_id'=>$request->idbarang])->first();
        if($dtl){
            $qty = (int) $dtl->qty;

            DB::table('stok_unit')
                ->where('barang_id', $request->idbarang)
                ->where('unit_id', $cekhdr->dari_unit)
                ->update([
                    'stok' => DB::raw("stok + {$qty}")
                ]);
            DB::table('stok_unit')
                ->where('barang_id', $request->idbarang)
                ->where('unit_id', $cekhdr->ke_unit)
                ->update([
                    'stok' => DB::raw("stok - {$qty}")
                ]);
            $dtlupdate = MutasiStokDetail::find($dtl->id);
            $dtlupdate->canceled=1;
            $dtlupdate->save();
            return response()->json('ok');
        }else{
            return response()->json('error',404);
        }
    }
}
