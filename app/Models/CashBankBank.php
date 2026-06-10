<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashBankBank extends Model
{
    use SoftDeletes;

    protected $table = 'cashbank_banks';

    protected $fillable = [
        'kode_bank',
        'nama_bank',
        'nomor_rekening',
        'nama_rekening',
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
}
