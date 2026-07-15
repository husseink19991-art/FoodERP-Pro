<?php

namespace App\Domains\Shared\Models;

use App\Domains\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class BaseTenantModel extends Model
{
    use HasUuids, SoftDeletes;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'tenant_id',
    ];

    /**
     * Boot the model and apply global tenant scope.
     */
    protected static function booted()
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (app()->bound(Tenant::class)) {
                $tenant = app(Tenant::class);
                $builder->where($builder->getQuery()->from . '.tenant_id', $tenant->id);
            }
        });

        static::creating(function ($model) {
            if (app()->bound(Tenant::class)) {
                $model->tenant_id = app(Tenant::class)->id;
            }
        });
    }

    /**
     * Scope a query to only include models of a given tenant.
     * Useful for super-admin operations that need to bypass global scope temporarily.
     */
    public function scopeForTenant(Builder $query, string $tenantId): Builder
    {
        return $query->withoutGlobalScope('tenant')->where('tenant_id', $tenantId);
    }
}
