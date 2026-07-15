<?php

namespace App\Domains\Product\Models;

use App\Domains\Shared\Models\BaseTenantModel;

class Brand extends BaseTenantModel
{
    protected $fillable = ['tenant_id', 'name', 'slug'];
}
