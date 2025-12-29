<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Barang extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'barang';
    protected $primaryKey = 'id';

    // Scope untuk filter kelompok unit
    public function scopeKelompok($query, $kelompok)
    {
        return $query->where('kelompok_unit', $kelompok);
    }
    
    // Scope untuk bengkel
    public function scopeBengkel($query)
    {
        return $query->where('kelompok_unit', 'bengkel');
    }
    
    // Scope untuk toko
    public function scopeToko($query)
    {
        return $query->where('kelompok_unit', 'toko');
    }
    
    // Scope untuk air
    public function scopeAir($query)
    {
        return $query->where('kelompok_unit', 'air');
    }

    public function kategoriRelation()
    {
        return $this->belongsTo(Kategori::class, 'idkategori', 'id');
    }

    public function satuanRelation()
    {
        return $this->belongsTo(Satuan::class, 'idsatuan', 'id');
    }
}
