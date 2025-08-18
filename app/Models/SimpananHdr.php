<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SimpananHdr extends Model
{
    use SoftDeletes;

    protected $table = 'simpanan_hdr';
    protected $primaryKey = 'idsimpanan';   // <- kasih tahu pk
    public $incrementing = true;            // pk auto increment
    protected $keyType = 'int';

    protected $fillable = [
        'id_anggota', 'norek', 'nama_pemilik', 'jenis_simpanan', 'saldo'
    ];

    public function anggota()
    {
        return $this->belongsTo(User::class, 'id_anggota', 'id');
    }

    public function details()
    {
        return $this->hasMany(SimpananDtl::class, 'idsimpanan', 'idsimpanan');
    }
}
