<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustmentDetail extends Model
{
    protected $table = 'stock_adjustment_details';

    protected $fillable = [
        'stock_adjustment_id',
        'barang_id',
        'adjustment_type',
        'old_stock',
        'adjustment_value',
        'new_stock',
        'old_satuan_id',
        'new_satuan_id',
        'conversion_factor',
        'note',
    ];

    protected $casts = [
        'old_stock' => 'integer',
        'adjustment_value' => 'integer',
        'new_stock' => 'integer',
        'conversion_factor' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function adjustment()
    {
        return $this->belongsTo(StockAdjustment::class, 'stock_adjustment_id');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function oldSatuan()
    {
        return $this->belongsTo(Satuan::class, 'old_satuan_id');
    }

    public function newSatuan()
    {
        return $this->belongsTo(Satuan::class, 'new_satuan_id');
    }
}
