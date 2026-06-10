<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cashbank_document_codes')) {
            Schema::table('cashbank_document_codes', function (Blueprint $table) {
                if (! Schema::hasColumn('cashbank_document_codes', 'bank_id')) {
                    $table->unsignedBigInteger('bank_id')->nullable()->after('prefix')->index();
                }
            });
        }

        $kasCoaId = DB::table('cashbank_coas')->updateOrInsert(
            ['kode_akun' => '110103'],
            [
                'nama_akun' => 'Kas CV, Mandiri',
                'tipe' => 'kas',
                'is_active' => true,
                'deleted_at' => null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('cashbank_coas')->updateOrInsert(
            ['kode_akun' => '420105'],
            [
                'nama_akun' => 'Pembelian Toko',
                'tipe' => 'biaya',
                'is_active' => true,
                'deleted_at' => null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $kasCoaId = DB::table('cashbank_coas')->where('kode_akun', '110103')->value('id');

        DB::table('cashbank_banks')->updateOrInsert(
            ['kode_bank' => 'BBCA'],
            [
                'nama_bank' => 'Bank BCA',
                'nomor_rekening' => '11232',
                'nama_rekening' => 'Kopkartex BCA',
                'coa_id' => $kasCoaId,
                'is_active' => true,
                'deleted_at' => null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $bankId = DB::table('cashbank_banks')->where('kode_bank', 'BBCA')->value('id');

        DB::table('cashbank_document_codes')->updateOrInsert(
            ['kode' => 'CPMA'],
            [
                'nama' => 'CASH MANDIRI',
                'prefix' => 'CP',
                'bank_id' => $bankId,
                'coa_id' => null,
                'is_active' => true,
                'deleted_at' => null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        if (! Schema::hasTable('cashbank_document_codes') || ! Schema::hasColumn('cashbank_document_codes', 'bank_id')) {
            return;
        }

        Schema::table('cashbank_document_codes', function (Blueprint $table) {
            $table->dropColumn('bank_id');
        });
    }
};
