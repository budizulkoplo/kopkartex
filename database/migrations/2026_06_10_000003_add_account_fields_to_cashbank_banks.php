<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cashbank_banks')) {
            return;
        }

        Schema::table('cashbank_banks', function (Blueprint $table) {
            if (! Schema::hasColumn('cashbank_banks', 'kode_akun')) {
                $table->string('kode_akun', 50)->nullable()->after('nama_bank')->index();
            }
            if (! Schema::hasColumn('cashbank_banks', 'nama_akun')) {
                $table->string('nama_akun', 150)->nullable()->after('kode_akun');
            }
        });

        DB::table('cashbank_banks')->where('kode_bank', 'BBCA')->update([
            'kode_akun' => '110103',
            'nama_akun' => 'Kas CV, Mandiri',
            'nama_bank' => 'Bank BCA',
            'nomor_rekening' => '11232',
            'nama_rekening' => 'Kopkartex BCA',
            'coa_id' => null,
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('cashbank_banks')) {
            return;
        }

        Schema::table('cashbank_banks', function (Blueprint $table) {
            if (Schema::hasColumn('cashbank_banks', 'nama_akun')) {
                $table->dropColumn('nama_akun');
            }
            if (Schema::hasColumn('cashbank_banks', 'kode_akun')) {
                $table->dropColumn('kode_akun');
            }
        });
    }
};
