<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReturBarang extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'retur';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'nomor_retur',
        'idsupplier',
        'kode_supplier',
        'nama_supplier',
        'tgl_retur',
        'grandtotal',
        'note',
        'unit_id',
        'created_user'
    ];
    
    protected $dates = [
        'tgl_retur',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    
    public function details()
    {
        return $this->hasMany(ReturBarangDetail::class, 'idretur', 'id');
    }
    
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'idsupplier', 'id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'created_user', 'id');
    }
    
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }
}