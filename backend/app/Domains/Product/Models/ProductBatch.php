<?php

namespace App\Domains\Product\Models;

use App\Domains\Shared\Models\BaseTenantModel;

class ProductBatch extends BaseTenantModel
{
    protected $fillable = [
        'tenant_id', 'product_id', 'warehouse_id', 'batch_number',
        'initial_quantity', 'current_quantity', 'manufacturing_date',
        'expiry_date', 'unit_cost',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'manufacturing_date' => 'date',
        'unit_cost' => 'decimal:2',
    ];
}
