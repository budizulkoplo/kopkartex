<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransaksiBengkel extends Model
{
    use SoftDeletes;

    protected $table = 'transaksi_bengkels'; // nama tabel sesuai SQL yang tadi
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
    ];
}
