<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pinbrg extends Model
{
    protected $table = 'pinbrg';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'period',
        'unit_usaha',
        'lokasi',
        'NO_AGT',        
        'NOPIN',          
        'NO_PIN',         
        'TG_PIN',         
        'TOTAL_HARGA',    
        'JUM_PIN',        
        'SISA_PIN',       
        'ANGS_X',         
        'ANGSUR1',        
        'ANGSUR2',        
        'JENIS',         
        'ANGS_KE',        
        'UNIT',          
        'STATUS',         
        'NO_BADGE',      
        'KEL',            
        'jenis_penjualan' 
    ];

    protected $casts = [
        'TG_PIN' => 'date',
        'TOTAL_HARGA' => 'decimal:2',
        'JUM_PIN' => 'decimal:2',
        'SISA_PIN' => 'decimal:2',
        'ANGS_X' => 'decimal:2',
        'ANGSUR1' => 'decimal:2',
        'ANGSUR2' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}