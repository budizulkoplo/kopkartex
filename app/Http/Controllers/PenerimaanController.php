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
use App\Models\Supplier;

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

    public function getSupplier(Request $request)
    {
        $q = $request->q;
        $supplier = Supplier::where('nama_supplier', 'LIKE', "%$q%")
            ->select('id', 'nama_supplier as text')
            ->get();

        return response()->json($supplier);
    }

    public function Riwayat(Request $request)
    {
        $tanggalAwal = $request->tanggal_awal ?? date('Y-m-d');
        $tanggalAkhir = $request->tanggal_akhir ?? date('Y-m-d');

        $query = Penerimaan::with(['details.barang', 'user'])
            ->whereDate('tgl_penerimaan', '>=', $tanggalAwal)
            ->whereDate('tgl_penerimaan', '<=', $tanggalAkhir)
            ->orderBy('tgl_penerimaan', 'desc');

        if($request->supplier){
            $query->where('nama_supplier', 'LIKE', "%{$request->supplier}%");
        }

        $penerimaan = $query->get();

        return view('transaksi.riwayatpenerimaan', [
            'penerimaan' => $penerimaan,
            'tanggal_awal' => $tanggalAwal,
            'tanggal_akhir' => $tanggalAkhir,
            'supplier' => $request->supplier ?? ''
        ]);
    }

    public function getDetail($id)
    {
        try {
            $penerimaan = Penerimaan::with(['details.barang'])->findOrFail($id);

            $detail = $penerimaan->details->map(function ($d) {
                return [
                    'id'               => $d->id,
                    'barang_id'        => $d->barang_id,
                    'kode_barang'      => $d->barang->kode_barang ?? '',
                    'nama_barang'      => $d->barang->nama_barang ?? '',
                    'jumlah'           => $d->jumlah,
                    'harga_beli'       => $d->harga_beli,
                    'harga_jual'       => $d->harga_jual,
                    'expired_date'     => $d->expired_date ? $d->expired_date->format('Y-m-d') : null,
                    'total_harga_beli' => $d->jumlah * $d->harga_beli,
                ];
            });

            $grandTotal = $detail->sum('total_harga_beli');

            return response()->json([
                'success'     => true,
                'penerimaan'  => [
                    'idpenerimaan'   => $penerimaan->idpenerimaan,
                    'nomor_invoice'  => $penerimaan->nomor_invoice,
                    'tgl_penerimaan' => $penerimaan->tgl_penerimaan->format('Y-m-d'),
                    'nama_supplier'  => $penerimaan->nama_supplier,
                    'note'           => $penerimaan->note,
                ],
                'detail'      => $detail,
                'grand_total' => $grandTotal
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail penerimaan',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Proses revisi penerimaan
     */
    public function prosesRevisi(Request $request)
    {
        DB::beginTransaction();
        try {
            $penerimaanId = $request->penerimaan_id;
            $items = $request->items;
            
            $penerimaan = Penerimaan::findOrFail($penerimaanId);
            
            foreach ($items as $item) {
                $detail = PenerimaanDtl::findOrFail($item['id']);
                $oldQty = $item['old_qty'];
                $newQty = $item['new_qty'];
                
                if ($newQty != $oldQty) {
                    $selisih = $newQty - $oldQty;
                    
                    // Update detail penerimaan
                    $detail->jumlah = $newQty;
                    $detail->save();
                    
                    // Adjust stok
                    if ($selisih != 0) {
                        // Untuk revisi penerimaan, selisih positif artinya tambah stok
                        // selisih negatif artinya kurangi stok
                        DB::statement("
                            INSERT INTO stok_unit (barang_id, unit_id, stok, updated_at, created_at) 
                            VALUES (?, ?, ?, NOW(), NOW())
                            ON DUPLICATE KEY UPDATE 
                                stok = stok + VALUES(stok),
                                updated_at = VALUES(updated_at)",
                            [$detail->barang_id, 1, $selisih]
                        );
                    }
                    
                    // Catat history revisi
                    DB::table('revisi_penerimaan')->insert([
                        'penerimaan_id' => $penerimaanId,
                        'penerimaan_dtl_id' => $detail->id,
                        'barang_id' => $detail->barang_id,
                        'qty_lama' => $oldQty,
                        'qty_baru' => $newQty,
                        'selisih' => $selisih,
                        'keterangan' => 'Revisi penerimaan',
                        'created_at' => now(),
                        'created_user' => auth()->id()
                    ]);
                }
                
                // Jika action adalah delete
                if (isset($item['action']) && $item['action'] == 'delete') {
                    // Kembalikan stok
                    DB::statement("
                        UPDATE stok_unit 
                        SET stok = stok - ?, 
                            updated_at = NOW()
                        WHERE barang_id = ? 
                        AND unit_id = ?",
                        [$oldQty, $detail->barang_id, 1]
                    );
                    
                    // Hapus detail
                    $detail->delete();
                    
                    // Catat history hapus
                    DB::table('revisi_penerimaan')->insert([
                        'penerimaan_id' => $penerimaanId,
                        'penerimaan_dtl_id' => $item['id'],
                        'barang_id' => $detail->barang_id,
                        'qty_lama' => $oldQty,
                        'qty_baru' => 0,
                        'selisih' => -$oldQty,
                        'keterangan' => 'Hapus item dari penerimaan',
                        'created_at' => now(),
                        'created_user' => auth()->id()
                    ]);
                }
            }
            
            // Recalculate grand total
            $newTotal = PenerimaanDtl::where('idpenerimaan', $penerimaanId)
                ->select(DB::raw('SUM(jumlah * harga_beli) as total'))
                ->value('total');
            
            // Update penerimaan jika diperlukan
            // (bisa tambah field total_revisi jika perlu)
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Revisi berhasil disimpan'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses revisi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Batalkan penerimaan (jika diperlukan)
     */
    public function batalkanPenerimaan($id)
    {
        DB::beginTransaction();
        try {
            $penerimaan = Penerimaan::with('details')->findOrFail($id);
            
            // Kembalikan semua stok
            foreach ($penerimaan->details as $detail) {
                DB::statement("
                    UPDATE stok_unit 
                    SET stok = stok - ?, 
                        updated_at = NOW()
                    WHERE barang_id = ? 
                    AND unit_id = ?",
                    [$detail->jumlah, $detail->barang_id, 1]
                );
            }
            
            // Hapus detail
            $penerimaan->details()->delete();
            
            // Hapus header
            $penerimaan->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Penerimaan berhasil dibatalkan'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan penerimaan: ' . $e->getMessage()
            ], 500);
        }
    }

}