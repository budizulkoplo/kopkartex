<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModalAwal extends Model
{
    protected $table = 'modal_awal';
    
    protected $fillable = [
        'periode',
        'barang_id',
        'kode_barang',
        'nama_barang',
        'harga_modal',
        'unit_id',
        'stok',
        'nilai_total_barang'
    ];

    protected $casts = [
        'harga_modal' => 'decimal:2',
        'nilai_total_barang' => 'decimal:2',
        'stok' => 'integer'
    ];

    /**
     * Relasi ke barang
     */
    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    /**
     * Relasi ke unit
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * Scope untuk filter periode
     */
    public function scopePeriode($query, $periode)
    {
        return $query->where('periode', $periode);
    }

    /**
     * Scope untuk filter unit
     */
    public function scopeUnit($query, $unitId)
    {
        return $query->where('unit_id', $unitId);
    }
}