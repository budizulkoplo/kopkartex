<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PenjualanDetail extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'penjualan_detail';
    protected $primaryKey = 'id';
}
