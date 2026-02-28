<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class TransaksiBengkel extends Model
{
    use SoftDeletes;

    protected $table = 'transaksi_bengkels';
    
    protected $fillable = [
        'nomor_invoice',
        'tanggal',
        'subtotal',
        'diskon',
        'grandtotal',
        'metode_bayar',
        'customer',
        'anggota_id',
        'dibayar',
        'kembali',
        'created_user',
        'status',
        'tenor',
        'bunga_barang',
        'note'
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public function details()
    {
        return $this->hasMany(TransaksiBengkelDetail::class, 'transaksi_bengkel_id');
    }

    // Hapus relasi unit() karena tidak ada kolom unit_id
    // public function unit()
    // {
    //     return $this->belongsTo(Unit::class, 'unit_id');
    // }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_user');
    }

    public function anggota()
    {
        return $this->belongsTo(User::class, 'anggota_id');
    }

    public function cicilan()
    {
        return $this->hasMany(TransaksiBengkelCicilan::class, 'transaksi_bengkel_id');
    }
}