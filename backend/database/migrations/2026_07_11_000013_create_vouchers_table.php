<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->enum('type', ['receipt', 'payment']); // receipt = collection from customer, payment = payment to supplier
            $table->string('voucher_number')->unique();
            $table->enum('entity_type', ['customer', 'supplier']);
            $table->uuid('entity_id');
            $table->decimal('amount', 15, 2);
            $table->enum('payment_method', ['cash', 'check', 'bank_transfer']);
            $table->string('reference_number')->nullable(); // Check number or Transfer ID
            $table->date('voucher_date');
            $table->text('notes')->nullable();
            $table->uuid('created_by');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });

        // Allocation of vouchers to specific invoices/purchases
        Schema::create('voucher_allocations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('voucher_id');
            $table->uuid('reference_id'); // invoice_id or purchase_id
            $table->decimal('amount', 15, 2);
            $table->timestamps();

            $table->foreign('voucher_id')->references('id')->on('vouchers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_allocations');
        Schema::dropIfExists('vouchers');
    }
};
