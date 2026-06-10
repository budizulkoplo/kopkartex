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
        'bank_id',
        'coa_id',
        'keterangan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function coa()
    {
        return $this->belongsTo(CashBankCoa::class, 'coa_id');
    }

    public function bank()
    {
        return $this->belongsTo(CashBankBank::class, 'bank_id');
    }
}
