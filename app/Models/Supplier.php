<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $table = 'suppliers';

    protected $fillable = [
        'kode_supplier',
        'nama_supplier',
        'npwp',
        'alamat',
        'telp',
        'kontak_person',
        'email',
        'rekening',
        'bank',
    ];
}
