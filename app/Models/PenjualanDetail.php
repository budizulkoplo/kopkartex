<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PenjualanDetail extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'penjualan_detail';
    protected $primaryKey = 'id';

    protected $fillable = [
        'penjualan_id',
        'barang_id',
        'qty',
        'harga',
    ];

    protected $casts = [
        'qty' => 'decimal:3',
        'harga' => 'decimal:2',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id', 'id');
    }
}
