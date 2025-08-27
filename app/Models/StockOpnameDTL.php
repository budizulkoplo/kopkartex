<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockOpnameDTL extends Model
{
    protected $table = 'stock_opname_dtl';

    protected $fillable = [
        'opnameid',
        'id_barang',
        'qty',
        'expired_date',
    ];
}