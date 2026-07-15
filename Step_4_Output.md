# FoodERP Pro Enterprise AI Platform - Step 4: Van Sales, Mobile Invoicing & Inventory Reconciliation

This document details the implementation of **Step 4** for the FoodERP Pro Enterprise AI Platform, focusing on the critical functionalities for managing van sales operations, mobile invoicing, and end-of-day reconciliation.

## 1. Van Loading & Stock Transfers

This module provides the logic for transferring products from the main warehouse to specific sales vans, ensuring accurate inventory tracking and availability.

### 1.1. `VanInventoryService.php`

Located at `backend/app/Domains/Inventory/Services/VanInventoryService.php`, this service orchestrates the process of loading vans:

-   **`approveVanLoad(string $vanLoadId)`**: This method is responsible for approving a pending `VanLoad` request. It iterates through the requested items and calls `transferToVan` for each product. It ensures that the `VanLoad` status is updated to `completed` upon successful transfer.
-   **`transferToVan(string $tenantId, string $vehicleId, string $warehouseId, string $productId, int $quantity, string $loadId)`**: This protected method handles the actual stock transfer. It deducts the specified quantity from the main warehouse batches using a **FEFO (First Expiry, First Out)** strategy and then updates the `van_inventory` for the respective vehicle and product. It also records these movements in the `batch_movements` table for traceability.

### 1.2. Models

-   **`VanLoad.php`**: (`backend/app/Domains/Inventory/Models/VanLoad.php`) Represents a request to load a van with products. It tracks the `vehicle_id`, `warehouse_id`, `requested_by` (sales representative), and the `status` of the load (pending, approved, rejected, completed).
-   **`VanLoadItem.php`**: (`backend/app/Domains/Inventory/Models/VanLoad.php`) Details the products and quantities requested in a specific `VanLoad`.
-   **`VanInventory.php`**: (`backend/app/Domains/Inventory/Models/VanLoad.php`) Tracks the current stock levels of each product within a specific van. This table is crucial for real-time inventory checks during mobile invoicing.

## 2. Van Mobile-Friendly Invoicing Engine

This engine provides API endpoints for sales representatives to create invoices directly from their mobile devices, with immediate deduction from their van's inventory.

### 2.1. `VanSalesService.php`

Located at `backend/app/Domains/Sales/Services/VanSalesService.php`, this service handles the core logic for processing van sales:

-   **`createVanInvoice(array $data, string $vehicleId, string $salesRepId)`**: This method processes a new sales invoice. It creates an `Invoice` record, processes each `InvoiceItem`, and then calls `deductFromVan` to update the van's inventory. It also handles updating the `customers.current_balance` and recording entries in the `debts` table for credit sales.
-   **`deductFromVan(string $tenantId, string $vehicleId, string $productId, int $quantity)`**: This protected method is responsible for deducting the sold quantity from the `van_inventory` table. It includes a check for sufficient stock in the van and ensures the operation is performed within a database transaction.

## 3. End-of-Day Reconciliation (Settlement) Service

This service provides the core logic for reconciling van sales and inventory at the end of each day, identifying any discrepancies.

### 3.1. `VanReconciliationService.php`

Located at `backend/app/Domains/Sales/Services/VanReconciliationService.php`, this service facilitates the settlement process:

-   **`generateReconciliationData(string $tenantId, string $vehicleId, string $salesRepId, string $date)`**: This method compiles a summary of sales for a given sales representative and vehicle on a specific date. It calculates total sales, cash collected, credit sales, and lists the remaining items in the van. This data is then presented to the administrator for review.
-   **`finalizeReconciliation(array $data)`**: This method records the final reconciliation details in the `van_reconciliations` table. It captures reported sales, collected amounts, and any calculated variance. If a significant variance is detected, it automatically triggers a `fraud_alert` to flag potential issues.

## 4. Next.js 15 Frontend Views

The frontend components provide intuitive and mobile-responsive interfaces for both salesmen and administrators.

### 4.1. Salesman Mobile-Friendly Invoice Creation Screen (`frontend/app/(dashboard)/[tenantId]/sales/new/page.tsx`)

This page is designed for mobile use by sales representatives to create new invoices:

-   **Two-Step Process**: Guides the user through selecting products and quantities, then specifying customer details and payment type (cash or credit).
-   **Product Selection**: Displays available products in the van, allowing salesmen to easily add or remove items and adjust quantities. It includes real-time stock checks.
-   **Order Summary**: Provides a clear summary of the items in the cart and the total amount.
-   **Responsive Design**: Optimized for mobile devices, ensuring ease of use in the field.

### 4.2. Admin/Accountant Interface for Van Loading and Settlement Approval (`frontend/app/(dashboard)/[tenantId]/sales/settlement/page.tsx`)

This interface is for administrative users to manage van loading requests and approve end-of-day reconciliations:

-   **Overview Cards**: Displays key metrics such as pending approvals, total daily cash collected, and overall inventory variance.
-   **Settlement Table**: Lists all pending and verified settlements, showing details like date, sales representative, vehicle, total sales, cash/credit breakdown, and variance.
-   **Actionable Items**: Allows administrators to view details of each settlement and approve pending ones. Discrepancies are highlighted for immediate attention.
-   **Search and Filter**: Provides functionality to search and filter settlements for easier management.

## 5. Instructions for Local Deployment and Testing

To test the implemented van sales and reconciliation features:

1.  **Ensure Docker containers are running and updated:**
    ```bash
    cd /home/ubuntu/foodERP
    docker-compose -f docker/docker-compose.yml up --build -d
    ```
2.  **Access the backend container and run the new migrations:**
    ```bash
    docker exec -it fooderp_backend php artisan migrate
    ```
3.  **Manually create sample data using `php artisan tinker`:**
    -   Create a `Vehicle` and assign a `User` (sales representative) to it.
    -   Create `VanLoad` requests with `VanLoadItem`s.
    -   Approve `VanLoad` requests using the `VanInventoryService` to populate `van_inventory`.
    -   Simulate sales using the `VanSalesService` to create invoices and deduct from `van_inventory`.
    -   Generate reconciliation data using `VanReconciliationService`.

4.  **Access the frontend application:**
    -   Open your browser and navigate to `http://localhost:3000/<your-tenant-domain>/sales/new` to simulate a salesman creating an invoice.
    -   Navigate to `http://localhost:3000/<your-tenant-domain>/sales/settlement` to view and approve settlements as an administrator.

This concludes Step 4. The system now supports comprehensive van sales operations, from inventory loading to mobile invoicing and end-of-day reconciliation, with built-in mechanisms for discrepancy detection. Please review and confirm before we proceed to the next step.
