<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashBankCoa extends Model
{
    use SoftDeletes;

    protected $table = 'cashbank_coas';

    protected $fillable = [
        'kode_akun',
        'nama_akun',
        'tipe',
        'att1',
        'att2',
        'att3',
        'att4',
        'att5',
        'keterangan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
