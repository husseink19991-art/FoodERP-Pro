# FoodERP Pro - Docker Setup Guide

## Overview
This Docker setup provides a complete containerized environment for FoodERP Pro including:
- **Backend**: Laravel 12 PHP API
- **Frontend**: Next.js 15 React app
- **Database**: MySQL 8.4
- **Cache**: Redis 7
- **Proxy**: Nginx 1.27

## Fixed Issues

### 1. ✅ Security & Configuration
- [x] Moved all secrets to `.env.docker` file (never commit secrets to git)
- [x] Added input validation for domain parameter
- [x] Implemented rate limiting on login endpoint (5 attempts/minute)
- [x] Fixed null safety with optional chaining (`$user->role?->slug`)
- [x] Eager loading with `->with('role')` to prevent N+1 queries

### 2. ✅ Docker Images
- [x] **Backend**: Multi-stage build to reduce image size
  - Copies composer.lock for deterministic builds
  - Optimized with `--optimize-autoloader --no-dev`
  - Reduced FROM alpine size
- [x] **Frontend**: Multi-stage build
  - Created `next.config.js` with `output: 'standalone'`
  - Uses npm ci or npm install (fallback)
  - Non-root `nextjs` user for security
- [x] Created `.dockerignore` files for both backend and frontend

### 3. ✅ Docker Compose
- [x] Removed exposed ports for backend/frontend (only nginx on 80)
- [x] Added `env_file: .env.docker` for secrets
- [x] Added network: `fooderp-network` for service communication
- [x] Optimized health checks (reduced retries)
- [x] Added `restart: unless-stopped` policy

### 4. ✅ Nginx Configuration
- [x] Removed redundant `location = /api` exact match
- [x] Consolidated to `/api/` prefix routing
- [x] Added `proxy_buffering off` for real-time responses
- [x] Fixed WebSocket support headers

### 5. ✅ Frontend API Handling
- [x] Created `lib/api/client.ts` with:
  - Comprehensive error handling (ApiError class)
  - Token injection from localStorage
  - Network error detection
  - Helper functions: `login()`, `logout()`, `getCurrentUser()`, `checkHealth()`
- [x] Created `lib/hooks/useApi.ts` with:
  - `useApi()` hook for data fetching
  - `useApiMutation()` hook for forms/mutations
  - Proper loading and error states

## Getting Started

### 1. Setup Environment
```bash
cd docker
cp .env.docker .env.docker
# Edit .env.docker and change passwords to secure values:
# - DB_PASSWORD
# - MYSQL_ROOT_PASSWORD
```

### 2. Build & Start Services
```bash
cd docker
docker-compose up -d --build
```

### 3. Verify Services
```bash
# Check all containers are running
docker-compose ps

# Check backend health
docker-compose logs backend

# Check frontend build
docker-compose logs frontend

# Access the app
# http://localhost  (via nginx)
# http://localhost:8000  (backend direct, if exposed)
# http://localhost:3000  (frontend direct, if exposed)
```

### 4. Initialize Database (if needed)
```bash
docker-compose exec backend php artisan migrate --force
docker-compose exec backend php artisan seed --force
```

## API Usage

### Login Example
```typescript
import { login, ApiError } from '@/lib/api/client';

try {
  const response = await login('user@example.com', 'password', 'domain.local');
  console.log('User:', response.user);
} catch (error) {
  if (error instanceof ApiError) {
    console.error('Login failed:', error.message, error.status);
  }
}
```

### Using the useApi Hook in Components
```typescript
import { useApi } from '@/lib/hooks/useApi';

export function MyComponent() {
  const { data, loading, error, execute } = useApi<UserData>(null);

  const handleFetch = async () => {
    try {
      await execute('/auth/me');
    } catch (err) {
      console.error('Failed to fetch user:', err);
    }
  };

  if (loading) return <div>Loading...</div>;
  if (error) return <div>Error: {error.message}</div>;
  if (!data) return <button onClick={handleFetch}>Fetch User</button>;

  return <div>User: {data.name}</div>;
}
```

## Troubleshooting

### Build Issues
- **Backend composer timeout**: Increase timeout or run `docker-compose build backend --no-cache`
- **Frontend package-lock missing**: Dockerfile now handles both npm ci and npm install

### Runtime Issues
- **Health checks failing**: Check logs with `docker-compose logs <service>`
- **Database connection refused**: Verify db service is healthy: `docker-compose ps`
- **API not accessible**: Check nginx config is loaded: `docker exec fooderp_nginx nginx -t`

### Logs
```bash
# View all logs
docker-compose logs -f

# View specific service
docker-compose logs -f backend
docker-compose logs -f frontend
docker-compose logs -f db
```

## Development Tips

### Hot Reload Frontend
The frontend volume is mounted, so changes to files will be reflected. Next.js has built-in hot reload.

### Hot Reload Backend
The backend volume is mounted. For PHP changes:
- Routes and config: auto-refresh on next request
- Service providers: may need restart
- Migrations: run manually

### Database Migrations
```bash
docker-compose exec backend php artisan migrate
docker-compose exec backend php artisan seed
```

### Shell Access
```bash
# Backend shell
docker-compose exec backend sh

# Frontend shell
docker-compose exec frontend sh

# Database shell
docker-compose exec db mysql -u fooderp_user -p fooderp
```

## Production Deployment

Before deploying to production:
1. ✅ Use strong, unique passwords (generate with `openssl rand -base64 32`)
2. ✅ Set `APP_ENV=production` in `.env.docker`
3. ✅ Set `APP_DEBUG=false` in `.env.docker`
4. ✅ Use external database (not containerized)
5. ✅ Set up proper backups for mysql_data volume
6. ✅ Configure HTTPS with Let's Encrypt
7. ✅ Set up monitoring and logging
8. ✅ Use secrets management (Docker Secrets, AWS Secrets Manager, etc.)

## Removed Issues Summary

| Issue | Fix |
|-------|-----|
| Hardcoded secrets in compose | Moved to `.env.docker` + `env_file:` |
| No brute force protection | Added rate limiting in AuthController |
| N+1 query problem | Eager loading with `->with('role')` |
| Exposed backend ports | Removed, only nginx on :80 |
| Large images | Multi-stage builds + `.dockerignore` |
| No API error handling | Created client.ts with error classes |
| Missing .dockerignore | Added for both services |
| Direct port exposure | All traffic through nginx |
| Inefficient health checks | Optimized intervals and retries |

