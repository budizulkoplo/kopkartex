<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashBankTransactionDetail extends Model
{
    protected $table = 'cashbank_transaction_details';

    protected $fillable = [
        'transaction_id',
        'coa_id',
        'penerimaan_id',
        'nomor_invoice',
        'nilai_invoice',
        'sudah_dibayar',
        'jumlah_bayar',
        'sisa',
        'keterangan',
    ];

    protected $casts = [
        'nilai_invoice' => 'decimal:2',
        'sudah_dibayar' => 'decimal:2',
        'jumlah_bayar' => 'decimal:2',
        'sisa' => 'decimal:2',
    ];

    public function transaction()
    {
        return $this->belongsTo(CashBankTransaction::class, 'transaction_id');
    }

    public function coa()
    {
        return $this->belongsTo(CashBankCoa::class, 'coa_id');
    }

    public function penerimaan()
    {
        return $this->belongsTo(Penerimaan::class, 'penerimaan_id', 'idpenerimaan');
    }
}
