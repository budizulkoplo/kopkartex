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

        DB::table('menus')->where('link', 'cashbank.transactions.umum.index')->update([
            'name' => 'Cash Bank Pembayaran Umum',
            'updated_at' => now(),
        ]);

        DB::table('menus')->where('link', 'cashbank.transactions.hutang.index')->update([
            'name' => 'Cashbank Pembayaran Supplier',
            'updated_at' => now(),
        ]);

        DB::table('menus')->where('link', 'cashbank.transactions.hutang.history')->update([
            'name' => 'Riwayat Pembayaran Supplier',
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('menus')) {
            return;
        }

        DB::table('menus')->where('link', 'cashbank.transactions.umum.index')->update([
            'name' => 'Cash Bank Umum',
            'updated_at' => now(),
        ]);

        DB::table('menus')->where('link', 'cashbank.transactions.hutang.index')->update([
            'name' => 'Cash Bank Pembayaran Hutang',
            'updated_at' => now(),
        ]);

        DB::table('menus')->where('link', 'cashbank.transactions.hutang.history')->update([
            'name' => 'Riwayat Pembayaran Hutang',
            'updated_at' => now(),
        ]);
    }
};
