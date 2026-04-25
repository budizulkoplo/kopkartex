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
    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'status_produk',
        'kategori',
        'satuan',
        'type',
        'harga_beli',
        'harga_jual',
        'harga_jual_umum',
        'idkategori',
        'idsatuan',
        'kelompok_unit',
        'img',
    ];

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

    public function scopeAktif($query)
    {
        return $query->where(function ($builder) {
            $builder->whereNull('status_produk')
                ->orWhere('status_produk', 'aktif');
        });
    }

    public function kategoriRelation()
    {
        return $this->belongsTo(Kategori::class, 'idkategori', 'id');
    }

    public function satuanRelation()
    {
        return $this->belongsTo(Satuan::class, 'idsatuan', 'id');
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'idkategori');
    }

    public function stok()
    {
        return $this->hasOne(StokUnit::class, 'barang_id', 'id');
    }
    
}
