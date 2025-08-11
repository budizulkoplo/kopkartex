<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiBengkelDetail extends Model
{
    protected $table = 'transaksi_bengkel_details';
    protected $fillable = [
        'transaksi_bengkel_id',
        'jenis',
        'jasa_id',
        'barang_id',
        'qty',
        'harga',
        'total',
    ];

    public function transaksi()
    {
        return $this->belongsTo(TransaksiBengkel::class, 'transaksi_bengkel_id');
    }
}
