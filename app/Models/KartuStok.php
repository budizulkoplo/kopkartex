<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KartuStok extends Model
{
    protected $table = 'kartu_stok';

    protected $fillable = [
        'tanggal',
        'barang_id',
        'unit_id',
        'jenis_transaksi',
        'arah',
        'qty_masuk',
        'qty_keluar',
        'saldo_awal',
        'saldo_akhir',
        'harga_pokok',
        'nilai_mutasi',
        'nomor_referensi',
        'referensi_tipe',
        'referensi_id',
        'referensi_detail_id',
        'unit_lawan_id',
        'batch_id',
        'dibalik_dari_id',
        'created_user',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'qty_masuk' => 'decimal:3',
        'qty_keluar' => 'decimal:3',
        'saldo_awal' => 'decimal:3',
        'saldo_akhir' => 'decimal:3',
        'harga_pokok' => 'decimal:2',
        'nilai_mutasi' => 'decimal:2',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id', 'id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }

    public function unitLawan()
    {
        return $this->belongsTo(Unit::class, 'unit_lawan_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_user', 'id');
    }

    public function dibalikDari()
    {
        return $this->belongsTo(self::class, 'dibalik_dari_id', 'id');
    }
}
