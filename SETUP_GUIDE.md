# FoodERP Pro Enterprise AI Platform - Setup & Deployment Guide

This guide provides step-by-step instructions to initialize and run the **FoodERP Pro Enterprise AI Platform** in your local development environment.

## 1. Prerequisites

Before you begin, ensure you have the following installed:
- **Docker & Docker Compose** (Recommended for easiest setup)
- **Node.js 20+** and **pnpm** (for the frontend)
- **PHP 8.3** and **Composer** (if running without Docker)
- **MySQL 8.4** and **Redis** (provided by Docker Compose)

---

## 2. Option A: Rapid Deployment with Docker (Recommended)

This is the fastest way to get the entire ecosystem (Backend, Frontend, DB, Redis) running.

### Step 1: Clone and Enter Project Directory
```bash
git clone <repository-url> foodERP
cd foodERP
```

### Step 2: Configure Environment Variables
Copy the example environment files:
```bash
cp backend/.env.example backend/.env
cp frontend/.env.local.example frontend/.env.local
```
*Note: Edit `backend/.env` to add your AI Provider API keys (Gemini, Groq, etc.).*

### Step 3: Spin Up Containers
```bash
docker compose -f docker/docker-compose.yml up --build -d
```

### Step 4: Initialize Database & Seed Data
```bash
docker compose -f docker/docker-compose.yml ps
docker compose -f docker/docker-compose.yml exec backend php artisan key:generate
docker compose -f docker/docker-compose.yml exec backend php artisan jwt:secret
docker compose -f docker/docker-compose.yml exec backend php artisan migrate --seed
```

---

## 3. Option B: Manual Local Setup

If you prefer to run services natively on your machine.

### Step 1: Backend Initialization (Laravel 12)
```bash
cd backend
composer install
cp .env.example .env
# Update .env with your local DB_HOST, DB_PASSWORD, etc.
php artisan key:generate
php artisan jwt:secret
php artisan migrate:fresh --seed
php artisan serve --port=8000
```

### Step 2: Frontend Initialization (Next.js 15)
```bash
cd ../frontend
pnpm install
cp .env.local.example .env.local
pnpm dev
```

---

## 4. Environment Configuration Templates

### Backend (`backend/.env`)
Ensure these core values are set for the AI and Tenant systems to work:

| Key | Description |
|-----|-------------|
| `DB_CONNECTION` | Must be `mysql` in Docker |
| `GEMINI_API_KEY` | Primary AI Insight engine key |
| `GROQ_API_KEY` | First failover provider |
| `JWT_SECRET` | Used for multi-tenant secure auth |

### Frontend (`frontend/.env.local`)
| Key | Description |
|-----|-------------|
| `NEXT_PUBLIC_API_URL` | Use `/api` so the browser reaches Laravel through Nginx |
| `NEXT_PUBLIC_APP_URL` | Public application URL (`http://localhost`) |
| `NEXT_PUBLIC_DEFAULT_TENANT` | The domain slug for the seeded tenant (default: `enterprise`) |

---

## 5. Testing the Ecosystem

Once everything is running, you can access the platform at:
- **Login Page**: `http://localhost/enterprise/login`
- **Default Credentials**: 
  - **Email**: `admin@fooderp.pro`
  - **Password**: `password`

### Key Modules to Verify:
1. **Executive Dashboard**: `http://localhost:3000/enterprise/analytics` (Check AI Insights widget).
2. **Van Sales**: `http://localhost:3000/enterprise/sales/new` (Mobile-responsive UI).
3. **Inventory**: `http://localhost:3000/enterprise/inventory` (FEFO batch tracking).
4. **Statement of Account**: `http://localhost:3000/enterprise/customers/1/statement`.

---

## 6. Troubleshooting
- **Database Connection**: In Docker use `DB_HOST=db`, `DB_PORT=3306`, and the MySQL credentials from `docker/docker-compose.yml`.
- **Internal API URL**: Backend-to-backend calls use `API_BASE_URL=http://backend:8000/api`; browser calls use `NEXT_PUBLIC_API_URL=/api`.
- **CORS Issues**: The browser uses the same public origin through Nginx (`http://localhost`), so no direct `localhost:3000` API URL is required.
- **AI Insights Empty**: Verify that `GEMINI_API_KEY` is valid and that you have seeded the database to provide data context for the AI.

---

**Congratulations!** You are now running the most advanced AI-powered ERP for food distribution. For further support, refer to the individual `Step_X_Output.md` files in the root directory.
