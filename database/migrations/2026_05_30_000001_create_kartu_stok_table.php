<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('kartu_stok')) {
            return;
        }

        Schema::create('kartu_stok', function (Blueprint $table) {
            $table->id();
            $table->dateTime('tanggal');
            $table->unsignedBigInteger('barang_id');
            $table->unsignedBigInteger('unit_id');
            $table->string('jenis_transaksi', 50);
            $table->string('arah', 10);
            $table->decimal('qty_masuk', 15, 3)->default(0);
            $table->decimal('qty_keluar', 15, 3)->default(0);
            $table->decimal('saldo_awal', 15, 3)->default(0);
            $table->decimal('saldo_akhir', 15, 3)->default(0);
            $table->decimal('harga_pokok', 15, 2)->nullable();
            $table->decimal('nilai_mutasi', 15, 2)->nullable();
            $table->string('nomor_referensi', 100)->nullable();
            $table->string('referensi_tipe', 100)->nullable();
            $table->unsignedBigInteger('referensi_id')->nullable();
            $table->unsignedBigInteger('referensi_detail_id')->nullable();
            $table->unsignedBigInteger('unit_lawan_id')->nullable();
            $table->uuid('batch_id')->nullable();
            $table->unsignedBigInteger('dibalik_dari_id')->nullable();
            $table->unsignedBigInteger('created_user')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['barang_id', 'unit_id', 'tanggal']);
            $table->index(['unit_id', 'tanggal']);
            $table->index(['jenis_transaksi', 'tanggal']);
            $table->index(['referensi_tipe', 'referensi_id']);
            $table->index('nomor_referensi');
            $table->index('batch_id');
            $table->index('dibalik_dari_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kartu_stok');
    }
};
