<?php
// app/Models/Pinbrg.php

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
        'NO_AGT',        // Sesuai query: NO_AGT
        'NOPIN',          // Sesuai query: NOPIN
        'NO_PIN',         // Sesuai query: NO_PIN
        'TG_PIN',         // Sesuai query: TG_PIN
        'TOTAL_HARGA',    // Sesuai query: TOTAL_HARGA
        'JUM_PIN',        // Sesuai query: JUM_PIN
        'SISA_PIN',       // Sesuai query: SISA_PIN
        'ANGS_X',         // Sesuai query: ANGS_X
        'ANGSUR1',        // Sesuai query: ANGSUR1
        'ANGSUR2',        // Sesuai query: ANGSUR2
        'JENIS',          // Sesuai query: JENIS
        'ANGS_KE',        // Sesuai query: ANGS_KE
        'UNIT',           // Sesuai query: UNIT
        'STATUS',         // Sesuai query: STATUS
        'NO_BADGE',       // Sesuai query: NO_BADGE
        'KEL',            // Sesuai query: KEL
        'jenis_penjualan' // Sesuai query: jenis_penjualan
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