<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PenerimaanDtl extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'penerimaan_detail';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $dates = [
        'expired_date',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id', 'id');
    }
}

