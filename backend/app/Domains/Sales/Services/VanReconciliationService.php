<?php

namespace App\Domains\Sales\Services;

use App\Domains\Sales\Models\VanReconciliation;
use App\Domains\Inventory\Models\VanInventory;
use App\Domains\Sales\Models\Invoice;
use Illuminate\Support\Facades\DB;

class VanReconciliationService
{
    /**
     * Generate end-of-day reconciliation data for a van
     */
    public function generateReconciliationData(string $tenantId, string $vehicleId, string $salesRepId, string $date)
    {
        // 1. Get all sales for the day
        $sales = Invoice::where('tenant_id', $tenantId)
            ->where('sales_rep_id', $salesRepId)
            ->whereDate('created_at', $date)
            ->get();

        $totalSales = $sales->sum('total_amount');
        $cashCollected = $sales->where('payment_type', 'cash')->sum('total_amount');
        $creditSales = $sales->where('payment_type', 'credit')->sum('total_amount');

        // 2. Get current van inventory
        $remainingInventory = VanInventory::where('tenant_id', $tenantId)
            ->where('vehicle_id', $vehicleId)
            ->with('product')
            ->get();

        // 3. Calculate Variance (simplified version)
        // In a real system, we would track initial stock + loads - sales
        // Here we return the summary for the UI to review
        return [
            'date' => $date,
            'total_sales' => $totalSales,
            'cash_collected' => $cashCollected,
            'credit_sales' => $creditSales,
            'remaining_items' => $remainingInventory->map(fn($item) => [
                'product_name' => $item->product->name,
                'quantity' => $item->quantity
            ])
        ];
    }

    /**
     * Finalize reconciliation and log discrepancies
     */
    public function finalizeReconciliation(array $data)
    {
        return DB::transaction(function () use ($data) {
            $reconciliation = VanReconciliation::create([
                'tenant_id' => $data['tenant_id'],
                'vehicle_id' => $data['vehicle_id'],
                'sales_rep_id' => $data['sales_rep_id'],
                'reconciliation_date' => $data['date'],
                'total_sales_reported' => $data['total_sales'],
                'total_cash_collected' => $data['cash_collected'],
                'total_credit_sales' => $data['credit_sales'],
                'variance' => $data['variance'], // User entered or calculated discrepancy
                'status' => 'verified',
                'notes' => $data['notes'] ?? null,
            ]);

            // If there's a variance, we might trigger a fraud alert
            if (abs($data['variance']) > 0) {
                DB::table('fraud_alerts')->insert([
                    'id' => \Illuminate\Support\Str::uuid(),
                    'tenant_id' => $data['tenant_id'],
                    'type' => 'inventory_discrepancy',
                    'details' => json_encode([
                        'reconciliation_id' => $reconciliation->id,
                        'variance' => $data['variance'],
                        'sales_rep_id' => $data['sales_rep_id']
                    ]),
                    'severity' => abs($data['variance']) > 100 ? 'high' : 'medium',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return $reconciliation;
        });
    }
}
