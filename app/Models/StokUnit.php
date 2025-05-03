<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StokUnit extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'stok_unit';
    protected $primaryKey = 'id';
}
