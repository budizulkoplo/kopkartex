<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cashbank_document_codes')) {
            Schema::table('cashbank_document_codes', function (Blueprint $table) {
                if (! Schema::hasColumn('cashbank_document_codes', 'coa_id')) {
                    $table->unsignedBigInteger('coa_id')->nullable()->after('prefix')->index();
                }
            });
        }

        if (Schema::hasTable('cashbank_transactions')) {
            Schema::table('cashbank_transactions', function (Blueprint $table) {
                if (! Schema::hasColumn('cashbank_transactions', 'periode')) {
                    $table->string('periode', 6)->nullable()->after('tgl_transaksi');
                }
                if (! Schema::hasColumn('cashbank_transactions', 'no_ref_nota')) {
                    $table->text('no_ref_nota')->nullable()->after('guna_membayar');
                }
                if (! Schema::hasColumn('cashbank_transactions', 'no_cash_cek_giro')) {
                    $table->string('no_cash_cek_giro', 80)->nullable()->after('dibayar_dengan');
                }
                if (! Schema::hasColumn('cashbank_transactions', 'tgl_giro_cek')) {
                    $table->date('tgl_giro_cek')->nullable()->after('no_cash_cek_giro');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('cashbank_transactions')) {
            Schema::table('cashbank_transactions', function (Blueprint $table) {
                foreach (['periode', 'no_ref_nota', 'no_cash_cek_giro', 'tgl_giro_cek'] as $column) {
                    if (Schema::hasColumn('cashbank_transactions', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('cashbank_document_codes')) {
            Schema::table('cashbank_document_codes', function (Blueprint $table) {
                if (Schema::hasColumn('cashbank_document_codes', 'coa_id')) {
                    $table->dropColumn('coa_id');
                }
            });
        }
    }
};
