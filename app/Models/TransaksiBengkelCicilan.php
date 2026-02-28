<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiBengkelCicilan extends Model
{
    use HasFactory;

    protected $table = 'transaksi_bengkel_cicilan';

    protected $fillable = [
        'transaksi_bengkel_id',
        'cicilan',
        'anggota_id',
        'pokok',
        'bunga',
        'total_cicilan',
        'status',
        'kategori'
    ];

    public function transaksi()
    {
        return $this->belongsTo(TransaksiBengkel::class, 'transaksi_bengkel_id');
    }

    public function anggota()
    {
        return $this->belongsTo(User::class, 'anggota_id');
    }
}