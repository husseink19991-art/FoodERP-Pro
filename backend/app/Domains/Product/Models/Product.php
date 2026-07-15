<?php

namespace App\Domains\Product\Models;

use App\Domains\Shared\Models\BaseTenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends BaseTenantModel
{
    protected $fillable = [
        'tenant_id',
        'category_id',
        'brand_id',
        'base_unit_id',
        'sku',
        'barcode',
        'qrcode',
        'name',
        'description',
        'price',
        'cost',
        'reorder_level',
        'attributes',
    ];

    protected $casts = [
        'attributes' => 'array',
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function baseUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    public function unitConversions(): HasMany
    {
        return $this->hasMany(ProductUnitConversion::class);
    }

    public function priceLists(): HasMany
    {
        return $this->hasMany(ProductPriceList::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(ProductBatch::class);
    }
}
