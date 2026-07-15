<?php

namespace App\Domains\Inventory\Models;

use App\Domains\Shared\Models\BaseTenantModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VanLoad extends BaseTenantModel
{
    protected $fillable = [
        'tenant_id',
        'vehicle_id',
        'warehouse_id',
        'requested_by',
        'status',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(VanLoadItem::class);
    }
}

class VanLoadItem extends BaseTenantModel
{
    protected $fillable = [
        'van_load_id',
        'product_id',
        'quantity',
    ];
}

class VanInventory extends BaseTenantModel
{
    protected $table = 'van_inventory';
    
    protected $fillable = [
        'tenant_id',
        'vehicle_id',
        'product_id',
        'quantity',
    ];
}
