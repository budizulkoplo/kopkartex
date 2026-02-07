<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PenjualanCicil extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'penjualan_cicilan';
    protected $primaryKey = 'id';

    protected $fillable = [
        'anggota_id',
        'penjualan_id',
        'cicilan',
        'pokok',
        'bunga',
        'total_cicilan',
        'status',
        'kategori',
        'periode_tagihan',
        'status_bayar',
    ];

}
