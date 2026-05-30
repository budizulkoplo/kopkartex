<?php

namespace App\Services;

use App\Models\KartuStok;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KartuStokBackfillService
{
    public function run(): array
    {
        $events = collect()
            ->merge($this->penerimaanEvents())
            ->merge($this->penjualanEvents())
            ->merge($this->returPembelianEvents())
            ->merge($this->transaksiBengkelEvents())
            ->merge($this->mutasiEvents())
            ->merge($this->stockAdjustmentEvents())
            ->merge($this->stockOpnameEvents())
            ->sortBy([
                ['tanggal', 'asc'],
                ['seq', 'asc'],
            ])
            ->values();

        $saldo = [];
        $inserted = 0;

        DB::transaction(function () use ($events, &$saldo, &$inserted) {
            foreach ($events as $event) {
                $key = $event['barang_id'] . ':' . $event['unit_id'];
                $saldoAwal = $saldo[$key] ?? 0.0;
                $qtyMasuk = 0.0;
                $qtyKeluar = 0.0;

                if ($event['arah'] === 'masuk') {
                    $qtyMasuk = (float) $event['qty'];
                    $saldoAkhir = $saldoAwal + $qtyMasuk;
                } elseif ($event['arah'] === 'keluar') {
                    $qtyKeluar = (float) $event['qty'];
                    $saldoAkhir = $saldoAwal - $qtyKeluar;
                } else {
                    $saldoAkhir = (float) $event['saldo_akhir'];
                    $selisih = $saldoAkhir - $saldoAwal;
                    $qtyMasuk = $selisih > 0 ? $selisih : 0;
                    $qtyKeluar = $selisih < 0 ? abs($selisih) : 0;
                }

                KartuStok::create([
                    'tanggal' => $event['tanggal'],
                    'barang_id' => $event['barang_id'],
                    'unit_id' => $event['unit_id'],
                    'jenis_transaksi' => $event['jenis_transaksi'],
                    'arah' => $event['arah'],
                    'qty_masuk' => $qtyMasuk,
                    'qty_keluar' => $qtyKeluar,
                    'saldo_awal' => $saldoAwal,
                    'saldo_akhir' => $saldoAkhir,
                    'harga_pokok' => $event['harga_pokok'] ?? null,
                    'nilai_mutasi' => isset($event['harga_pokok']) ? (($qtyMasuk + $qtyKeluar) * (float) $event['harga_pokok']) : null,
                    'nomor_referensi' => $event['nomor_referensi'] ?? null,
                    'referensi_tipe' => $event['referensi_tipe'],
                    'referensi_id' => $event['referensi_id'],
                    'referensi_detail_id' => $event['referensi_detail_id'] ?? null,
                    'unit_lawan_id' => $event['unit_lawan_id'] ?? null,
                    'batch_id' => $event['batch_id'] ?? (string) Str::uuid(),
                    'created_user' => $event['created_user'] ?? null,
                    'keterangan' => $event['keterangan'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $saldo[$key] = $saldoAkhir;
                $inserted++;
            }
        });

        return [
            'events' => $events->count(),
            'inserted' => $inserted,
        ];
    }

    private function penerimaanEvents(): Collection
    {
        return DB::table('penerimaan as p')
            ->join('penerimaan_detail as d', 'd.idpenerimaan', '=', 'p.idpenerimaan')
            ->leftJoin('users as u', 'u.id', '=', 'p.user_id')
            ->whereNull('p.deleted_at')
            ->whereNull('d.deleted_at')
            ->select([
                'p.idpenerimaan',
                'p.nomor_invoice',
                'p.tgl_penerimaan',
                'p.created_at',
                'p.user_id',
                'u.unit_kerja',
                'd.id as detail_id',
                'd.barang_id',
                'd.jumlah',
                'd.harga_beli',
            ])
            ->get()
            ->filter(fn ($row) => $row->barang_id && $row->unit_kerja)
            ->map(fn ($row) => [
                'seq' => 10,
                'tanggal' => $this->parseDate($row->tgl_penerimaan, $row->created_at),
                'barang_id' => (int) $row->barang_id,
                'unit_id' => (int) $row->unit_kerja,
                'jenis_transaksi' => 'penerimaan',
                'arah' => 'masuk',
                'qty' => (float) $row->jumlah,
                'harga_pokok' => (float) $row->harga_beli,
                'nomor_referensi' => $row->nomor_invoice,
                'referensi_tipe' => 'penerimaan',
                'referensi_id' => $row->idpenerimaan,
                'referensi_detail_id' => $row->detail_id,
                'created_user' => $row->user_id,
                'keterangan' => 'Backfill penerimaan',
            ]);
    }

    private function penjualanEvents(): Collection
    {
        return DB::table('penjualan as p')
            ->join('penjualan_detail as d', 'd.penjualan_id', '=', 'p.id')
            ->leftJoin('barang as b', 'b.id', '=', 'd.barang_id')
            ->whereNull('p.deleted_at')
            ->whereNull('d.deleted_at')
            ->where(function ($query) {
                $query->where('p.type_order', '!=', 'mobile')
                    ->orWhere('p.status_ambil', 'finish');
            })
            ->select([
                'p.id',
                'p.nomor_invoice',
                'p.tanggal',
                'p.unit_id',
                'p.created_user',
                'd.id as detail_id',
                'd.barang_id',
                'd.qty',
                'b.harga_beli',
            ])
            ->get()
            ->filter(fn ($row) => $row->barang_id && $row->unit_id)
            ->map(fn ($row) => [
                'seq' => 20,
                'tanggal' => $this->parseDate($row->tanggal),
                'barang_id' => (int) $row->barang_id,
                'unit_id' => (int) $row->unit_id,
                'jenis_transaksi' => 'penjualan',
                'arah' => 'keluar',
                'qty' => (float) $row->qty,
                'harga_pokok' => (float) $row->harga_beli,
                'nomor_referensi' => $row->nomor_invoice,
                'referensi_tipe' => 'penjualan',
                'referensi_id' => $row->id,
                'referensi_detail_id' => $row->detail_id,
                'created_user' => $row->created_user,
                'keterangan' => 'Backfill penjualan',
            ]);
    }

    private function returPembelianEvents(): Collection
    {
        return DB::table('retur as r')
            ->join('retur_detail as d', 'd.idretur', '=', 'r.id')
            ->whereNull('r.deleted_at')
            ->select([
                'r.id',
                'r.nomor_retur',
                'r.tgl_retur',
                'r.unit_id',
                'r.created_user',
                'd.id as detail_id',
                'd.barang_id',
                'd.qty',
                'd.harga_beli',
            ])
            ->get()
            ->filter(fn ($row) => $row->barang_id && $row->unit_id)
            ->map(fn ($row) => [
                'seq' => 30,
                'tanggal' => $this->parseDate($row->tgl_retur),
                'barang_id' => (int) $row->barang_id,
                'unit_id' => (int) $row->unit_id,
                'jenis_transaksi' => 'retur_pembelian',
                'arah' => 'keluar',
                'qty' => (float) $row->qty,
                'harga_pokok' => (float) $row->harga_beli,
                'nomor_referensi' => $row->nomor_retur,
                'referensi_tipe' => 'retur',
                'referensi_id' => $row->id,
                'referensi_detail_id' => $row->detail_id,
                'created_user' => $row->created_user,
                'keterangan' => 'Backfill retur pembelian',
            ]);
    }

    private function transaksiBengkelEvents(): Collection
    {
        return DB::table('transaksi_bengkels as t')
            ->join('transaksi_bengkel_details as d', 'd.transaksi_bengkel_id', '=', 't.id')
            ->leftJoin('barang as b', 'b.id', '=', 'd.barang_id')
            ->leftJoin('users as u', 'u.id', '=', 't.created_user')
            ->whereNull('t.deleted_at')
            ->whereNull('d.deleted_at')
            ->where('d.jenis', 'barang')
            ->select([
                't.id',
                't.nomor_invoice',
                't.tanggal',
                't.created_user',
                'u.unit_kerja',
                'd.id as detail_id',
                'd.barang_id',
                'd.qty',
                'b.harga_beli',
            ])
            ->get()
            ->filter(fn ($row) => $row->barang_id && $row->unit_kerja)
            ->map(fn ($row) => [
                'seq' => 35,
                'tanggal' => $this->parseDate($row->tanggal),
                'barang_id' => (int) $row->barang_id,
                'unit_id' => (int) $row->unit_kerja,
                'jenis_transaksi' => 'transaksi_bengkel',
                'arah' => 'keluar',
                'qty' => (float) $row->qty,
                'harga_pokok' => (float) $row->harga_beli,
                'nomor_referensi' => $row->nomor_invoice,
                'referensi_tipe' => 'transaksi_bengkels',
                'referensi_id' => $row->id,
                'referensi_detail_id' => $row->detail_id,
                'created_user' => $row->created_user,
                'keterangan' => 'Backfill transaksi bengkel',
            ]);
    }

    private function mutasiEvents(): Collection
    {
        return DB::table('mutasi_stok as m')
            ->join('mutasi_stok_detail as d', 'd.mutasi_id', '=', 'm.id')
            ->leftJoin('barang as b', 'b.id', '=', 'd.barang_id')
            ->whereNull('m.deleted_at')
            ->where(function ($query) {
                $query->whereNull('d.canceled')
                    ->orWhere('d.canceled', 0);
            })
            ->select([
                'm.id',
                'm.tanggal',
                'm.dari_unit',
                'm.ke_unit',
                'm.created_user',
                'd.id as detail_id',
                'd.barang_id',
                'd.qty',
                'b.harga_beli',
            ])
            ->get()
            ->flatMap(function ($row) {
                if (! $row->barang_id || ! $row->dari_unit || ! $row->ke_unit) {
                    return [];
                }

                $batchId = (string) Str::uuid();
                $common = [
                    'tanggal' => $this->parseDate($row->tanggal),
                    'barang_id' => (int) $row->barang_id,
                    'qty' => (float) $row->qty,
                    'harga_pokok' => (float) $row->harga_beli,
                    'nomor_referensi' => 'MUT-' . str_pad((string) $row->id, 6, '0', STR_PAD_LEFT),
                    'referensi_tipe' => 'mutasi_stok',
                    'referensi_id' => $row->id,
                    'referensi_detail_id' => $row->detail_id,
                    'batch_id' => $batchId,
                    'created_user' => $row->created_user,
                    'keterangan' => 'Backfill mutasi stok',
                ];

                return [
                    array_merge($common, [
                        'seq' => 40,
                        'unit_id' => (int) $row->dari_unit,
                        'unit_lawan_id' => (int) $row->ke_unit,
                        'jenis_transaksi' => 'mutasi_keluar',
                        'arah' => 'keluar',
                    ]),
                    array_merge($common, [
                        'seq' => 41,
                        'unit_id' => (int) $row->ke_unit,
                        'unit_lawan_id' => (int) $row->dari_unit,
                        'jenis_transaksi' => 'mutasi_masuk',
                        'arah' => 'masuk',
                    ]),
                ];
            });
    }

    private function stockAdjustmentEvents(): Collection
    {
        return DB::table('stock_adjustments as h')
            ->join('stock_adjustment_details as d', 'd.stock_adjustment_id', '=', 'h.id')
            ->leftJoin('barang as b', 'b.id', '=', 'd.barang_id')
            ->select([
                'h.id',
                'h.kode_adjustment',
                'h.tanggal_adjustment',
                'h.unit_id',
                'h.user_id',
                'd.id as detail_id',
                'd.barang_id',
                'd.new_stock',
                'b.harga_beli',
            ])
            ->get()
            ->filter(fn ($row) => $row->barang_id && $row->unit_id)
            ->map(fn ($row) => [
                'seq' => 50,
                'tanggal' => $this->parseDate($row->tanggal_adjustment),
                'barang_id' => (int) $row->barang_id,
                'unit_id' => (int) $row->unit_id,
                'jenis_transaksi' => 'stock_adjustment',
                'arah' => 'set',
                'saldo_akhir' => (float) $row->new_stock,
                'harga_pokok' => (float) $row->harga_beli,
                'nomor_referensi' => $row->kode_adjustment,
                'referensi_tipe' => 'stock_adjustments',
                'referensi_id' => $row->id,
                'referensi_detail_id' => $row->detail_id,
                'created_user' => $row->user_id,
                'keterangan' => 'Backfill stock adjustment',
            ]);
    }

    private function stockOpnameEvents(): Collection
    {
        return DB::table('stock_opname as so')
            ->leftJoin('barang as b', 'b.id', '=', 'so.id_barang')
            ->whereNull('so.deleted_at')
            ->select([
                'so.id',
                'so.tgl_opname',
                'so.id_unit',
                'so.id_barang',
                'so.stock_fisik',
                'so.user',
                'b.harga_beli',
            ])
            ->get()
            ->filter(fn ($row) => $row->id_barang && $row->id_unit)
            ->map(fn ($row) => [
                'seq' => 60,
                'tanggal' => $this->parseDate($row->tgl_opname),
                'barang_id' => (int) $row->id_barang,
                'unit_id' => (int) $row->id_unit,
                'jenis_transaksi' => 'stock_opname',
                'arah' => 'set',
                'saldo_akhir' => (float) $row->stock_fisik,
                'harga_pokok' => (float) $row->harga_beli,
                'nomor_referensi' => 'OPN-' . str_pad((string) $row->id, 6, '0', STR_PAD_LEFT),
                'referensi_tipe' => 'stock_opname',
                'referensi_id' => $row->id,
                'created_user' => $row->user,
                'keterangan' => 'Backfill stock opname',
            ]);
    }

    private function parseDate($value, $fallbackTime = null): Carbon
    {
        if (!$value) {
            return $fallbackTime ? Carbon::parse($fallbackTime) : now();
        }

        $date = Carbon::parse($value);

        if ($fallbackTime && $date->format('H:i:s') === '00:00:00') {
            $time = Carbon::parse($fallbackTime);
            $date->setTime((int) $time->format('H'), (int) $time->format('i'), (int) $time->format('s'));
        }

        return $date;
    }
}
