<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('kartu_stok_saldo_bulanan')) {
            return;
        }

        Schema::create('kartu_stok_saldo_bulanan', function (Blueprint $table) {
            $table->id();
            $table->string('periode', 7);
            $table->unsignedBigInteger('barang_id');
            $table->unsignedBigInteger('unit_id');
            $table->decimal('saldo_awal', 15, 3)->default(0);
            $table->decimal('total_masuk', 15, 3)->default(0);
            $table->decimal('total_keluar', 15, 3)->default(0);
            $table->decimal('saldo_akhir', 15, 3)->default(0);
            $table->dateTime('generated_at')->nullable();
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->timestamps();

            $table->unique(['periode', 'barang_id', 'unit_id'], 'kartu_stok_saldo_periode_barang_unit_unique');
            $table->index(['unit_id', 'periode']);
            $table->index(['barang_id', 'periode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kartu_stok_saldo_bulanan');
    }
};
