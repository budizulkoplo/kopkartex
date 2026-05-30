<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KartuStokSaldoBulanan extends Model
{
    protected $table = 'kartu_stok_saldo_bulanan';

    protected $fillable = [
        'periode',
        'barang_id',
        'unit_id',
        'saldo_awal',
        'total_masuk',
        'total_keluar',
        'saldo_akhir',
        'generated_at',
        'generated_by',
    ];

    protected $casts = [
        'saldo_awal' => 'decimal:3',
        'total_masuk' => 'decimal:3',
        'total_keluar' => 'decimal:3',
        'saldo_akhir' => 'decimal:3',
        'generated_at' => 'datetime',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id', 'id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by', 'id');
    }
}
