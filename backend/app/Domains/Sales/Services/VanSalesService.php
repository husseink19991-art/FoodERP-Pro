<?php

namespace App\Domains\Sales\Services;

use App\Domains\Sales\Models\Invoice;
use App\Domains\Sales\Models\InvoiceItem;
use App\Domains\Inventory\Models\VanInventory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VanSalesService
{
    /**
     * Process a van sale invoice
     */
    public function createVanInvoice(array $data, string $vehicleId, string $salesRepId)
    {
        return DB::transaction(function () use ($data, $vehicleId, $salesRepId) {
            $tenantId = $data['tenant_id'];

            // 1. Create Invoice
            $invoice = Invoice::create([
                'tenant_id' => $tenantId,
                'customer_id' => $data['customer_id'],
                'sales_rep_id' => $salesRepId,
                'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
                'total_amount' => $data['total_amount'],
                'total_cost' => $data['total_cost'],
                'status' => $data['payment_type'] === 'cash' ? 'paid' : 'pending',
                'payment_type' => $data['payment_type'],
            ]);

            // 2. Process Items and Deduct from Van Inventory
            foreach ($data['items'] as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'unit_cost' => $item['unit_cost'],
                    'subtotal' => $item['subtotal'],
                ]);

                $this->deductFromVan($tenantId, $vehicleId, $item['product_id'], $item['quantity']);
            }

            // 3. Update Customer Balance if Credit
            if ($data['payment_type'] === 'credit') {
                DB::table('customers')
                    ->where('id', $data['customer_id'])
                    ->increment('current_balance', $data['total_amount']);
                
                // Record in debts ledger
                DB::table('debts')->insert([
                    'id' => Str::uuid(),
                    'tenant_id' => $tenantId,
                    'type' => 'customer_receivable',
                    'entity_id' => $data['customer_id'],
                    'reference_id' => $invoice->id,
                    'amount' => $data['total_amount'],
                    'remaining_balance' => $data['total_amount'],
                    'due_date' => now()->addDays(30), // Default 30 days
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return $invoice;
        });
    }

    protected function deductFromVan(string $tenantId, string $vehicleId, string $productId, int $quantity)
    {
        $vanStock = VanInventory::where('tenant_id', $tenantId)
            ->where('vehicle_id', $vehicleId)
            ->where('product_id', $productId)
            ->lockForUpdate()
            ->first();

        if (!$vanStock || $vanStock->quantity < $quantity) {
            throw new \Exception("Insufficient stock in van for product ID: $productId");
        }

        $vanStock->decrement('quantity', $quantity);
    }
}
