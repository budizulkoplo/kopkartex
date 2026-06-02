<?php

namespace App\Services;

use App\Models\Barang;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class BarangNonMovingService
{
    private array $transactionSources = [
        ['table' => 'kartu_stok', 'column' => 'barang_id', 'ignore_old_stock_opname' => true],
        ['table' => 'stock_opname', 'column' => 'id_barang', 'deleted_at' => true, 'current_stock_opname_only' => true],
        ['table' => 'transaksi_bengkel_details', 'column' => 'barang_id', 'deleted_at' => true],
        ['table' => 'penjualan_detail', 'column' => 'barang_id', 'deleted_at' => true],
        ['table' => 'penerimaan_detail', 'column' => 'barang_id', 'deleted_at' => true],
        ['table' => 'retur_detail', 'column' => 'barang_id', 'deleted_at' => true],
        ['table' => 'mutasi_stok_detail', 'column' => 'barang_id', 'deleted_at' => true],
        ['table' => 'stock_adjustment_details', 'column' => 'barang_id'],
    ];

    public function restoreIfNonMoving(int $barangId): bool
    {
        $barang = Barang::query()
            ->whereKey($barangId)
            ->where('is_non_moving', true)
            ->first();

        if (! $barang) {
            return false;
        }

        $barang->is_non_moving = false;
        $barang->non_moving_at = null;
        $barang->non_moving_by = null;
        $barang->save();

        return true;
    }

    public function restoreByCode(string $kodeBarang, ?string $kelompokUnit = null): bool
    {
        $query = Barang::query()
            ->where('kode_barang', $kodeBarang)
            ->where('is_non_moving', true);

        if ($kelompokUnit !== null) {
            $query->where('kelompok_unit', $kelompokUnit);
        }

        $barang = $query->first();

        if (! $barang) {
            return false;
        }

        return $this->restoreIfNonMoving($barang->id);
    }

    public function restoreBengkelWithAnyTransaction(): int
    {
        $query = Barang::query()
            ->where('kelompok_unit', 'bengkel')
            ->where('is_non_moving', true);

        $this->whereHasAnyTransaction($query, 'barang.id');

        return $query->update([
            'is_non_moving' => false,
            'non_moving_at' => null,
            'non_moving_by' => null,
            'updated_at' => now(),
        ]);
    }

    public function whereHasNoTransaction($query, string $barangColumn = 'barang.id')
    {
        foreach ($this->transactionSources as $source) {
            $query->whereNotExists(function (Builder $subQuery) use ($source, $barangColumn) {
                $this->buildTransactionExistsSubQuery($subQuery, $source, $barangColumn);
            });
        }

        return $query;
    }

    public function whereHasAnyTransaction($query, string $barangColumn = 'barang.id')
    {
        return $query->where(function ($builder) use ($barangColumn) {
            foreach ($this->transactionSources as $source) {
                $builder->orWhereExists(function (Builder $subQuery) use ($source, $barangColumn) {
                    $this->buildTransactionExistsSubQuery($subQuery, $source, $barangColumn);
                });
            }
        });
    }

    private function buildTransactionExistsSubQuery(Builder $query, array $source, string $barangColumn): void
    {
        $query->select(DB::raw(1))
            ->from($source['table'])
            ->whereColumn($source['table'] . '.' . $source['column'], $barangColumn);

        if ($source['deleted_at'] ?? false) {
            $query->whereNull($source['table'] . '.deleted_at');
        }

        if ($source['ignore_old_stock_opname'] ?? false) {
            $monthStart = now()->startOfMonth()->toDateString();

            $query->where(function (Builder $builder) use ($source, $monthStart) {
                $builder->where($source['table'] . '.jenis_transaksi', '<>', 'stock_opname')
                    ->orWhere($source['table'] . '.tanggal', '>=', $monthStart);
            });
        }

        if ($source['current_stock_opname_only'] ?? false) {
            $query->where($source['table'] . '.tgl_opname', '>=', now()->startOfMonth()->toDateString())
                ->where($source['table'] . '.status', '<>', 'pending');
        }
    }
}
