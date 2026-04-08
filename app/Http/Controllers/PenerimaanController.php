<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Penerimaan;
use App\Models\PenerimaanDtl;
use App\Models\StokUnit;
use App\Models\Supplier;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Models\Satuan;
use App\Models\Kategori;
use Illuminate\Support\Facades\Auth;

class PenerimaanController extends Controller
{
    public function index(Request $request): View
    {
        // Ambil data satuan dan kategori untuk dropdown
        $satuans = Satuan::select('id', 'name')->get();
        $kategoris = Kategori::select('id', 'name')->get();
        
        return view('transaksi.penerimaan', [
            'invoice' => $this->genCode(),
            'satuans' => $satuans,
            'kategoris' => $kategoris,
        ]);
    }

    // Fungsi generate kode invoice otomatis
    private function genCode()
    {
        $total = Penerimaan::withTrashed()->whereDate('created_at', date("Y-m-d"))->count();
        $nomorUrut = $total + 1;
        return 'RCV-' . date("ymd") . str_pad($nomorUrut, 3, '0', STR_PAD_LEFT);
    }

    // Method untuk mendapatkan invoice baru
    public function getInvoice()
    {
        return response()->json($this->genCode());
    }
    
    public function getBarang(Request $request)
    {
        $barang = Barang::whereRaw("CONCAT(kode_barang, nama_barang) LIKE ?", ["%{$request->q}%"])
            ->select('id', 'kode_barang as code', 'nama_barang as text', 'type', 'harga_beli', 'harga_jual', 'idsatuan', 'idkategori')
            ->get()
            ->map(function($item) {
                $satuan = Satuan::find($item->idsatuan);
                $kategori = Kategori::find($item->idkategori);
                $item->satuan = $satuan ? $satuan->name : '';
                $item->kategori = $kategori ? $kategori->name : '';
                return $item;
            });
        
        return response()->json($barang);
    }

