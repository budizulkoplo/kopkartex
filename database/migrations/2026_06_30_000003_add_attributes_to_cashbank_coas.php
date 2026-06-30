<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cashbank_coas')) {
            return;
        }

        Schema::table('cashbank_coas', function (Blueprint $table): void {
            foreach (['att1', 'att2', 'att3', 'att4', 'att5'] as $column) {
                if (! Schema::hasColumn('cashbank_coas', $column)) {
                    $table->string($column, 50)->nullable()->after('tipe');
                }
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE cashbank_coas MODIFY tipe VARCHAR(100) NOT NULL DEFAULT "lainnya"');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('cashbank_coas')) {
            return;
        }

        Schema::table('cashbank_coas', function (Blueprint $table): void {
            foreach (['att1', 'att2', 'att3', 'att4', 'att5'] as $column) {
                if (Schema::hasColumn('cashbank_coas', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
