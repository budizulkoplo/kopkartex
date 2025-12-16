<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockOpnameHDR extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'stock_opname';
    protected $primaryKey = 'id';
    public $incrementing = true;
    
    protected $fillable = [
        'id_unit',
        'id_barang',
        'kode_barang',
        'tgl_opname',
        'user',
        'stock_sistem',
        'stock_fisik',
        'keterangan',
        'status',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    
    protected $casts = [
        'tgl_opname' => 'date',
        'stock_sistem' => 'integer',
        'stock_fisik' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];
    
    // Relasi ke detail
    public function details()
    {
        return $this->hasMany(StockOpnameDTL::class, 'opnameid', 'id');
    }
    
    // Relasi ke barang
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id');
    }
    
    // Relasi ke unit
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'id_unit', 'id');
    }
}