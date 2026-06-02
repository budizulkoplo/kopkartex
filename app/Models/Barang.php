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
        'is_non_moving',
        'non_moving_at',
        'non_moving_by',
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

    protected $casts = [
        'is_non_moving' => 'boolean',
        'non_moving_at' => 'datetime',
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

    public function scopeNormalMoving($query)
    {
        return $query->where(function ($builder) {
            $builder->whereNull('is_non_moving')
                ->orWhere('is_non_moving', false);
        });
    }

    public function scopeNonMoving($query)
    {
        return $query->where('is_non_moving', true);
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
