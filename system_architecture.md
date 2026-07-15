# FoodERP Pro Enterprise AI Platform - System Architecture

This document outlines the system directory architecture for the FoodERP Pro Enterprise AI Platform, adhering to Domain-Driven Design (DDD) and Clean Architecture principles, with a multi-tenant approach.

## 1. High-Level Architecture

The platform consists of two main applications:

- **Backend (Laravel 12):** Handles business logic, data persistence, API endpoints, and multi-tenancy management.
- **Frontend (Next.js 15):** Provides the user interface, consuming APIs from the backend.

## 2. Directory Structure

### 2.1. Root Project Directory

```
foodERP/
├── backend/             # Laravel 12 application
├── frontend/            # Next.js 15 application
├── docker/              # Docker-related files (docker-compose.yml, Dockerfiles)
├── docs/                # Project documentation (e.g., API docs, architecture diagrams)
└── README.md
```

### 2.2. Backend (Laravel 12) - Domain-Driven Design & Clean Architecture

The Laravel backend will follow a modular structure, where each module represents a bounded context or domain. This approach promotes separation of concerns, maintainability, and scalability.

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

The Next.js frontend will utilize a component-based architecture, leveraging `shadcn/ui` for UI components and a clear separation of concerns.

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

## 3. Multi-Tenancy Setup

Multi-tenancy will be implemented using a hybrid approach:

- **Tenant Identification:** Primarily via subdomain (e.g., `tenant1.fooderp.com`) or a request header.
- **Database Isolation:** A single database with a `tenant_id` column on relevant tables (shared schema approach). Core system tables (e.g., `tenants`, `users` for super-admin) will not have `tenant_id`.

## 4. Database Migrations Strategy

- **Central Migrations:** For core system tables (e.g., `tenants`, `users`, `roles`, `permissions`). These will reside in `backend/database/migrations`.
- **Domain-Specific Migrations:** For tables specific to a domain (e.g., `products`, `invoices`, `vehicles`). These will be managed within their respective `Domains` or `Infrastructure/Persistence/Migrations` directories, but Laravel's migration system will be configured to discover them.

This structure provides a robust foundation for the FoodERP Pro Enterprise AI Platform, ensuring modularity, scalability, and maintainability.
