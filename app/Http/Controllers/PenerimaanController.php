<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Penerimaan;
use App\Models\PenerimaanDtl;
use App\Models\StokUnit;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PenerimaanController extends Controller
{
    public function index(Request $request): View
    {
        return view('transaksi.penerimaan', [
            // 'roles' => Role::with('permissions')->get(),
            // 'allroles' => Role::all(),
            // 'unit' => Unit::all(),
        ]);
    }
    public function getBarang(Request $request){
        $barang = Barang::whereRaw("CONCAT(kode_barang, nama_barang) LIKE ?", ["%{$request->q}%"])
            ->select('id','kode_barang as code','nama_barang as text','harga_beli','harga_jual')
            ->get();
        return response()->json($barang);
    }

    public function getBarangByCode(Request $request){
        $barang = Barang::where("kode_barang", "=",$request->kode)
            ->select('id','kode_barang as code','nama_barang as text','harga_beli','harga_jual')
            ->first();
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

            $hdr=new Penerimaan;
            $hdr->nomor_invoice = $request->invoice;
            $hdr->tgl_penerimaan = $formattedDate;
            $hdr->nama_supplier = $request->supplier;
            $hdr->note = $request->note;
            $hdr->user_id = auth()->user()->id;
            $hdr->save();
            $idhdr = $hdr->id;

            $quantities   = $request->input('qty');
            $barang       = $request->input('id');
            $hargaBeliArr = $request->input('harga_beli');
            $hargaJualArr = $request->input('harga_jual');

            foreach ($barang as $index => $id) {
                // insert detail penerimaan
                $dtl = new PenerimaanDtl;
                $dtl->idpenerimaan = $idhdr;
                $dtl->barang_id    = $barang[$index];
                $dtl->jumlah       = $quantities[$index];
                $dtl->harga_beli   = $hargaBeliArr[$index] ?? 0;
                $dtl->harga_jual   = $hargaJualArr[$index] ?? 0;
                $dtl->save();

                // update stok
                DB::statement("
                    INSERT INTO stok_unit (barang_id, unit_id, stok, updated_at,created_at) 
                    VALUES (?, ?, ?, NOW(), NOW())
                    ON DUPLICATE KEY UPDATE 
                        stok = stok + VALUES(stok),
                        updated_at=VALUES(updated_at)",
                    [$barang[$index], 1, $quantities[$index]]
                );

                // update harga di master barang
                Barang::where('id', $barang[$index])->update([
                    'harga_beli' => $hargaBeliArr[$index],
                    'harga_jual' => $hargaJualArr[$index],
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return response()->json($hdr);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }

}
