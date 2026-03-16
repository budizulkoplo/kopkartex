<?php

namespace App\Models;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use SoftDeletes;
    protected $table = 'menus';
    protected $fillable = [
        'name',
        'link',
        'parent_id',
        'role',
    ];

    public function getRoleNamesAttribute(): Collection
    {
        return collect(explode(';', (string) $this->role))
            ->map(fn ($role) => trim($role))
            ->filter()
            ->values();
    }

    public function hasRoleAccess(array $roles): bool
    {
        return $this->role_names->intersect($roles)->isNotEmpty();
    }

    public function syncRoleNames(array $roles): void
    {
        $normalized = collect($roles)
            ->map(fn ($role) => trim((string) $role))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $this->role = empty($normalized) ? '' : ';' . implode(';', $normalized) . ';';
    }
}
