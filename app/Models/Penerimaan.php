<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Penerimaan extends Model
{
    protected $table = 'penerimaan';

    protected $primaryKey = 'idpenerimaan';

    protected $fillable = [
        'nomor_invoice', 'tgl_penerimaan', 'nama_supplier', 'note', 'user_id'
    ];

    protected $dates = ['tgl_penerimaan', 'created_at', 'updated_at', 'deleted_at'];

    // atau Laravel 8+ gunakan $casts
    protected $casts = [
        'tgl_penerimaan' => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(PenerimaanDtl::class, 'idpenerimaan');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}


