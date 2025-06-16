<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReturBarangDetail extends Model
{
    use SoftDeletes;
    protected $table = 'retur_detail';
    protected $primaryKey = 'id';
}
