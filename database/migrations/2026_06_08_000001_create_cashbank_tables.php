<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashbank_document_codes', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 30)->unique();
            $table->string('nama', 100);
            $table->string('prefix', 20)->nullable();
            $table->text('keterangan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cashbank_coas', function (Blueprint $table) {
            $table->id();
            $table->string('kode_akun', 50)->unique();
            $table->string('nama_akun', 150);
            $table->enum('tipe', ['kas', 'bank', 'hutang', 'biaya', 'pendapatan', 'lainnya'])->default('lainnya');
            $table->text('keterangan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cashbank_banks', function (Blueprint $table) {
            $table->id();
            $table->string('kode_bank', 30)->unique();
            $table->string('nama_bank', 100);
            $table->string('nomor_rekening', 50)->nullable();
            $table->string('nama_rekening', 100)->nullable();
            $table->foreignId('coa_id')->nullable()->constrained('cashbank_coas')->nullOnDelete();
            $table->text('keterangan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cashbank_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_transaksi', 50)->unique();
            $table->enum('jenis', ['umum', 'pembayaran_hutang'])->default('umum');
            $table->foreignId('unit_id')->nullable()->constrained('unit')->nullOnDelete();
            $table->foreignId('document_code_id')->nullable()->constrained('cashbank_document_codes')->nullOnDelete();
            $table->foreignId('coa_id')->nullable()->constrained('cashbank_coas')->nullOnDelete();
            $table->foreignId('bank_id')->nullable()->constrained('cashbank_banks')->nullOnDelete();
            $table->date('tgl_transaksi');
            $table->unsignedInteger('supplier_id')->nullable()->index();
            $table->string('dibayar_kepada', 150);
            $table->text('guna_membayar')->nullable();
            $table->decimal('sejumlah', 18, 2)->default(0);
            $table->enum('dibayar_dengan', ['cash', 'kredit'])->default('cash');
            $table->enum('status', ['draft', 'posted', 'void'])->default('posted');
            $table->foreignId('created_user')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cashbank_transaction_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('cashbank_transactions')->cascadeOnDelete();
            $table->foreignId('coa_id')->nullable()->constrained('cashbank_coas')->nullOnDelete();
            $table->unsignedInteger('penerimaan_id')->nullable();
            $table->string('nomor_invoice', 50)->nullable();
            $table->decimal('nilai_invoice', 18, 2)->default(0);
            $table->decimal('sudah_dibayar', 18, 2)->default(0);
            $table->decimal('jumlah_bayar', 18, 2)->default(0);
            $table->decimal('sisa', 18, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['penerimaan_id', 'nomor_invoice']);
        });

        Schema::create('cashbank_transaction_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('cashbank_transactions')->cascadeOnDelete();
            $table->string('aksi', 50);
            $table->text('keterangan')->nullable();
            $table->json('payload')->nullable();
            $table->foreignId('created_user')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Role::findOrCreate('cashbank', 'web');
        $this->seedMenus();
    }

    public function down(): void
    {
        Schema::dropIfExists('cashbank_transaction_logs');
        Schema::dropIfExists('cashbank_transaction_details');
        Schema::dropIfExists('cashbank_transactions');
        Schema::dropIfExists('cashbank_banks');
        Schema::dropIfExists('cashbank_coas');
        Schema::dropIfExists('cashbank_document_codes');

        if (Schema::hasTable('menus')) {
            DB::table('menus')
                ->whereIn('link', [
                    '#cashbank-master',
                    '#cashbank-transaksi',
                    'cashbank.document-codes.index',
                    'cashbank.coas.index',
                    'cashbank.banks.index',
                    'cashbank.transactions.umum.index',
                    'cashbank.transactions.hutang.index',
                ])
                ->delete();
        }
    }

    private function seedMenus(): void
    {
        if (! Schema::hasTable('menus')) {
            return;
        }

        $rootRole = ';superadmin;admin;cashbank;';

        DB::table('menus')->updateOrInsert(
            ['link' => '#cashbank-master'],
            [
                'name' => 'Master Cash Bank',
                'parent_id' => null,
                'role' => $rootRole,
                'seq' => 30,
                'icon' => 'bi bi-wallet2',
                'updated_at' => now(),
                'created_at' => now(),
                'deleted_at' => null,
            ]
        );

        DB::table('menus')->updateOrInsert(
            ['link' => '#cashbank-transaksi'],
            [
                'name' => 'Transaksi Cash Bank',
                'parent_id' => null,
                'role' => $rootRole,
                'seq' => 31,
                'icon' => 'bi bi-cash-coin',
                'updated_at' => now(),
                'created_at' => now(),
                'deleted_at' => null,
            ]
        );

        $masterId = DB::table('menus')->where('link', '#cashbank-master')->value('id');
        $transaksiId = DB::table('menus')->where('link', '#cashbank-transaksi')->value('id');

        foreach ([
            ['Kode Dokumen', 'cashbank.document-codes.index', $masterId, 1, 'bi bi-file-earmark-text'],
            ['Kode Akun COA', 'cashbank.coas.index', $masterId, 2, 'bi bi-diagram-3'],
            ['Bank', 'cashbank.banks.index', $masterId, 3, 'bi bi-bank'],
            ['Cash Bank Umum', 'cashbank.transactions.umum.index', $transaksiId, 1, 'bi bi-receipt'],
            ['Cash Bank Pembayaran Hutang', 'cashbank.transactions.hutang.index', $transaksiId, 2, 'bi bi-credit-card-2-front'],
        ] as [$name, $link, $parentId, $seq, $icon]) {
            DB::table('menus')->updateOrInsert(
                ['link' => $link],
                [
                    'name' => $name,
                    'parent_id' => $parentId,
                    'role' => $rootRole,
                    'seq' => $seq,
                    'icon' => $icon,
                    'updated_at' => now(),
                    'created_at' => now(),
                    'deleted_at' => null,
                ]
            );
        }
    }
};
