<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Penjualan extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'penjualan';
    protected $primaryKey = 'id';

    public function user()
    {
        return $this->belongsTo(User::class, 'created_user', 'id');
    }
    
}
