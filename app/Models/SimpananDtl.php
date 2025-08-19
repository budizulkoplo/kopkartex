<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SimpananDtl extends Model
{
    use SoftDeletes;

    protected $table = 'simpanan_dtl';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'idsimpanan', 'nominal', 'saldo_awal', 'saldo_ahir'
    ];

    public function header()
    {
        return $this->belongsTo(SimpananHdr::class, 'idsimpanan', 'idsimpanan');
    }
}
