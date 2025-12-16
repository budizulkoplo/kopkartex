<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenerimaanDtl extends Model
{
    protected $table = 'penerimaan_detail';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'idpenerimaan',
        'barang_id',
        'jumlah',
        'harga_beli',
        'harga_jual',
        'subtotal',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    
    public function penerimaan()
    {
        return $this->belongsTo(Penerimaan::class, 'idpenerimaan', 'idpenerimaan');
    }
    
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id', 'id');
    }
}