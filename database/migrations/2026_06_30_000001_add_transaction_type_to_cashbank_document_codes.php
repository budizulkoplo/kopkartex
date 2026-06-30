<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cashbank_document_codes')) {
            return;
        }

        $afterColumn = Schema::hasColumn('cashbank_document_codes', 'bank_id') ? 'bank_id' : 'prefix';

        Schema::table('cashbank_document_codes', function (Blueprint $table) use ($afterColumn): void {
            if (! Schema::hasColumn('cashbank_document_codes', 'transaction_type')) {
                $table->enum('transaction_type', ['payment', 'receipt'])->default('payment')->after($afterColumn);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('cashbank_document_codes') || ! Schema::hasColumn('cashbank_document_codes', 'transaction_type')) {
            return;
        }

        Schema::table('cashbank_document_codes', function (Blueprint $table): void {
            $table->dropColumn('transaction_type');
        });
    }
};
