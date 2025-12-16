<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockOpnameDTL extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'stock_opname_dtl';
    protected $primaryKey = 'id';
    public $incrementing = true;
    
    protected $fillable = [
        'opnameid',
        'id_barang',
        'qty',
        'expired_date',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    
    protected $casts = [
        'expired_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];
    
    // Relasi ke header
    public function header()
    {
        return $this->belongsTo(StockOpnameHDR::class, 'opnameid', 'id');
    }
    
    // Relasi ke barang
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id');
    }
}