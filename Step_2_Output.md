# FoodERP Pro Enterprise AI Platform - Step 2: Authentication & Tenant Control Panel

This document details the implementation of **Step 2** for the FoodERP Pro Enterprise AI Platform, focusing on multi-tenant authentication, database isolation, and the foundational frontend components for login and the dashboard layout.

## 1. Laravel 12 Multi-Tenant Authentication System

The backend authentication system is built on Laravel 12, supporting a multi-tenant architecture with distinct roles: Super Admin, Tenant Admin, Accountant, and Van Salesman. Authentication is handled via API tokens (Sanctum) for stateless communication with the Next.js frontend.

### 1.1. `AuthController.php`

Located at `backend/app/Http/Controllers/Auth/AuthController.php`, this controller manages user login, logout, and retrieval of authenticated user information. Key features include:

-   **Tenant-aware Login**: Users log in by providing their email, password, and the tenant's domain. The system first verifies the tenant's existence and activity before attempting to authenticate the user within that tenant's scope.
-   **API Token Generation**: Upon successful login, a Sanctum API token is generated for the authenticated user.
-   **User Information**: The `me` endpoint provides details about the authenticated user, including their assigned role and tenant information.

### 1.2. `User.php` Model

Found at `backend/app/Domains/User/Models/User.php`, the `User` model extends `BaseTenantModel` and integrates Laravel's authentication traits. It includes:

-   **Tenant Relationship**: A `belongsTo` relationship with the `Tenant` model, linking each user to their respective tenant.
-   **Role Relationship**: A `belongsTo` relationship with the `Role` model, defining the user's permissions.
-   **Permission Checking**: A `hasPermission` method to easily check if a user has a specific permission, supporting wildcard permissions (`*`) for super-admins.

### 1.3. `Role.php` Model

Located at `backend/app/Domains/User/Models/Role.php`, the `Role` model defines the different user roles within the system. It includes:

-   **Tenant Relationship**: Extends `BaseTenantModel` to ensure roles are tenant-scoped.
-   **Permissions**: A `jsonb` field (`permissions`) to store an array of permissions associated with each role, allowing for flexible access control.

## 2. Database Tenant Isolation Logic

Strict tenant isolation is enforced at the database level using Laravel's Eloquent Global Scopes, ensuring that each tenant can only access their own data.

### 2.1. `BaseTenantModel.php`

This abstract model, located at `backend/app/Domains/Shared/Models/BaseTenantModel.php`, is the cornerstone of tenant isolation. All tenant-specific models extend this class, inheriting the following logic:

-   **Global Scope**: A `tenant` global scope is automatically applied to all queries. When a `Tenant` instance is bound to the application container (via `TenantMiddleware`), all queries on models extending `BaseTenantModel` are automatically filtered by the current tenant's `id`.
-   **Automatic `tenant_id` Assignment**: When a new model extending `BaseTenantModel` is created, its `tenant_id` is automatically populated with the `id` of the currently authenticated tenant.
-   **`scopeForTenant` Method**: Provides a way for super-admins or specific system operations to explicitly query data for a given tenant, bypassing the global scope when necessary.

## 3. Next.js 15 Frontend Views

The frontend is built with Next.js 15, utilizing `shadcn/ui` and Tailwind CSS for a modern and responsive user experience.

### 3.1. Login Page

Found at `frontend/app/(auth)/login/page.tsx`, this page provides a clean and functional interface for user authentication. Key aspects include:

-   **Form Validation**: Uses `react-hook-form` and `zod` for robust client-side form validation.
-   **Tenant Domain Input**: Requires users to enter their tenant domain, email, and password.
-   **API Integration (Simulated)**: The current implementation simulates an API call for demonstration purposes. In a production environment, it would interact with the Laravel `AuthController` to authenticate users and receive API tokens.
-   **UI Components**: Leverages `shadcn/ui` components like `Card`, `Input`, and `Button` for a consistent look and feel.

### 3.2. Main Responsive Dashboard Layout (Shell)

Located at `frontend/app/(dashboard)/[tenantId]/layout.tsx`, this component defines the overall structure of the application dashboard. It is designed to be responsive and includes:

-   **Dynamic Navigation**: The sidebar dynamically adjusts based on the `tenantId` parameter in the URL, ensuring tenant-specific routing.
-   **Collapsible Sidebar**: A responsive sidebar that can be toggled open/closed, adapting to different screen sizes.
-   **Navigation Items**: A predefined list of navigation items (`navItems`) for modules like Dashboard, Products, Sales, Inventory, Customers, Debts, Analytics, Fraud Detection, and Settings.
-   **Top Navbar**: Includes a search bar, notification icon, and a user dropdown menu for profile and logout actions.
-   **UI Components**: Extensively uses `shadcn/ui` components such as `Button`, `Input`, `DropdownMenu`, `Avatar`, and `Link` for a cohesive design.

## 4. Instructions for Local Deployment and Testing

To test the implemented authentication and dashboard layout:

1.  **Ensure Docker containers are running:**
    ```bash
    cd /home/ubuntu/foodERP
    docker-compose -f docker/docker-compose.yml up --build -d
    ```
2.  **Access the backend container and run migrations (if not already done):**
    ```bash
    docker exec -it fooderp_backend php artisan migrate
    ```
3.  **Manually create a tenant and a user for testing:**
    -   Access the Laravel shell:
        ```bash
        docker exec -it fooderp_backend php artisan tinker
        ```
    -   Create a tenant:
        ```php
        $tenant = App\Domains\Tenant\Models\Tenant::create(['name' => 'Test Tenant', 'domain' => 'test.fooderp.com', 'is_active' => true]);
        ```
    -   Create an admin role for the tenant:
        ```php
        $role = App\Domains\User\Models\Role::create(['tenant_id' => $tenant->id, 'name' => 'Administrator', 'slug' => 'admin', 'permissions' => ['*']]);
        ```
    -   Create a user for the tenant:
        ```php
        App\Domains\User\Models\User::create(['tenant_id' => $tenant->id, 'role_id' => $role->id, 'name' => 'Test Admin', 'email' => 'admin@test.com', 'password' => Hash::make('password')]);
        ```
    -   Exit tinker: `exit`

4.  **Access the frontend application:**
    -   Open your browser and navigate to `http://localhost:3000`.
    -   On the login page, use `test.fooderp.com` as the Tenant Domain, `admin@test.com` as Email, and `password` as Password.
    -   Upon successful (simulated) login, you should be redirected to `http://localhost:3000/test.fooderp.com/dashboard` and see the dashboard layout.

This concludes Step 2. The multi-tenant authentication and dashboard shell provide a robust foundation for building out the rest of the FoodERP Pro Enterprise AI Platform. Please review and confirm before we proceed.
