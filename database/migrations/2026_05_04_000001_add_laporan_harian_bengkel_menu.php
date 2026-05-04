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

        $parentId = DB::table('menus')
            ->where('name', 'Laporan')
            ->whereNull('deleted_at')
            ->value('id');

        if (! $parentId) {
            return;
        }

        DB::table('menus')->updateOrInsert(
            ['link' => 'laporan.harian_bengkel'],
            [
                'name' => 'Laporan Harian Bengkel',
                'parent_id' => $parentId,
                'role' => ';superadmin;admin;it;kasir;gudang;bendahara;bengkel;keuangan;',
                'seq' => 3,
                'icon' => 'fa-solid fa-calendar-day',
                'updated_at' => now(),
                'created_at' => now(),
                'deleted_at' => null,
            ]
        );
    }

    public function down(): void
    {
        if (! Schema::hasTable('menus')) {
            return;
        }

        DB::table('menus')
            ->where('link', 'laporan.harian_bengkel')
            ->delete();
    }
};
