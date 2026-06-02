<?php

namespace App\Services;

use App\Models\KartuStok;
use App\Models\StokUnit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use RuntimeException;

class KartuStokService
{
    public function masuk(array $data): KartuStok
    {
        return $this->catat(array_merge($data, [
            'arah' => 'masuk',
            'qty' => abs((float) ($data['qty'] ?? 0)),
        ]));
    }

    public function keluar(array $data): KartuStok
    {
        return $this->catat(array_merge($data, [
            'arah' => 'keluar',
            'qty' => abs((float) ($data['qty'] ?? 0)),
        ]));
    }

    public function setSaldo(array $data): KartuStok
    {
        return $this->catat(array_merge($data, [
            'arah' => 'set',
        ]));
    }

    public function catat(array $data): KartuStok
    {
        $barangId = (int) ($data['barang_id'] ?? 0);
        $unitId = (int) ($data['unit_id'] ?? 0);
        $arah = (string) ($data['arah'] ?? '');

        if (! $barangId || ! $unitId) {
            throw new RuntimeException('Barang dan unit wajib diisi untuk kartu stok.');
        }

        app(BarangNonMovingService::class)->restoreIfNonMoving($barangId);

        $stokUnit = StokUnit::withTrashed()
            ->where('barang_id', $barangId)
            ->where('unit_id', $unitId)
            ->lockForUpdate()
            ->first();

        if (! $stokUnit) {
            $stokUnit = StokUnit::create([
                'barang_id' => $barangId,
                'unit_id' => $unitId,
                'stok' => 0,
            ]);
        } elseif ($stokUnit->trashed()) {
            $stokUnit->restore();
        }

        $saldoAwal = (float) $stokUnit->stok;
        $qty = abs((float) ($data['qty'] ?? 0));
        $qtyMasuk = 0.0;
        $qtyKeluar = 0.0;

        if ($arah === 'masuk') {
            $qtyMasuk = $qty;
            $saldoAkhir = $saldoAwal + $qty;
        } elseif ($arah === 'keluar') {
            $qtyKeluar = $qty;
            $saldoAkhir = $saldoAwal - $qty;

            if (($data['allow_minus'] ?? false) !== true && $saldoAkhir < 0) {
                throw new RuntimeException('Stok tidak cukup untuk transaksi kartu stok.');
            }
        } elseif ($arah === 'set') {
            $saldoAkhir = (float) ($data['saldo_akhir'] ?? $data['new_stock'] ?? 0);
            $selisih = $saldoAkhir - $saldoAwal;

            if ($selisih >= 0) {
                $qtyMasuk = $selisih;
            } else {
                $qtyKeluar = abs($selisih);
            }
        } else {
            throw new RuntimeException('Arah kartu stok tidak valid.');
        }

        $stokUnit->stok = $saldoAkhir;
        $stokUnit->save();

        $hargaPokok = $data['harga_pokok'] ?? null;
        $nilaiMutasi = $data['nilai_mutasi'] ?? null;

        if ($nilaiMutasi === null && $hargaPokok !== null) {
            $nilaiMutasi = ($qtyMasuk + $qtyKeluar) * (float) $hargaPokok;
        }

        return KartuStok::create([
            'tanggal' => $data['tanggal'] ?? now(),
            'barang_id' => $barangId,
            'unit_id' => $unitId,
            'jenis_transaksi' => $data['jenis_transaksi'] ?? 'manual',
            'arah' => $arah,
            'qty_masuk' => $qtyMasuk,
            'qty_keluar' => $qtyKeluar,
            'saldo_awal' => $saldoAwal,
            'saldo_akhir' => $saldoAkhir,
            'harga_pokok' => $hargaPokok,
            'nilai_mutasi' => $nilaiMutasi,
            'nomor_referensi' => $data['nomor_referensi'] ?? null,
            'referensi_tipe' => $data['referensi_tipe'] ?? null,
            'referensi_id' => $data['referensi_id'] ?? null,
            'referensi_detail_id' => $data['referensi_detail_id'] ?? null,
            'unit_lawan_id' => $data['unit_lawan_id'] ?? null,
            'batch_id' => $data['batch_id'] ?? (string) Str::uuid(),
            'dibalik_dari_id' => $data['dibalik_dari_id'] ?? null,
            'created_user' => $data['created_user'] ?? Auth::id(),
            'keterangan' => $data['keterangan'] ?? null,
        ]);
    }
}
