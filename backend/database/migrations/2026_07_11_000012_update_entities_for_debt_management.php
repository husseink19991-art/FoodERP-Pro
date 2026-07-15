<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->decimal('credit_limit', 15, 2)->default(0)->after('current_balance');
            $table->integer('payment_terms_days')->default(0)->after('credit_limit'); // 0 = Cash, 30 = Net 30
            $table->boolean('is_blocked')->default(false)->after('payment_terms_days');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->decimal('credit_limit', 15, 2)->default(0)->after('current_balance');
            $table->integer('payment_terms_days')->default(0)->after('credit_limit');
        });

        // Ledger for detailed transaction history
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->enum('entity_type', ['customer', 'supplier']);
            $table->uuid('entity_id');
            $table->enum('transaction_type', ['invoice', 'purchase', 'receipt', 'payment', 'adjustment']);
            $table->uuid('reference_id'); // ID of the related document
            $table->string('reference_number'); // Invoice #, Voucher #
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->decimal('balance_after', 15, 2);
            $table->text('description')->nullable();
            $table->timestamp('transaction_date');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index(['entity_type', 'entity_id', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['credit_limit', 'payment_terms_days']);
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['credit_limit', 'payment_terms_days', 'is_blocked']);
        });
    }
};
