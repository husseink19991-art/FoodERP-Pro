<?php

namespace App\Domains\Inventory\Services;

use App\Domains\Product\Models\Product;
use App\Domains\Product\Models\ProductBatch;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Deduct inventory using FEFO (First Expiry, First Out)
     */
    public function deductStock(Product $product, int $quantity, string $referenceType, string $referenceId)
    {
        return DB::transaction(function () use ($product, $quantity, $referenceType, $referenceId) {
            $batches = ProductBatch::where('product_id', $product->id)
                ->where('current_quantity', '>', 0)
                ->orderBy('expiry_date', 'asc')
                ->lockForUpdate()
                ->get();

            $remainingToDeduct = $quantity;

            foreach ($batches as $batch) {
                if ($remainingToDeduct <= 0) break;

                $deduction = min($batch->current_quantity, $remainingToDeduct);
                
                $batch->decrement('current_quantity', $deduction);
                
                DB::table('batch_movements')->insert([
                    'id' => \Illuminate\Support\Str::uuid(),
                    'tenant_id' => $product->tenant_id,
                    'batch_id' => $batch->id,
                    'type' => 'out',
                    'quantity' => $deduction,
                    'reference_type' => $referenceType,
                    'reference_id' => $referenceId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $remainingToDeduct -= $deduction;
            }

            if ($remainingToDeduct > 0) {
                throw new \Exception("Insufficient stock for product: {$product->name}");
            }
        });
    }

    /**
     * Get expiring batches alert
     */
    public function getExpiringBatches(int $daysThreshold = 30)
    {
        return ProductBatch::with('product')
            ->where('current_quantity', '>', 0)
            ->where('expiry_date', '<=', now()->addDays($daysThreshold))
            ->orderBy('expiry_date', 'asc')
            ->get();
    }
}
