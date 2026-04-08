<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Penerimaan extends Model
{
    use SoftDeletes;
    
    protected $table = 'penerimaan';
    protected $primaryKey = 'idpenerimaan'; 
    public $incrementing = true; 
    
    protected $fillable = [
        'idpenerimaan', 
        'nomor_invoice',
        'tgl_penerimaan',
        'idsupplier',
        'kode_supplier',
        'nama_supplier',
        'note',
        'user_id',
        'metode_bayar',
        'tgl_tempo',
        'status_bayar',
        'grandtotal',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    
    protected $casts = [
        'tgl_penerimaan' => 'datetime',
        'tgl_tempo' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'grandtotal' => 'decimal:2'
    ];
    
    public function details()
    {
        return $this->hasMany(PenerimaanDtl::class, 'idpenerimaan', 'idpenerimaan');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'idsupplier', 'id');
    }

    public function getEffectiveGrandtotalAttribute(): float
    {
        if ($this->relationLoaded('details')) {
            return round(
                $this->details->sum(fn ($detail) => (float) $detail->subtotal),
                2
            );
        }

        return round((float) $this->grandtotal, 2);
    }
}
