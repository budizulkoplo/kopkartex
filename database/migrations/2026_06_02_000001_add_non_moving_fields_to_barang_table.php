<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            if (! Schema::hasColumn('barang', 'is_non_moving')) {
                $table->boolean('is_non_moving')->default(false)->after('status_produk');
            }

            if (! Schema::hasColumn('barang', 'non_moving_at')) {
                $table->timestamp('non_moving_at')->nullable()->after('is_non_moving');
            }

            if (! Schema::hasColumn('barang', 'non_moving_by')) {
                $table->unsignedBigInteger('non_moving_by')->nullable()->after('non_moving_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            if (Schema::hasColumn('barang', 'non_moving_by')) {
                $table->dropColumn('non_moving_by');
            }

            if (Schema::hasColumn('barang', 'non_moving_at')) {
                $table->dropColumn('non_moving_at');
            }

            if (Schema::hasColumn('barang', 'is_non_moving')) {
                $table->dropColumn('is_non_moving');
            }
        });
    }
};
