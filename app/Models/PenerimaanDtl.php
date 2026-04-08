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
        'ppn',
        'subtotal',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'jumlah' => 'decimal:3',
        'harga_beli' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'ppn' => 'decimal:2',
        'subtotal' => 'decimal:2',
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