    public function getBarangByCode(Request $request)
    {
        $barang = Barang::where("kode_barang", "=", $request->kode)
            ->select('id', 'kode_barang as code', 'nama_barang as text', 'type', 'harga_beli', 'harga_jual', 'idsatuan', 'idkategori')
            ->first();
            
        if($barang){
            $satuan = Satuan::find($barang->idsatuan);
            $kategori = Kategori::find($barang->idkategori);
            $barang->satuan = $satuan ? $satuan->name : '';
            $barang->kategori = $kategori ? $kategori->name : '';
            return response()->json($barang);
        }else{

            return response()->json(['error' => 'Barang tidak ditemukan'], 404);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $formattedDate = Carbon::parse($request->date)->format('Y-m-d H:i:s');
            
            // Validasi supplier
            if (!$request->supplier_id) {
                throw new Exception('Supplier harus dipilih.');
            }
            
            // Cari supplier berdasarkan ID
            $supplier = Supplier::find($request->supplier_id);
            if (!$supplier) {
                throw new Exception('Supplier tidak ditemukan.');
            }

            // Validasi metode bayar tempo
            if ($request->metode_bayar == 'tempo') {
                if (!$request->tgl_tempo) {
                    throw new Exception('Tanggal tempo harus diisi untuk pembayaran tempo.');
                }
                $tglTempo = Carbon::parse($request->tgl_tempo)->format('Y-m-d');
                $statusBayar = 'pending';
            } else {
                $tglTempo = null;
                $statusBayar = 'paid';
            }

            // Validasi ada barang yang ditambahkan
            $quantities = $request->input('qty', []);
            $barangIds = $request->input('barang_id', []);
            
            if (empty($barangIds) || empty($quantities)) {
                throw new Exception('Minimal ada 1 barang yang harus ditambahkan.');
            }

            // Simpan header penerimaan dengan ID dan kode supplier
            $hdr = new Penerimaan;
            $hdr->nomor_invoice = $request->invoice ?? $this->genCode();
            $hdr->tgl_penerimaan = $formattedDate;
            $hdr->idsupplier = $supplier->id;
            $hdr->kode_supplier = $supplier->kode_supplier;
            $hdr->nama_supplier = $supplier->nama_supplier;
            $hdr->note = $request->note;
            $hdr->user_id = auth()->user()->id;
            $hdr->metode_bayar = $request->metode_bayar;
            $hdr->tgl_tempo = $tglTempo;
            $hdr->status_bayar = $statusBayar;
            $hdr->save();
            
            $idhdr = $hdr->idpenerimaan;
            
            $hargaBeliArr = $request->input('harga_beli', []);
            $hargaJualArr = $request->input('harga_jual', []);
            $kodeBarangArr = $request->input('kode_barang', []);
            $namaBarangArr = $request->input('nama_barang', []);
            $ppnPersenArr = $request->input('ppn_persen', []);
            $satuanArr = $request->input('satuan', []);
            $kategoriArr = $request->input('kategori', []);
            $persenPpnGlobal = $request->input('persen_ppn_global', 0);

            $grandTotal = 0;

            foreach ($barangIds as $index => $barangId) {
                // Validasi quantity
                if (empty($quantities[$index]) || $quantities[$index] <= 0) {
                    throw new Exception("Quantity barang ke-" . ($index + 1) . " harus lebih dari 0.");
                }
                
                // Validasi harga beli
                if (empty($hargaBeliArr[$index]) || $hargaBeliArr[$index] < 0) {
                    throw new Exception("Harga beli barang ke-" . ($index + 1) . " tidak valid.");
                }
                
                // Validasi harga jual
                if (empty($hargaJualArr[$index]) || $hargaJualArr[$index] < 0) {
                    throw new Exception("Harga jual barang ke-" . ($index + 1) . " tidak valid.");
                }

                $kodeBarang = $kodeBarangArr[$index] ?? '';
                $namaBarang = $namaBarangArr[$index] ?? '';
                
                // Cek apakah ini barang baru (dimulai dengan 'new-')
                if (str_starts_with($barangId, 'new-')) {
                    throw new Exception('Barang baru harus disimpan terlebih dahulu sebelum bisa ditambahkan ke penerimaan.');
                }
                
                // Hitung PPN - gunakan PPN per item jika ada, jika tidak gunakan global
                $ppnPersen = $ppnPersenArr[$index] ?? $persenPpnGlobal;
                $subtotal = $quantities[$index] * ($hargaBeliArr[$index] ?? 0);
                $ppn = ($subtotal * $ppnPersen) / 100;
                $total = $subtotal + $ppn;
                $grandTotal += $total;

                // Insert detail penerimaan dengan harga_jual
                $dtl = new PenerimaanDtl;
                $dtl->idpenerimaan = $idhdr;
                $dtl->barang_id    = $barangId;
                $dtl->jumlah       = $quantities[$index];
                $dtl->harga_beli   = $hargaBeliArr[$index] ?? 0;
                $dtl->harga_jual   = $hargaJualArr[$index] ?? 0;
                $dtl->ppn          = $ppn;
                $dtl->subtotal     = $total;
                $dtl->save();

                // Update stok
                DB::statement("
                    INSERT INTO stok_unit (barang_id, unit_id, stok, updated_at, created_at) 
                    VALUES (?, ?, ?, NOW(), NOW())
                    ON DUPLICATE KEY UPDATE 
                        stok = stok + VALUES(stok),
                        updated_at = VALUES(updated_at)",
                    [$barangId, Auth::user()->unit_kerja, $quantities[$index]]
                );

                // Update harga di master barang
                Barang::where('id', $barangId)->update([
                    'harga_beli' => $hargaBeliArr[$index] ?? 0,
                    'harga_jual' => $hargaJualArr[$index] ?? 0,
                    'updated_at' => now(),
                ]);
            }

            // Update grand total di header
            $hdr->grandtotal = $grandTotal;
            $hdr->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Penerimaan berhasil disimpan',
                'invoice' => $hdr->nomor_invoice,
                'id' => $hdr->idpenerimaan
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getSupplier(Request $request)
    {
        $q = $request->q;
        $supplier = Supplier::where('nama_supplier', 'LIKE', "%$q%")
            ->select('id', 'kode_supplier', 'nama_supplier as text')
            ->get();

        return response()->json($supplier);
    }

    public function storeSupplier(Request $request)
    {
        try {
            $request->validate([
                'nama_supplier' => 'required|string|max:255',
            ]);

            // Generate kode supplier otomatis
            $kodeSupplier = 'SUP-' . date('ymd') . str_pad(Supplier::count() + 1, 3, '0', STR_PAD_LEFT);

            $supplier = new Supplier();
            $supplier->kode_supplier = $kodeSupplier;
            $supplier->nama_supplier = $request->nama_supplier;
            $supplier->alamat = $request->alamat ?? null;
            $supplier->telp = $request->telp ?? null;
            $supplier->kontak_person = $request->kontak_person ?? null;
            $supplier->email = $request->email ?? null;
            $supplier->save();


            return response()->json([
                'success' => true,
                'supplier' => [
                    'id' => $supplier->id,
                    'kode_supplier' => $supplier->kode_supplier,
                    'text' => $supplier->nama_supplier
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan supplier: ' . $e->getMessage()
            ], 500);
        }
    }

    public function Riwayat(Request $request)
    {
        $tanggalAwal = $request->tanggal_awal ?? date('Y-m-d');
        $tanggalAkhir = $request->tanggal_akhir ?? date('Y-m-d');

        $query = Penerimaan::with(['details.barang', 'user', 'supplier'])
            ->whereDate('tgl_penerimaan', '>=', $tanggalAwal)
            ->whereDate('tgl_penerimaan', '<=', $tanggalAkhir)
            ->orderBy('tgl_penerimaan', 'desc');

        if($request->supplier){
            $query->where('nama_supplier', 'LIKE', "%{$request->supplier}%");
        }

        $penerimaan = $query->paginate(25)->withQueryString();
        $penerimaan->getCollection()->transform(function ($item) {
            $item->grandtotal = $item->effective_grandtotal;
            return $item;
        });

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
            // Cari penerimaan berdasarkan idpenerimaan
            $penerimaan = Penerimaan::with(['details.barang', 'user', 'supplier'])
                ->where('idpenerimaan', $id)
                ->first();
            
            if (!$penerimaan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Penerimaan tidak ditemukan'
                ], 404);
            }

            $detail = $penerimaan->details->map(function ($d) {
                $barang = $d->barang;
                $baseSubtotal = (float) $d->jumlah * (float) $d->harga_beli;
                $ppnNominal = (float) $d->ppn;
                $ppnPersen = $baseSubtotal > 0 ? ($ppnNominal / $baseSubtotal) * 100 : 0;

                return [
                    'id'               => $d->id,
                    'barang_id'        => $d->barang_id,
                    'kode_barang'      => $barang->kode_barang ?? 'N/A',
                    'nama_barang'      => $barang->nama_barang ?? 'Tidak ditemukan',
                    'jumlah'           => $d->jumlah,
                    'harga_beli'       => $d->harga_beli,
                    'harga_jual'       => $d->harga_jual,
                    'ppn'              => $d->ppn,
                    'ppn_persen'       => round($ppnPersen, 2),
                    'subtotal'         => $d->subtotal ?? ($d->jumlah * $d->harga_beli),
                    'idsatuan'         => $barang->idsatuan ?? null,
                    'satuan'           => $barang->satuanRelation->name ?? '',
                    'created_at'       => $d->created_at,
                ];
            });


            $grandTotal = round($detail->sum(fn ($item) => (float) $item['subtotal']), 2);

            return response()->json([
                'success'     => true,
                'penerimaan'  => [
                    'idpenerimaan'   => $penerimaan->idpenerimaan,
                    'nomor_invoice'  => $penerimaan->nomor_invoice,
                    'tgl_penerimaan' => $penerimaan->tgl_penerimaan->format('d-m-Y H:i'),
                    'idsupplier'     => $penerimaan->idsupplier,
                    'kode_supplier'  => $penerimaan->kode_supplier,
                    'nama_supplier'  => $penerimaan->nama_supplier,
                    'note'           => $penerimaan->note ?? '',
                    'metode_bayar'   => $penerimaan->metode_bayar,
                    'tgl_tempo'      => $penerimaan->tgl_tempo ? $penerimaan->tgl_tempo->format('d-m-Y') : null,
                    'status_bayar'   => $penerimaan->status_bayar,
                    'grandtotal'     => $grandTotal,
                    'user_name'      => $penerimaan->user->name ?? '-'
                ],
                'detail'      => $detail,
                'satuan_options' => Satuan::query()->select('id', 'name')->orderBy('name')->get(),
                'grand_total' => $grandTotal
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail penerimaan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function prosesRevisi(Request $request)
    {
        $validated = $request->validate([
            'penerimaan_id' => 'required|exists:penerimaan,idpenerimaan',
            'tgl_penerimaan' => 'required|date',
            'metode_bayar' => 'required|in:cash,tempo',
            'tgl_tempo' => 'nullable|date',
            'note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:penerimaan_detail,id',
            'items.*.barang_id' => 'required|exists:barang,id',
            'items.*.old_qty' => 'required|numeric|min:0',
            'items.*.new_qty' => 'nullable|numeric|min:0',
            'items.*.harga_beli' => 'nullable|numeric|min:0',
            'items.*.harga_jual' => 'nullable|numeric|min:0',
            'items.*.idsatuan' => 'nullable|exists:satuan,id',
            'items.*.action' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $penerimaanId = $validated['penerimaan_id'];
            $penerimaan = Penerimaan::findOrFail($penerimaanId);

            if ($validated['metode_bayar'] === 'tempo' && empty($validated['tgl_tempo'])) {
                throw new Exception('Tanggal tempo wajib diisi jika metode bayar tempo.');
            }

            $penerimaan->tgl_penerimaan = Carbon::parse($validated['tgl_penerimaan'])->format('Y-m-d H:i:s');
            $penerimaan->metode_bayar = $validated['metode_bayar'];
            $penerimaan->tgl_tempo = $validated['metode_bayar'] === 'tempo'
                ? Carbon::parse($validated['tgl_tempo'])->format('Y-m-d')
                : null;
            $penerimaan->status_bayar = $validated['metode_bayar'] === 'tempo' ? 'pending' : 'paid';
            $penerimaan->note = $validated['note'] ?? null;
            $penerimaan->save();

            foreach ($validated['items'] as $item) {
                $detail = PenerimaanDtl::with('barang')->findOrFail($item['id']);
                if ((int) $detail->idpenerimaan !== (int) $penerimaanId) {
                    throw new Exception('Detail penerimaan tidak sesuai dengan transaksi yang direvisi.');
                }

                if ((int) $detail->barang_id !== (int) $item['barang_id']) {
                    throw new Exception('Barang pada detail penerimaan tidak valid.');
                }

                $oldQty = (float) $item['old_qty'];
                $newQty = isset($item['new_qty']) ? (float) $item['new_qty'] : $oldQty;
                $oldHargaBeli = (float) $detail->harga_beli;
                $oldHargaJual = (float) $detail->harga_jual;
                $newHargaBeli = isset($item['harga_beli']) ? (float) $item['harga_beli'] : $oldHargaBeli;
                $newHargaJual = isset($item['harga_jual']) ? (float) $item['harga_jual'] : $oldHargaJual;
                $ppnBase = $oldQty * $oldHargaBeli;
                $ppnPercent = $ppnBase > 0 ? ((float) $detail->ppn / $ppnBase) * 100 : 0;
                $oldSatuanId = $detail->barang?->idsatuan;
                $newSatuanId = !empty($item['idsatuan']) ? (int) $item['idsatuan'] : $oldSatuanId;

                if (($item['action'] ?? null) === 'delete') {
                    $stok = DB::table('stok_unit')
                        ->where('barang_id', $detail->barang_id)
                        ->where('unit_id', Auth::user()->unit_kerja)
                        ->lockForUpdate()
                        ->value('stok');

                    if ($stok < $oldQty) {
                        throw new Exception('Stok tidak cukup untuk menghapus item');
                    }

                    DB::statement("
                        UPDATE stok_unit
                        SET stok = stok - ?, updated_at = NOW()
                        WHERE barang_id = ? AND unit_id = ?
                    ", [$oldQty, $detail->barang_id, Auth::user()->unit_kerja]);

                    $detail->delete();

                    DB::table('revisi_penerimaan')->insert([
                        'penerimaan_id' => $penerimaanId,
                        'penerimaan_dtl_id' => $detail->id,
                        'barang_id' => $detail->barang_id,
                        'qty_lama' => $oldQty,
                        'qty_baru' => 0,
                        'selisih' => -$oldQty,
                        'keterangan' => 'Hapus item dari penerimaan',
                        'created_at' => now(),
                        'created_user' => auth()->id()
                    ]);

                    continue;
                }

                $selisih = $newQty - $oldQty;
                if ($selisih != 0.0) {
                    $stok = DB::table('stok_unit')
                        ->where('barang_id', $detail->barang_id)
                        ->where('unit_id', Auth::user()->unit_kerja)
                        ->lockForUpdate()
                        ->value('stok');

                    if ($stok + $selisih < 0) {
                        throw new Exception('Stok tidak cukup untuk revisi');
                    }

                    DB::statement("
                        INSERT INTO stok_unit (barang_id, unit_id, stok, updated_at, created_at)
                        VALUES (?, ?, ?, NOW(), NOW())
                        ON DUPLICATE KEY UPDATE
                            stok = stok + VALUES(stok),
                            updated_at = VALUES(updated_at)
                    ", [$detail->barang_id, Auth::user()->unit_kerja, $selisih]);
                }

                $newPpn = ($newQty * $newHargaBeli) * ($ppnPercent / 100);
                $detail->jumlah = $newQty;
                $detail->harga_beli = $newHargaBeli;
                $detail->harga_jual = $newHargaJual;
                $detail->ppn = $newPpn;
                $detail->subtotal = ($newQty * $newHargaBeli) + $newPpn;
                $detail->save();

                if ($detail->barang) {
                    $detail->barang->harga_beli = $newHargaBeli;
                    $detail->barang->harga_jual = $newHargaJual;
                    $detail->barang->idsatuan = $newSatuanId;
                    $detail->barang->save();
                }

                if (
                    $selisih != 0.0 ||
                    $newHargaBeli != $oldHargaBeli ||
                    $newHargaJual != $oldHargaJual ||
                    (int) $newSatuanId !== (int) $oldSatuanId
                ) {
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
            }

            $penerimaan->grandtotal = PenerimaanDtl::where('idpenerimaan', $penerimaanId)->sum('subtotal');
            $penerimaan->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Revisi penerimaan berhasil disimpan'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses revisi: ' . $e->getMessage()
            ], 500);
        }
    }

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
                    [$detail->jumlah, $detail->barang_id, Auth::user()->unit_kerja]
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

    public function nota($invoice): View
    {   
        $hdr = Penerimaan::join('users','users.id','penerimaan.user_id')
            ->select('penerimaan.*','users.name as petugas')
            ->where('penerimaan.nomor_invoice',$invoice)
            ->firstOrFail();

        $dtl = PenerimaanDtl::join('barang','barang.id','penerimaan_detail.barang_id')
            ->select(
                'barang.nama_barang',
                'barang.kode_barang',
                'penerimaan_detail.jumlah',
                'penerimaan_detail.harga_beli',
                'penerimaan_detail.harga_jual',
                'penerimaan_detail.ppn',
                'penerimaan_detail.subtotal'
            )
            ->where('idpenerimaan',$hdr->idpenerimaan)
            ->get();

        return view('transaksi.penerimaan-nota', [
            'hdr' => $hdr,
            'dtl' => $dtl,
        ]);
    }

    public function updateStatusBayar(Request $request, $id)
    {
        try {
            $penerimaan = Penerimaan::findOrFail($id);
            
            $penerimaan->status_bayar = $request->status_bayar;
            
            if ($request->status_bayar == 'paid') {
                $penerimaan->tgl_lunas = now();
            }
            
            $penerimaan->save();


            return response()->json([
                'success' => true,
                'message' => 'Status pembayaran berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        try {
            $penerimaan = Penerimaan::with(['details.barang', 'user', 'supplier'])->findOrFail($id);
            
            return view('transaksi.edit-penerimaan', [
                'penerimaan' => $penerimaan,
                'invoice' => $this->genCode(),
            ]);
        } catch (\Exception $e) {
            return redirect()->route('penerimaan.riwayat')
                ->with('error', 'Data penerimaan tidak ditemukan');
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $penerimaan = Penerimaan::findOrFail($id);
            
            $formattedDate = Carbon::parse($request->date)->format('Y-m-d H:i:s');
            
            if ($request->metode_bayar == 'tempo') {
                if (!$request->tgl_tempo) {
                    throw new Exception('Tanggal tempo harus diisi untuk pembayaran tempo.');
                }
                $tglTempo = Carbon::parse($request->tgl_tempo)->format('Y-m-d');
                $statusBayar = $request->status_bayar ?? 'pending';
            } else {
                $tglTempo = null;
                $statusBayar = 'paid';
            }

            // Update header dengan data supplier baru
            if ($request->supplier_id) {
                $supplier = Supplier::find($request->supplier_id);
                if ($supplier) {
                    $penerimaan->idsupplier = $supplier->id;
                    $penerimaan->kode_supplier = $supplier->kode_supplier;
                    $penerimaan->nama_supplier = $supplier->nama_supplier;
                }
            }

            $penerimaan->tgl_penerimaan = $formattedDate;
            $penerimaan->note = $request->note;
            $penerimaan->metode_bayar = $request->metode_bayar;
            $penerimaan->tgl_tempo = $tglTempo;
            $penerimaan->status_bayar = $statusBayar;
            $penerimaan->save();

            // TODO: Handle update details jika diperlukan
            
            DB::commit();
            

            return response()->json([
                'success' => true,
                'message' => 'Penerimaan berhasil diperbarui',
                'invoice' => $penerimaan->nomor_invoice
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getKategori()
    {
        $kategoris = Kategori::select('id', 'name')->get();
        return response()->json($kategoris);
    }

    public function storeBarang(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'kode_barang' => 'required|string|max:50|unique:barang,kode_barang',
                'nama_barang' => 'required|string|max:100',
                'harga_beli' => 'required|numeric|min:0',
                'harga_jual' => 'required|numeric|min:0',
            ]);

            // Cek apakah kode barang sudah ada
            $existingBarang = Barang::where('kode_barang', $request->kode_barang)->first();
            if ($existingBarang) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode barang sudah terdaftar'
                ], 400);
            }

            $barang = new Barang();
            $barang->kode_barang = $request->kode_barang;
            $barang->nama_barang = $request->nama_barang;
            $barang->type = $request->type ?? null;
            $barang->idkategori = $request->idkategori ?? null;
            $barang->idsatuan = $request->idsatuan ?? null;
            $barang->harga_beli = $request->harga_beli;
            $barang->harga_jual = $request->harga_jual;
            $barang->kelompok_unit = $request->kelompok_unit ?? 'toko';
            $barang->deskripsi = $request->deskripsi ?? null;
            $barang->save();

            // Tambahkan stok awal 0
            DB::statement("
                INSERT INTO stok_unit (barang_id, unit_id, stok, updated_at, created_at) 
                VALUES (?, ?, ?, NOW(), NOW())
                ON DUPLICATE KEY UPDATE 
                    updated_at = VALUES(updated_at)",
                [$barang->id, Auth::user()->unit_kerja, 0]
            );

            DB::commit();

            // Ambil nama satuan dan kategori
            $satuan = Satuan::find($request->idsatuan);
            $kategori = Kategori::find($request->idkategori);


            return response()->json([
                'success' => true,
                'barang' => [
                    'id' => $barang->id,
                    'code' => $barang->kode_barang,
                    'text' => $barang->nama_barang,
                    'nama_barang' => $barang->nama_barang,
                    'harga_beli' => $barang->harga_beli,
                    'harga_jual' => $barang->harga_jual,
                    'satuan' => $satuan ? $satuan->name : '',
                    'kategori' => $kategori ? $kategori->name : ''
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan barang: ' . $e->getMessage()
            ], 500);
        }
    }
}




