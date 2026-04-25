<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->enum('status_produk', ['aktif', 'nonaktif'])
                ->default('aktif')
                ->after('nama_barang');
        });

        DB::table('barang')
            ->whereNull('status_produk')
            ->update(['status_produk' => 'aktif']);
    }

    public function down(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->dropColumn('status_produk');
        });
    }
};
