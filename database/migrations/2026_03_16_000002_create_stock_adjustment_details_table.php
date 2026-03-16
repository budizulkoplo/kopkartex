<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustment_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_adjustment_id');
            $table->unsignedBigInteger('barang_id');
            $table->string('adjustment_type', 20);
            $table->integer('old_stock')->default(0);
            $table->integer('adjustment_value')->default(0);
            $table->integer('new_stock')->default(0);
            $table->unsignedBigInteger('old_satuan_id')->nullable();
            $table->unsignedBigInteger('new_satuan_id')->nullable();
            $table->integer('conversion_factor')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('stock_adjustment_id');
            $table->index('barang_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustment_details');
    }
};
