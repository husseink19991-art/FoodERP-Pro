<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Tenant\Models\Tenant;
use App\Domains\User\Models\User;
use App\Domains\Product\Models\Product;
use App\Domains\Product\Models\Category;
use App\Domains\Product\Models\Brand;
use App\Domains\Product\Models\Unit;
use App\Domains\Product\Models\ProductBatch;
use App\Domains\Product\Models\Warehouse;
use App\Domains\Sales\Models\Customer;
use App\Domains\Sales\Models\Invoice;
use Illuminate\Support\Str;

class FoodERPSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create a Tenant
        $tenant = Tenant::create([
            'id' => Str::uuid(),
            'name' => 'FoodERP Pro Enterprise',
            'domain' => 'enterprise',
            'is_active' => true,
        ]);

        // 2. Create Users
        $role = \App\Domains\User\Models\Role::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Administrator',
            'slug' => 'admin',
            'permissions' => ['*'],
        ]);

        $admin = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Admin User',
            'email' => 'admin@fooderp.pro',
            'password' => bcrypt('password'),
            'role_id' => $role->id,
        ]);

        // 3. Create Brands and Categories
        $brand = Brand::create(['tenant_id' => $tenant->id, 'name' => 'FoodMaster', 'slug' => 'foodmaster']);
        $category = Category::create(['tenant_id' => $tenant->id, 'name' => 'Grains', 'slug' => 'grains']);
        $unit = Unit::create(['tenant_id' => $tenant->id, 'name' => 'Bag', 'short_name' => 'bag']);
        $warehouse = Warehouse::create(['tenant_id' => $tenant->id, 'name' => 'Main Warehouse']);

        // 4. Create Products
        $product = Product::create([
            'tenant_id' => $tenant->id,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'base_unit_id' => $unit->id,
            'sku' => 'RICE-BAS-001',
            'name' => 'Premium Basmati Rice 5kg',
            'price' => 15.50,
            'cost' => 10.00,
            'reorder_level' => 20,
        ]);

        // 5. Create Batches
        ProductBatch::create([
            'tenant_id' => $tenant->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'batch_number' => 'BN-2024-001',
            'initial_quantity' => 1000,
            'current_quantity' => 500,
            'expiry_date' => now()->addMonths(6),
            'unit_cost' => 10.00,
        ]);

        // 6. Create Customers
        $customer = Customer::create([
            'tenant_id' => $tenant->id,
            'name' => 'Al-Baraka Supermarket',
            'email' => 'contact@albaraka.com',
            'phone' => '966500000000',
            'credit_limit' => 5000,
            'payment_terms_days' => 30,
        ]);

        // 7. Create Invoices
        Invoice::create([
            'tenant_id' => $tenant->id,
            'customer_id' => $customer->id,
            'sales_rep_id' => $admin->id,
            'invoice_number' => 'INV-88210',
            'total_amount' => 450.00,
            'total_cost' => 300.00,
            'status' => 'pending',
            'payment_type' => 'credit',
        ]);
    }
}
