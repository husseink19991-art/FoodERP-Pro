# FoodERP Pro Enterprise AI Platform - Step 5: Customer & Supplier Debt Management, Collections & Payments (AR/AP)

This document details the implementation of **Step 5** for the FoodERP Pro Enterprise AI Platform, focusing on robust customer and supplier debt management, collections, and payments functionalities. This includes ledger tracking, voucher processing, automated balance updates, and corresponding frontend interfaces.

## 1. Customer & Supplier Ledgers

The system now includes comprehensive ledger capabilities to track balances, credit limits, payment terms, and historical transactions for both customers and suppliers.

### 1.1. Database Migrations

A new migration, `2026_07_11_000012_update_entities_for_debt_management.php`, introduces the following enhancements:

-   **`customers` table modifications**: Added `credit_limit`, `payment_terms_days` (e.g., 0 for Cash, 30 for Net 30), and `is_blocked` fields to manage customer credit and payment behavior.
-   **`suppliers` table modifications**: Added `credit_limit` and `payment_terms_days` fields to manage supplier payment terms.
-   **`ledger_entries` table**: A new table to store a detailed, immutable log of all financial transactions affecting customer and supplier balances. It includes `entity_type` (customer/supplier), `entity_id`, `transaction_type` (invoice, purchase, receipt, payment, adjustment), `reference_id`, `debit`, `credit`, `balance_after`, and `transaction_date`.

### 1.2. Models

-   **`Customer.php`**: (`backend/app/Domains/Sales/Models/Customer.php`) Updated to include the new `credit_limit`, `payment_terms_days`, and `is_blocked` attributes. It also defines a `hasMany` relationship to `ledgerEntries` for easy access to its transaction history.
-   **`Supplier.php`**: (`backend/app/Domains/Sales/Models/Supplier.php`) Updated with `credit_limit` and `payment_terms_days` attributes, and a `hasMany` relationship to `ledgerEntries`.
-   **`LedgerEntry.php`**: (`backend/app/Domains/Sales/Models/LedgerEntry.php`) A new model representing individual entries in the financial ledger, ensuring a clear audit trail for all balance changes.

## 2. Receipt & Payment Vouchers Engine (سندات القبض والصرف)

This engine handles the processing of cash/check collections from customers and payments made to suppliers, with robust allocation capabilities.

### 2.1. Database Migrations

The `2026_07_11_000013_create_vouchers_table.php` migration introduces:

-   **`vouchers`**: Stores details of all receipt and payment vouchers. It includes `type` (receipt/payment), `voucher_number`, `entity_type` (customer/supplier), `entity_id`, `amount`, `payment_method` (cash, check, bank_transfer), `reference_number`, `voucher_date`, `notes`, and `created_by`.
-   **`voucher_allocations`**: Links `vouchers` to specific `invoices` (for receipts) or `purchases` (for payments), detailing how the voucher amount is allocated.

### 2.2. `DebtService.php`

Located at `backend/app/Domains/Sales/Services/DebtService.php`, this service contains the core logic for processing vouchers:

-   **`logReceipt(array $data, string $userId)`**: Processes a customer receipt voucher. It creates a `Voucher` record, decrements the `customer.current_balance`, creates a `LedgerEntry`, and allocates the payment to specific `debts` (invoices) by updating their `remaining_balance`. All operations are wrapped in a database transaction (`DB::transaction`) to ensure atomicity.
-   **`logPayment(array $data, string $userId)`**: Processes a supplier payment voucher. It creates a `Voucher` record, decrements the `supplier.current_balance`, and creates a `LedgerEntry`. This is also wrapped in a `DB::transaction`.

## 3. Automated Balance Update Triggers

Automated logic ensures that customer and supplier balances are updated instantly and accurately with every relevant financial transaction.

