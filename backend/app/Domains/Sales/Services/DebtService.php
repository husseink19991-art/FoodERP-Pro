<?php

namespace App\Domains\Sales\Services;

use App\Domains\Sales\Models\Customer;
use App\Domains\Sales\Models\Supplier;
use App\Domains\Sales\Models\LedgerEntry;
use App\Domains\Sales\Models\Voucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DebtService
{
    /**
     * Log a receipt voucher (Collection from Customer)
     */
    public function logReceipt(array $data, string $userId)
    {
        return DB::transaction(function () use ($data, $userId) {
            $customer = Customer::lockForUpdate()->findOrFail($data['entity_id']);
            
            $voucher = Voucher::create([
                'tenant_id' => $customer->tenant_id,
                'type' => 'receipt',
                'voucher_number' => 'RV-' . strtoupper(Str::random(8)),
                'entity_type' => 'customer',
                'entity_id' => $customer->id,
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'reference_number' => $data['reference_number'] ?? null,
                'voucher_date' => $data['voucher_date'],
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            // Update customer balance
            $customer->decrement('current_balance', $data['amount']);

            // Add ledger entry
            LedgerEntry::create([
                'tenant_id' => $customer->tenant_id,
                'entity_type' => 'customer',
                'entity_id' => $customer->id,
                'transaction_type' => 'receipt',
                'reference_id' => $voucher->id,
                'reference_number' => $voucher->voucher_number,
                'debit' => 0,
                'credit' => $data['amount'],
                'balance_after' => $customer->current_balance,
                'description' => "Receipt Voucher: {$voucher->voucher_number}",
                'transaction_date' => $data['voucher_date'],
            ]);

            // Allocate to specific debts (simplified)
            if (!empty($data['allocations'])) {
                foreach ($data['allocations'] as $alloc) {
                    DB::table('voucher_allocations')->insert([
                        'id' => Str::uuid(),
                        'voucher_id' => $voucher->id,
                        'reference_id' => $alloc['invoice_id'],
                        'amount' => $alloc['amount'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('debts')
                        ->where('reference_id', $alloc['invoice_id'])
                        ->decrement('remaining_balance', $alloc['amount']);
                }
            }

            return $voucher;
        });
    }

    /**
     * Log a payment voucher (Payment to Supplier)
     */
    public function logPayment(array $data, string $userId)
    {
        return DB::transaction(function () use ($data, $userId) {
            $supplier = Supplier::lockForUpdate()->findOrFail($data['entity_id']);
            
            $voucher = Voucher::create([
                'tenant_id' => $supplier->tenant_id,
                'type' => 'payment',
                'voucher_number' => 'PV-' . strtoupper(Str::random(8)),
                'entity_type' => 'supplier',
                'entity_id' => $supplier->id,
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'reference_number' => $data['reference_number'] ?? null,
                'voucher_date' => $data['voucher_date'],
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            // Update supplier balance
            $supplier->decrement('current_balance', $data['amount']);

            // Add ledger entry
            LedgerEntry::create([
                'tenant_id' => $supplier->tenant_id,
                'entity_type' => 'supplier',
                'entity_id' => $supplier->id,
                'transaction_type' => 'payment',
                'reference_id' => $voucher->id,
                'reference_number' => $voucher->voucher_number,
                'debit' => $data['amount'],
                'credit' => 0,
                'balance_after' => $supplier->current_balance,
                'description' => "Payment Voucher: {$voucher->voucher_number}",
                'transaction_date' => $data['voucher_date'],
            ]);

            return $voucher;
        });
    }

    /**
     * Get Debt Aging Report
     */
    public function getDebtAgingReport(string $tenantId)
    {
        return DB::table('debts')
            ->where('tenant_id', $tenantId)
            ->where('remaining_balance', '>', 0)
            ->select(
                'entity_id',
                DB::raw("SUM(CASE WHEN due_date >= CURRENT_DATE THEN remaining_balance ELSE 0 END) as current"),
                DB::raw("SUM(CASE WHEN due_date < CURRENT_DATE AND due_date >= CURRENT_DATE - INTERVAL '30 days' THEN remaining_balance ELSE 0 END) as overdue_1_30"),
                DB::raw("SUM(CASE WHEN due_date < CURRENT_DATE - INTERVAL '30 days' AND due_date >= CURRENT_DATE - INTERVAL '60 days' THEN remaining_balance ELSE 0 END) as overdue_31_60"),
                DB::raw("SUM(CASE WHEN due_date < CURRENT_DATE - INTERVAL '60 days' THEN remaining_balance ELSE 0 END) as overdue_60_plus")
            )
            ->groupBy('entity_id')
            ->get();
    }
}
