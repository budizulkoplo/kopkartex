<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KonfigBunga extends Model
{
    use HasFactory;

    protected $table = 'konfigurasi';
    protected $primaryKey = 'id';

    protected $casts = [
        'bunga_barang' => 'float',
        'bunga_pinjaman' => 'float',
        'control_limit' => 'integer',
    ];

    public static function activeConfig(): ?self
    {
        return static::query()->first();
    }

    public static function isDebtLimitControlEnabled(): bool
    {
        return (int) optional(static::activeConfig())->control_limit !== 0;
    }

    public static function resolveDebtLimit(object $user): ?float
    {
        if (!static::isDebtLimitControlEnabled()) {
            return null;
        }

        if (!empty($user->limit_hutang) && (float) $user->limit_hutang > 0) {
            return (float) $user->limit_hutang;
        }

        return 0.35 * (float) ($user->gaji ?? 0);
    }
}
