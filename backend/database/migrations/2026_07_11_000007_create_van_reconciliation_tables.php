<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Van Inventory (Inventory assigned to a specific vehicle/sales rep)
        Schema::create('van_inventory', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('vehicle_id');
            $table->uuid('product_id');
            $table->integer('quantity')->default(0);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->unique(['vehicle_id', 'product_id']);
        });

        // Van Load Requests (Loading stock from warehouse to van)
        Schema::create('van_loads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('vehicle_id');
            $table->uuid('warehouse_id');
            $table->uuid('requested_by');
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('van_load_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('van_load_id');
            $table->uuid('product_id');
            $table->integer('quantity');
            $table->timestamps();

            $table->foreign('van_load_id')->references('id')->on('van_loads')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });

        // Van Sales Reconciliation (End of day reconciliation)
        Schema::create('van_reconciliations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('vehicle_id');
            $table->uuid('sales_rep_id');
            $table->date('reconciliation_date');
            $table->decimal('total_sales_reported', 15, 2);
            $table->decimal('total_cash_collected', 15, 2);
            $table->decimal('total_credit_sales', 15, 2);
            $table->decimal('variance', 15, 2)->default(0); // Difference between inventory out and sales+remaining
            $table->enum('status', ['pending', 'verified', 'disputed'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->foreign('sales_rep_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('van_reconciliations');
        Schema::dropIfExists('van_load_items');
        Schema::dropIfExists('van_loads');
        Schema::dropIfExists('van_inventory');
    }
};
