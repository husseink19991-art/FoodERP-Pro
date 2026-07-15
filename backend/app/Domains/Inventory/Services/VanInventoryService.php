<?php

namespace App\Domains\Inventory\Services;

use App\Domains\Inventory\Models\VanLoad;
use App\Domains\Inventory\Models\VanLoadItem;
use App\Domains\Inventory\Models\VanInventory;
use App\Domains\Product\Models\ProductBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VanInventoryService
{
    /**
     * Approve and execute a Van Load request
     */
    public function approveVanLoad(string $vanLoadId)
    {
        return DB::transaction(function () use ($vanLoadId) {
            $vanLoad = VanLoad::with('items')->findOrFail($vanLoadId);

            if ($vanLoad->status !== 'pending') {
                throw new \Exception("Van load is already {$vanLoad->status}");
            }

            foreach ($vanLoad->items as $item) {
                $this->transferToVan(
                    $vanLoad->tenant_id,
                    $vanLoad->vehicle_id,
                    $vanLoad->warehouse_id,
                    $item->product_id,
                    $item->quantity,
                    $vanLoad->id
                );
            }

            $vanLoad->update(['status' => 'completed']);
            return $vanLoad;
        });
    }

    /**
     * Transfer stock from warehouse (FEFO) to van
     */
    protected function transferToVan(string $tenantId, string $vehicleId, string $warehouseId, string $productId, int $quantity, string $loadId)
    {
        $batches = ProductBatch::where('tenant_id', $tenantId)
            ->where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->where('current_quantity', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->lockForUpdate()
            ->get();

        $remainingToTransfer = $quantity;

        foreach ($batches as $batch) {
            if ($remainingToTransfer <= 0) break;

            $transferAmount = min($batch->current_quantity, $remainingToTransfer);
            
            // Deduct from warehouse batch
            $batch->decrement('current_quantity', $transferAmount);

            // Record movement
            DB::table('batch_movements')->insert([
                'id' => Str::uuid(),
                'tenant_id' => $tenantId,
                'batch_id' => $batch->id,
                'type' => 'out',
                'quantity' => $transferAmount,
                'reference_type' => 'van_load',
                'reference_id' => $loadId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Add to van inventory (we might want to track batches in van too, 
            // but for simplicity here we track total per product per vehicle)
            VanInventory::updateOrCreate(
                ['tenant_id' => $tenantId, 'vehicle_id' => $vehicleId, 'product_id' => $productId],
                ['quantity' => DB::raw("quantity + $transferAmount")]
            );

            $remainingToTransfer -= $transferAmount;
        }

        if ($remainingToTransfer > 0) {
            throw new \Exception("Insufficient warehouse stock for product ID: $productId");
        }
    }
}
