# FoodERP Pro Enterprise AI Platform - Step 1: System Directory Architecture, Tenant Setup, and Database Migrations

This document details the foundational work completed for **Step 1** of the FoodERP Pro Enterprise AI Platform. We have established the core system directory architecture, implemented multi-tenancy mechanisms, defined essential database migrations, and provided initial Docker configurations for deployment.

## 1. System Architecture Overview

The FoodERP Pro platform is designed with a clear separation of concerns, utilizing a **Laravel 12** backend for business logic and API services, and a **Next.js 15** frontend for the user interface. The architecture adheres to **Domain-Driven Design (DDD)** and **Clean Architecture** principles, ensuring modularity, scalability, and maintainability. A multi-tenant approach is implemented using a shared database schema.

## 2. Directory Structures

### 2.1. Root Project Directory

The project is organized into a root `foodERP/` directory containing the backend, frontend, Docker configurations, and documentation:

```
foodERP/
├── backend/             # Laravel 12 application
├── frontend/            # Next.js 15 application
├── docker/              # Docker-related files (docker-compose.yml, Dockerfiles)
├── docs/                # Project documentation (e.g., API docs, architecture diagrams)
└── README.md
```

### 2.2. Backend (Laravel 12) - Domain-Driven Design & Clean Architecture

The Laravel backend is structured around bounded contexts, with each domain (`Tenant`, `User`, `Product`, `Inventory`, `Sales`, `Vehicle`, `Commission`, `Analytics`) having its dedicated directory for actions, DTOs, models, policies, providers, services, and value objects. Infrastructure concerns like persistence and external services are also clearly separated.

```
backend/
├── app/
│   ├── Console/
│   ├── Exceptions/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/      # Multi-tenancy middleware
│   │   └── Requests/
│   ├── Providers/
│   ├── Domains/             # Bounded Contexts / Domain Modules
│   │   ├── Shared/          # Common interfaces, traits, services
│   │   ├── Tenant/          # Tenant management domain
│   │   │   ├── Actions/
│   │   │   ├── DataTransferObjects/
│   │   │   ├── Models/
│   │   │   ├── Policies/
│   │   │   ├── Providers/
│   │   │   ├── Services/
│   │   │   └── ValueObjects/
│   │   ├── User/            # User management domain
│   │   ├── Product/         # Product management domain
│   │   ├── Inventory/       # Inventory management domain
│   │   ├── Sales/           # Sales (Invoices, Collections) domain
│   │   ├── Vehicle/         # Vehicle and GPS tracking domain
│   │   ├── Commission/      # Sales commission domain
│   │   └── Analytics/       # Arabic NLP Analytics domain
│   ├── Infrastructure/      # Infrastructure concerns (e.g., external services, repositories implementations)
│   │   ├── Persistence/
│   │   │   ├── Eloquent/
│   │   │   └── Migrations/
│   │   ├── ExternalServices/
│   │   │   ├── GPSProvider/
│   │   │   └── LLMFailover/
│   │   └── Providers/
│   └── Application/         # Application layer services (orchestrates domains)
│       ├── Commands/
│       ├── Queries/
│       └── Services/
├── bootstrap/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/          # Central migrations for core system (e.g., `tenants` table)
│   ├── seeders/
│   └── uuid_functions.sql   # PostgreSQL UUID generation functions
├── public/
├── resources/
├── routes/
├── storage/
├── tests/
├── vendor/
└── .env
```

### 2.3. Frontend (Next.js 15)

The Next.js frontend leverages the App Router and is organized into logical routes and reusable components. `shadcn/ui` is integrated for a consistent and modern user interface.

