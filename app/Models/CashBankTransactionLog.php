<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashBankTransactionLog extends Model
{
    protected $table = 'cashbank_transaction_logs';

    protected $fillable = [
        'transaction_id',
        'aksi',
        'keterangan',
        'payload',
        'created_user',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function transaction()
    {
        return $this->belongsTo(CashBankTransaction::class, 'transaction_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_user');
    }
}
