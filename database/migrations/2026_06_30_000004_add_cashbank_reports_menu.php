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

        $role = ';superadmin;admin;cashbank;keuangan;bendahara;';

        DB::table('menus')->updateOrInsert(
            ['link' => '#cashbank-laporan'],
            [
                'name' => 'Laporan Cashbank',
                'parent_id' => null,
                'role' => $role,
                'seq' => 32,
                'icon' => 'bi bi-clipboard-data',
                'updated_at' => now(),
                'created_at' => now(),
                'deleted_at' => null,
            ]
        );

        $parentId = DB::table('menus')->where('link', '#cashbank-laporan')->value('id');

        foreach ([
            ['Laporan Ledger', 'laporan.cashbank.ledger', 1, 'bi bi-journal-text'],
            ['Summary Bank Detail', 'laporan.cashbank.summary-bank-detail', 2, 'bi bi-bank'],
        ] as [$name, $link, $seq, $icon]) {
            DB::table('menus')->updateOrInsert(
                ['link' => $link],
                [
                    'name' => $name,
                    'parent_id' => $parentId,
                    'role' => $role,
                    'seq' => $seq,
                    'icon' => $icon,
                    'updated_at' => now(),
                    'created_at' => now(),
                    'deleted_at' => null,
                ]
            );
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('menus')) {
            return;
        }

        DB::table('menus')
            ->whereIn('link', [
                '#cashbank-laporan',
                'laporan.cashbank.ledger',
                'laporan.cashbank.summary-bank-detail',
            ])
            ->delete();
    }
};