```
frontend/
├── app/                     # Next.js App Router
│   ├── (auth)/
│   │   ├── login/
│   │   └── register/
│   ├── (dashboard)/
│   │   ├── [tenantId]/
│   │   │   ├── layout.tsx
│   │   │   ├── page.tsx
│   │   │   ├── products/
│   │   │   ├── sales/
│   │   │   └── analytics/
│   ├── api/                 # API routes (if any server-side logic is needed)
│   ├── layout.tsx
│   └── page.tsx
├── components/              # Reusable React components (UI, business logic-agnostic)
│   ├── ui/                  # shadcn/ui components
│   └── custom/
├── lib/                     # Utility functions, helpers, API clients
│   ├── api/
│   ├── hooks/
│   └── utils.ts
├── public/
├── styles/
├── types/
├── .env
└── next.config.js
```

## 3. Multi-Tenancy Implementation

Multi-tenancy is implemented using a shared database schema approach, where a `tenant_id` column is present on all tenant-specific tables. Tenant identification is primarily done via subdomain or `X-Tenant-ID` request header.

- **`TenantMiddleware.php`**: This middleware intercepts incoming requests, identifies the tenant based on the domain or header, and binds the `Tenant` model instance to the application's service container. This ensures that all subsequent operations are tenant-scoped.
- **`Tenant.php` Model**: Represents the tenant entity, storing tenant-specific information like name, domain, and settings.
- **`BaseTenantModel.php`**: An abstract base model that all tenant-scoped models will extend. It automatically applies a global scope to filter queries by the current tenant's ID and sets the `tenant_id` during model creation.
- **`TenantSetupService.php`**: A service responsible for creating new tenants and initializing their default resources, such as an 'Administrator' role.

## 4. Database Migrations

All database tables utilize UUIDs as primary keys. Migrations are structured to support the multi-tenant architecture and cover core ERP functionalities:

- **`2026_07_11_000001_create_tenants_table.php`**: Creates the central `tenants` table.
- **`2026_07_11_000002_create_users_and_roles_tables.php`**: Creates `roles` and `users` tables, with `tenant_id` for tenant-specific users/roles and `nullable` for super-admins.
- **`2026_07_11_000003_create_products_and_inventory_tables.php`**: Defines `categories`, `products`, `warehouses`, and `inventory_stocks` tables, all tenant-scoped.
- **`2026_07_11_000004_create_sales_and_invoices_tables.php`**: Includes `customers`, `invoices`, `invoice_items`, and `collections` tables, linked to tenants and users.
- **`2026_07_11_000005_create_vehicles_and_gps_logs_tables.php`**: Sets up `vehicles`, `gps_engine_logs`, and `fuel_logs` tables for vehicle tracking, including the necessary `tenant_id`.
- **`2026_07_11_000006_create_commissions_and_fraud_tables.php`**: Creates `commission_tiers`, `commissions`, `fraud_alerts`, and `expenses` tables, supporting advanced features like sales commission calculations and anomaly detection.

## 5. Docker Compose and Deployment Configuration

Docker is used to containerize the application, providing a consistent development and production environment. The `docker-compose.yml` orchestrates the following services:

- **`backend`**: Laravel 12 application, built from `backend.Dockerfile`.
- **`frontend`**: Next.js 15 application, built from `frontend.Dockerfile`.
- **`db`**: PostgreSQL 16 database, with persistent data volume.
- **`redis`**: Redis 7 for caching and queues.

### Local Deployment Instructions

To set up and run the project locally using Docker:

1.  **Navigate to the `foodERP` directory:**
    ```bash
    cd /home/ubuntu/foodERP
    ```
2.  **Build and start the Docker containers:**
    ```bash
    docker-compose -f docker/docker-compose.yml up --build -d
    ```
3.  **Access the backend container and install Laravel dependencies (if not already done by Dockerfile):**
    ```bash
    docker exec -it fooderp_backend composer install
    ```
4.  **Run database migrations:**
    ```bash
    docker exec -it fooderp_backend php artisan migrate
    ```
5.  **Generate application key:**
    ```bash
    docker exec -it fooderp_backend php artisan key:generate
    ```
6.  **The backend will be accessible at `http://localhost:8000` and the frontend at `http://localhost:3000`.**

This completes Step 1, providing a robust and well-structured foundation for the FoodERP Pro Enterprise AI Platform. Please review the generated files and confirm before proceeding to the next steps.
