<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransaksiBengkelDetail extends Model
{
    use SoftDeletes;

    protected $table = 'transaksi_bengkel_details';
    
    protected $fillable = [
        'transaksi_bengkel_id',
        'jenis',
        'jasa_id',
        'barang_id',
        'qty',
        'harga',
        'total'
    ];

    protected $casts = [
        'harga' => 'float',
        'total' => 'float',
        'qty' => 'integer'
    ];

    public function transaksi()
    {
        return $this->belongsTo(TransaksiBengkel::class, 'transaksi_bengkel_id');
    }

    public function jasa()
    {
        return $this->belongsTo(JasaBengkel::class, 'jasa_id');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }
}