<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('barang')) {
            return;
        }

        Schema::table('barang', function (Blueprint $table) {
            if (! Schema::hasColumn('barang', 'status_produk')) {
                $table->enum('status_produk', ['aktif', 'nonaktif'])
                    ->default('aktif')
                    ->after('nama_barang');
            }
        });

        if (Schema::hasColumn('barang', 'status_produk')) {
            DB::table('barang')
                ->whereNull('status_produk')
                ->update(['status_produk' => 'aktif']);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('barang') || ! Schema::hasColumn('barang', 'status_produk')) {
            return;
        }

        Schema::table('barang', function (Blueprint $table) {
            $table->dropColumn('status_produk');
        });
    }
};
