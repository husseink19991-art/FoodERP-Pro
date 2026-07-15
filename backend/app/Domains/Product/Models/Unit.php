<?php

namespace App\Domains\Product\Models;

use App\Domains\Shared\Models\BaseTenantModel;

class Unit extends BaseTenantModel
{
    protected $fillable = ['tenant_id', 'name', 'short_name'];
}
