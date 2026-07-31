<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('retur')) {
            return;
        }

        Schema::table('retur', function (Blueprint $table) {
            if (! Schema::hasColumn('retur', 'penerimaan_id')) {
                $table->unsignedInteger('penerimaan_id')->nullable()->after('unit_id');
            }

            if (! Schema::hasColumn('retur', 'nomor_penerimaan')) {
                $table->string('nomor_penerimaan', 50)->nullable()->after('penerimaan_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('retur')) {
            return;
        }

        Schema::table('retur', function (Blueprint $table) {
            if (Schema::hasColumn('retur', 'nomor_penerimaan')) {
                $table->dropColumn('nomor_penerimaan');
            }

            if (Schema::hasColumn('retur', 'penerimaan_id')) {
                $table->dropColumn('penerimaan_id');
            }
        });
    }
};
