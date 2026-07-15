<?php

namespace App\Domains\User\Models;

use App\Domains\Shared\Models\BaseTenantModel;

class Role extends BaseTenantModel
{
    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
