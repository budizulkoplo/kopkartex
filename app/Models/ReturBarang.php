<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReturBarang extends Model
{
    use SoftDeletes;
    protected $table = 'retur';
    protected $primaryKey = 'id';
}
