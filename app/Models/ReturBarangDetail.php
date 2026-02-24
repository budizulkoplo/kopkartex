<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturBarangDetail extends Model
{
    use HasFactory;
    
    protected $table = 'retur_detail';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'idretur',
        'barang_id',
        'qty',
        'harga_beli',
        'harga_jual',
        'subtotal'
    ];
    
    public function retur()
    {
        return $this->belongsTo(ReturBarang::class, 'idretur', 'id');
    }
    
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id', 'id');
    }
}