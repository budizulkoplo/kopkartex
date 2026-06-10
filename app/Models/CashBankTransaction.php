<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashBankTransaction extends Model
{
    use SoftDeletes;

    protected $table = 'cashbank_transactions';

    protected $fillable = [
        'nomor_transaksi',
        'jenis',
        'unit_id',
        'document_code_id',
        'coa_id',
        'bank_id',
        'tgl_transaksi',
        'periode',
        'supplier_id',
        'dibayar_kepada',
        'guna_membayar',
        'no_ref_nota',
        'sejumlah',
        'dibayar_dengan',
        'no_cash_cek_giro',
        'tgl_giro_cek',
        'status',
        'created_user',
    ];

    protected $casts = [
        'tgl_transaksi' => 'date',
        'tgl_giro_cek' => 'date',
        'sejumlah' => 'decimal:2',
    ];

    public function details()
    {
        return $this->hasMany(CashBankTransactionDetail::class, 'transaction_id');
    }

    public function logs()
    {
        return $this->hasMany(CashBankTransactionLog::class, 'transaction_id')->latest();
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function documentCode()
    {
        return $this->belongsTo(CashBankDocumentCode::class, 'document_code_id');
    }

    public function coa()
    {
        return $this->belongsTo(CashBankCoa::class, 'coa_id');
    }

    public function bank()
    {
        return $this->belongsTo(CashBankBank::class, 'bank_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_user');
    }
}
