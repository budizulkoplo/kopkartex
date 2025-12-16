<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StokUnit extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'stok_unit';
    
    protected $fillable = [
        'barang_id',     // <-- TAMBAHKAN INI
        'unit_id',
        'stok',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    
    protected $casts = [
        'stok' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];
    
    // Relasi ke barang
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id', 'id');
    }
    
    // Relasi ke unit
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }
}