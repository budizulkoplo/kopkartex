<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('menus')) {
            return;
        }

        $barangMenu = DB::table('menus')->where('link', 'barang.list')->first();
        $parentId = $barangMenu->parent_id ?? null;
        $role = $barangMenu->role ?? ';superadmin;admin;';
        $seq = $barangMenu ? ((int) $barangMenu->seq + 1) : 5;

        DB::table('menus')->updateOrInsert(
            ['link' => 'kategori.cicilan.index'],
            [
                'name' => 'Kategori Cicilan',
                'parent_id' => $parentId,
                'role' => $role,
                'seq' => $seq,
                'icon' => 'bi bi-tags',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        if (! Schema::hasTable('menus')) {
            return;
        }

        DB::table('menus')->where('link', 'kategori.cicilan.index')->delete();
    }
};
