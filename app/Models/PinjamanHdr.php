<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PinjamanHdr extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pinjaman_hdr'; // pastikan sesuai nama tabel

    protected $primaryKey = 'id_pinjaman';
    public $incrementing = false; // karena id_pinjaman varchar/uuid
    protected $keyType = 'string';

    protected $fillable = [
        'id_pinjaman',
        'tgl_pengajuan',
        'nomor_anggota',
        'gaji',
        'nominal_pengajuan',
        'tenor',
        'jaminan',
        'status',
        'tgl_approve',
    ];

    protected $dates = ['tgl_pengajuan','tgl_approve','created_at','updated_at','deleted_at'];
}
