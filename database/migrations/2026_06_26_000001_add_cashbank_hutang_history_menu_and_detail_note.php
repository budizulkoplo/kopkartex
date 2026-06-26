<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cashbank_transaction_details') && ! Schema::hasColumn('cashbank_transaction_details', 'keterangan')) {
            Schema::table('cashbank_transaction_details', function (Blueprint $table): void {
                $table->text('keterangan')->nullable()->after('sisa');
            });
        }

        if (! Schema::hasTable('menus')) {
            return;
        }

        $rootRole = ';superadmin;admin;cashbank;';
        $parentId = DB::table('menus')->where('link', '#cashbank-transaksi')->value('id');

        DB::table('menus')->updateOrInsert(
            ['link' => 'cashbank.transactions.hutang.history'],
            [
                'name' => 'Riwayat Pembayaran Hutang',
                'parent_id' => $parentId,
                'role' => $rootRole,
                'seq' => 3,
                'icon' => 'bi bi-clock-history',
                'updated_at' => now(),
                'created_at' => now(),
                'deleted_at' => null,
            ]
        );
    }

    public function down(): void
    {
        if (Schema::hasTable('menus')) {
            DB::table('menus')
                ->where('link', 'cashbank.transactions.hutang.history')
                ->delete();
        }
    }
};
