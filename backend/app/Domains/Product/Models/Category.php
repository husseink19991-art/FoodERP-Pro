<?php

namespace App\Domains\Product\Models;

use App\Domains\Shared\Models\BaseTenantModel;

class Category extends BaseTenantModel
{
    protected $fillable = ['tenant_id', 'name', 'slug'];
}
