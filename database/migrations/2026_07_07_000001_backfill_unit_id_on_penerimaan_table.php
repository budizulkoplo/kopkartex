<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('penerimaan')) {
            return;
        }

        if (! Schema::hasColumn('penerimaan', 'unit_id')) {
            Schema::table('penerimaan', function (Blueprint $table) {
                $table->unsignedBigInteger('unit_id')->nullable()->after('grandtotal');
                $table->index('unit_id');
            });
        }

        DB::statement("
            UPDATE penerimaan p
            JOIN users u ON u.id = p.user_id
            SET p.unit_id = CAST(u.unit_kerja AS UNSIGNED)
            WHERE p.unit_id IS NULL
                AND u.unit_kerja IS NOT NULL
                AND u.unit_kerja <> ''
                AND u.unit_kerja REGEXP '^[0-9]+$'
        ");
    }

    public function down(): void
    {
        if (! Schema::hasTable('penerimaan') || ! Schema::hasColumn('penerimaan', 'unit_id')) {
            return;
        }

        Schema::table('penerimaan', function (Blueprint $table) {
            $table->dropColumn('unit_id');
        });
    }
};
