<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('penerimaan_detail')) {
            return;
        }

        Schema::table('penerimaan_detail', function (Blueprint $table) {
            if (! Schema::hasColumn('penerimaan_detail', 'harga_beli')) {
                $table->float('harga_beli')->default(0);
            }
            if (! Schema::hasColumn('penerimaan_detail', 'harga_jual')) {
                $table->float('harga_jual')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('penerimaan_detail')) {
            return;
        }

        Schema::table('penerimaan_detail', function (Blueprint $table) {
            if (Schema::hasColumn('penerimaan_detail', 'harga_beli')) {
                $table->dropColumn('harga_beli');
            }
            if (Schema::hasColumn('penerimaan_detail', 'harga_jual')) {
                $table->dropColumn('harga_jual');
            }
        });
    }
};