-   **Customer Balance Update**: When a credit invoice is issued (as implemented in Step 4 via `VanSalesService`), the `customer.current_balance` is incremented, and a corresponding `debt` entry is created. When a receipt voucher is logged via `DebtService::logReceipt`, the `customer.current_balance` is decremented, and the `debts.remaining_balance` for allocated invoices is reduced.
-   **Supplier Balance Update**: When a purchase is made (future implementation), the `supplier.current_balance` will be incremented. When a payment voucher is logged via `DebtService::logPayment`, the `supplier.current_balance` is decremented.
-   **Transaction Safety**: All critical financial operations are enclosed within `DB::transaction` blocks, guaranteeing that either all changes are committed successfully or none are, preventing data corruption.

## 4. Next.js 15 Frontend Views

The frontend provides intuitive interfaces for managing and reporting on debts and payments.

### 4.1. "Statement of Account" (كشف حساب) Interface (`frontend/app/(dashboard)/[tenantId]/customers/[id]/statement/page.tsx`)

This page provides a detailed financial statement for a specific customer:

-   **Balance Summary**: Displays opening balance, total debits, total credits, and the current outstanding balance.
-   **Transaction List**: Presents a chronological list of all ledger entries for the customer, including invoices, receipts, and adjustments, with their respective debit/credit amounts and running balance.
-   **Filtering**: Allows filtering transactions by date range.
-   **Export Options**: Provides options to print or export the statement as a PDF.

### 4.2. Debt Aging Report Dashboard (`frontend/app/(dashboard)/[tenantId]/debts/aging/page.tsx`)

This dashboard offers a comprehensive overview of outstanding customer receivables, categorized by age:

-   **Aging Buckets**: Displays total amounts for current (0-30 days), overdue (31-60 days), overdue (61-90 days), and overdue (90+ days) categories.
-   **Visual Progress**: Uses progress bars to visually represent the proportion of debt in each aging bucket.
-   **Customer-Specific Aging**: Presents a table showing each customer's total balance and its breakdown across different aging periods, along with a calculated risk level.
-   **Actionable Insights**: Provides a direct link to view the Statement of Account for each customer.

### 4.3. Accountant Interface for Collections and Payments (`frontend/app/(dashboard)/[tenantId]/vouchers/new/page.tsx`)

This interface allows accountants to easily log new receipt and payment vouchers:

-   **Voucher Type Selection**: Users can toggle between creating a Receipt Voucher (for customer collections) or a Payment Voucher (for supplier payments).
-   **Entity Selection**: Dynamic selection of either a customer or a supplier based on the voucher type.
-   **Amount and Payment Method**: Fields for entering the transaction amount and selecting the payment method (cash, check, bank transfer).
-   **Notes and References**: Allows for additional details and reference numbers (e.g., check number).
-   **Account Overview**: Displays the selected customer/supplier's current balance, credit limit, and utilization.
-   **Invoice Allocation**: For receipt vouchers, it provides a section to allocate the payment to specific pending invoices, with an option for auto-allocation (e.g., FIFO).

## 5. Instructions for Local Deployment and Testing

To test the implemented debt management and payment functionalities:

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
    -   Create `Customer` and `Supplier` records with credit limits and payment terms.
    -   Create `Invoice` records (credit sales) for customers to generate receivables.
    -   Use the `DebtService` to `logReceipt` for customers and `logPayment` for suppliers.
    -   Verify `customer.current_balance`, `supplier.current_balance`, `debts.remaining_balance`, and `ledger_entries` for accuracy.

4.  **Access the frontend application:**
    -   Open your browser and navigate to `http://localhost:3000/<your-tenant-domain>/customers/<customer-id>/statement` to view a customer's statement of account.
    -   Navigate to `http://localhost:3000/<your-tenant-domain>/debts/aging` to view the Debt Aging Report.
    -   Navigate to `http://localhost:3000/<your-tenant-domain>/vouchers/new` to log new collections or payments.

This concludes Step 5. The FoodERP Pro platform now features a robust and secure system for managing customer and supplier debts, collections, and payments, ensuring financial accuracy and traceability. Please review and confirm before we proceed to the next step.
