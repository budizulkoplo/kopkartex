<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashBankDocumentCode extends Model
{
    use SoftDeletes;

    protected $table = 'cashbank_document_codes';

    protected $fillable = [
        'kode',
        'nama',
        'prefix',
        'keterangan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
