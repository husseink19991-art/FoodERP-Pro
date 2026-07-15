# FoodERP Pro Enterprise AI Platform - Step 3: Core Products, Inventory Management & Expiry Tracking

This document outlines the implementation of **Step 3** for the FoodERP Pro Enterprise AI Platform, covering the core product management, multi-level pricing, and advanced inventory tracking functionalities, along with their corresponding frontend interfaces.

## 1. Product Management Module

The product management module has been enhanced to support comprehensive product definitions, including categories, brands, unique identifiers, and unit conversion logic.

### 1.1. Database Migrations

A new migration, `2026_07_11_000009_update_products_table_for_brands_and_units.php`, introduces the following tables and modifications:

-   **`brands`**: Stores information about product brands (`id`, `tenant_id`, `name`, `slug`).
-   **`units`**: Defines various units of measure (e.g., Box, Piece) with `id`, `tenant_id`, `name`, `short_name`.
-   **`products` table modifications**: Added `brand_id`, `base_unit_id` (foreign keys to `brands` and `units` respectively), `barcode`, and `qrcode` fields.
-   **`product_unit_conversions`**: Manages conversion factors between different units for a product (e.g., 1 Box = 12 Pieces). It links `products` to `units` with a `conversion_factor`.

### 1.2. `Product.php` Model

The `Product` model (`backend/app/Domains/Product/Models/Product.php`) has been updated with relationships to `Category`, `Brand`, `Unit` (for `baseUnit`), `ProductUnitConversion`, `ProductPriceList`, and `ProductBatch` models, enabling a rich product definition.

## 2. Price Lists Engine

The multi-level pricing engine allows for flexible pricing configurations, supporting different price types like wholesale, retail, and custom cost structures.

### 2.1. Database Migrations

The `2026_07_11_000010_create_price_lists_table.php` migration introduces:

-   **`price_lists`**: Defines different types of price lists (e.g., Wholesale, Retail, VIP) with `id`, `tenant_id`, `name`, and `is_default`.
-   **`product_price_lists`**: Stores the specific price of a `product` within a given `price_list`, linking `products` to `price_lists` with a `price` field.

## 3. Batch & Expiry Tracking System (Main Warehouse)

This system implements robust inventory management with FIFO/FEFO logic, batch tracking, and automated expiration alerts.

### 3.1. Database Migrations

The `2026_07_11_000011_create_product_batches_table.php` migration adds:

-   **`product_batches`**: Tracks individual batches of products, including `product_id`, `warehouse_id`, `batch_number`, `initial_quantity`, `current_quantity`, `manufacturing_date`, `expiry_date`, and `unit_cost`. An index on `product_id` and `expiry_date` facilitates FEFO operations.
-   **`batch_movements`**: Logs all inventory movements (in, out, adjustment) for each batch, linking to `product_batches` and providing `reference_type` and `reference_id` for traceability.

### 3.2. `InventoryService.php`

The `InventoryService` (`backend/app/Domains/Inventory/Services/InventoryService.php`) provides core logic for inventory operations:

-   **`deductStock(Product $product, int $quantity, string $referenceType, string $referenceId)`**: Implements **FEFO (First Expiry, First Out)** logic. It identifies and deducts stock from batches with the earliest expiry dates first. It also records `batch_movements` for each deduction.
-   **`getExpiringBatches(int $daysThreshold = 30)`**: Retrieves batches that are expiring within a specified `daysThreshold`, enabling automated expiration alert systems.

## 4. Next.js 15 Frontend Views

Frontend views provide intuitive interfaces for managing products and monitoring inventory status.

### 4.1. Product Catalog (`frontend/app/(dashboard)/[tenantId]/products/page.tsx`)

This page offers a comprehensive view of all products, featuring:

-   **Product Listing**: Displays product name, SKU, barcode, category, current stock, and price.
-   **Search and Filter**: Allows users to search for products by various criteria.
-   **Key Metrics Cards**: Provides quick insights into total products, low stock items, and active categories.
-   **Actions**: Dropdown menus for actions like 
viewing details, editing, and managing batches.

### 4.2. Stock Alert / Reorder Point Interface (`frontend/app/(dashboard)/[tenantId]/inventory/page.tsx`)

This page serves as the central hub for inventory management, including batch status and stock alerts:

-   **Inventory Overview Cards**: Displays critical metrics such as critical expiry items, expiring soon items, total stock value, and monthly inventory turnover.
-   **Active Batches Table**: Presents a detailed table of active product batches, showing batch number, product name, current stock level (with a progress bar), expiry date, and status (Critical, Soon, Good).
-   **Stock Alerts Section**: Highlights items that are below their reorder threshold, providing quick access to create purchase orders for replenishment.
-   **Movement Logs**: A button to access detailed inventory movement logs (future implementation).

## 5. Instructions for Local Deployment and Testing

To test the implemented product management and inventory tracking features:

1.  **Ensure Docker containers are running and updated:**
    ```bash
    cd /home/ubuntu/foodERP
    docker-compose -f docker/docker-compose.yml up --build -d
    ```
2.  **Access the backend container and run the new migrations:**
    ```bash
    docker exec -it fooderp_backend php artisan migrate
    ```
3.  **Manually create some sample data for products, brands, units, price lists, and batches using `php artisan tinker` to populate the database and test the frontend views.**
    -   Example for Brand:
        ```php
        App\Domains\Product\Models\Brand::create([\"tenant_id\" => \"<your-tenant-uuid>\", \"name\" => \"FoodMaster\", \"slug\" => \"foodmaster\"]);
        ```
    -   Example for Unit:
        ```php
        App\Domains\Product\Models\Unit::create([\"tenant_id\" => \"<your-tenant-uuid>\", \"name\" => \"Bag\", \"short_name\" => \"bag\"]);
        ```
    -   Example for Product (requires category, brand, base unit UUIDs):
        ```php
        App\Domains\Product\Models\Product::create([\"tenant_id\" => \"<your-tenant-uuid>\", \"category_id\" => \"<category-uuid>\", \"brand_id\" => \"<brand-uuid>\", \"base_unit_id\" => \"<unit-uuid>\", \"sku\" => \"RICE-BAS-001\", \"name\" => \"Premium Basmati Rice 5kg\", \"price\" => 15.50, \"cost\" => 10.00, \"reorder_level\" => 20]);
        ```
    -   Example for Product Batch (requires product, warehouse UUIDs):
        ```php
        App\Domains\Product\Models\ProductBatch::create([\"tenant_id\" => \"<your-tenant-uuid>\", \"product_id\" => \"<product-uuid>\", \"warehouse_id\" => \"<warehouse-uuid>\", \"batch_number\" => \"BN-2024-001\", \"initial_quantity\" => 100, \"current_quantity\" => 50, \"expiry_date\" => \"2024-12-15\", \"unit_cost\" => 10.00]);
        ```

4.  **Access the frontend application:**
    -   Open your browser and navigate to `http://localhost:3000/<your-tenant-domain>/products` to view the Product Catalog.
    -   Navigate to `http://localhost:3000/<your-tenant-domain>/inventory` to view the Batch Status and Stock Alerts.

This concludes Step 3. The core product and inventory management functionalities are now in place, providing a robust system for tracking goods and managing stock levels. Please review and confirm before we proceed to the next step.
