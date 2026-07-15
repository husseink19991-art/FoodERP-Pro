<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_batches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('product_id');
            $table->uuid('warehouse_id');
            $table->string('batch_number');
            $table->integer('initial_quantity');
            $table->integer('current_quantity');
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date');
            $table->decimal('unit_cost', 15, 2);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            $table->index(['product_id', 'expiry_date']); // For FEFO
        });

        // Track inventory movements per batch
        Schema::create('batch_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('batch_id');
            $table->enum('type', ['in', 'out', 'adjustment']);
            $table->integer('quantity');
            $table->string('reference_type'); // e.g., Purchase, Invoice, Adjustment
            $table->uuid('reference_id');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('batch_id')->references('id')->on('product_batches')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_movements');
        Schema::dropIfExists('product_batches');
    }
};
