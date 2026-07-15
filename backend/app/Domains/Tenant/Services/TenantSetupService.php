<?php

namespace App\Domains\Tenant\Services;

use App\Domains\Tenant\Models\Tenant;
use App\Domains\Tenant\DataTransferObjects\TenantData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantSetupService
{
    public function createTenant(array $data): Tenant
    {
        return DB::transaction(function () use ($data) {
            $tenant = Tenant::create([
                'name' => $data['name'],
                'domain' => $data['domain'],
                'settings' => $data['settings'] ?? [],
            ]);

            $this->initializeTenantResources($tenant);

            return $tenant;
        });
    }

    protected function initializeTenantResources(Tenant $tenant): void
    {
        // Here we can seed default roles, categories, or warehouses for the new tenant
        // Example: Seed default 'Admin' role
        DB::table('roles')->insert([
            'id' => Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Administrator',
            'slug' => 'admin',
            'permissions' => json_encode(['*']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
