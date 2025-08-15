<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JasaBengkel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'jasa_bengkel';

    protected $fillable = [
        'kode_jasa',
        'nama_jasa',
        'deskripsi',
        'harga',
        'satuan',
        'status',
    ];
}
