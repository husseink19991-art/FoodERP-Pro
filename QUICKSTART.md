# Quick Start Guide - FoodERP Pro

## 🚀 In 5 Minutes

### 1. Verify Local Builds Work
```bash
cd docker
docker-compose build
docker-compose up -d
curl http://localhost/api/health
docker-compose down
```

### 2. Push Code to GitHub
```bash
git add .
git commit -m "Add Docker, CI/CD, and registry setup"
git remote add origin https://github.com/husseink1991/FoodERP-Pro.git
git push -u origin main
```

### 3. GitHub Actions Automatically:
- ✅ Runs linting
- ✅ Runs tests
- ✅ Builds Docker images
- ✅ Pushes to GHCR
- ✅ Creates packages in GitHub

### 4. Verify Images
```bash
# In GitHub:
# Settings → Actions → General → Allow all actions
# Go to Packages tab to see images
```

---

## 📦 Three Registry Methods

### Method 1: GitHub Actions (Automatic) ⭐ RECOMMENDED
```
git push → GitHub Actions triggers → Tests pass → Images pushed to GHCR
```
**Pro:** Automatic, integrated, no setup needed
**Con:** Slower (runs all tests)

### Method 2: Manual Push Script (Local)
```bash
chmod +x scripts/push-to-ghcr.sh
./scripts/push-to-ghcr.sh v1.0.0
```
**Pro:** Fast, full control
**Con:** Manual, need local Docker setup

### Method 3: Docker CLI (Raw)
```bash
docker login ghcr.io -u husseink1991
docker tag fooderp_backend:latest ghcr.io/husseink1991/fooderp-pro/backend:v1.0.0
docker push ghcr.io/husseink1991/fooderp-pro/backend:v1.0.0
```
**Pro:** Minimal, simple
**Con:** More manual steps

---

## 🏥 Frontend Healthchecks

All endpoints return JSON with status and metrics:

```bash
# Alive (Docker uses this)
curl http://localhost/api/health?probe=live
# → {"status": "alive"}

# Ready for traffic
curl http://localhost/api/health?probe=ready
# → {"status": "ready", "uptime": 12345}

# Deep check (with backend verification)
curl http://localhost/api/health?probe=deep
# → {"status": "healthy", "dependencies": {"backend": "healthy"}}

# Performance metrics
curl http://localhost/api/health?probe=metrics
# → {"status": "ok", "metrics": {"uptime": 12345, "requestCount": 42}}
```

---

## 📊 CI/CD Pipeline Status

Monitor in GitHub:
1. Go to **Actions** tab
2. Click **ci-cd.yml** to see all runs
3. Click run to see:
   - ✅ Lint status
   - ✅ Test results
   - ✅ Build logs
   - ✅ Push confirmation

**Image pushed when:**
- ✅ All tests pass
- ✅ Commit pushed to main/develop
- ✅ No build errors

---

## 🎯 What Each File Does

| File | Purpose |
|------|---------|
| `.github/workflows/ci-cd.yml` | Full CI/CD pipeline |
| `.github/workflows/push-to-registry.yml` | Simple build & push |
| `docker/docker-compose.yml` | Container orchestration |
| `docker-compose.yml` | ⬇️ Updated with GHCR support |
| `frontend/app/api/health/route.ts` | Healthcheck endpoints |
| `scripts/push-to-ghcr.sh` | Manual push script |
| `GITHUB_SETUP.md` | GitHub Actions setup |
| `REGISTRY_AND_CICD_SETUP.md` | Complete registry guide |

---

## 🔑 One-Time Setup

```bash
# 1. Create GitHub PAT
# Go to: https://github.com/settings/tokens
# Create token with write:packages scope
# Copy token to clipboard

# 2. Login to GHCR
echo YOUR_TOKEN | docker login ghcr.io -u husseink1991 --password-stdin

# 3. That's it! ✅
# GitHub Actions handle the rest automatically
```

---

## 🚀 Deployment Flow

```
Your Local Changes
        ↓
git push origin main
        ↓
GitHub Actions triggered
        ↓
Tests run (lint, backend, frontend)
        ↓
Docker images built
        ↓
Images pushed to GHCR
        ↓
In production, pull and run:
docker pull ghcr.io/husseink1991/fooderp-pro/backend:latest
docker-compose up -d
```

---

## 🆘 Troubleshooting

**Tests failing?**
```bash
# Run locally to debug
composer test  # Backend
npm test       # Frontend
```

**Workflow not running?**
```bash
# Ensure Actions enabled
# Settings → Actions → General → Allow all actions
```

**Docker push failed?**
```bash
# Check login
docker login ghcr.io

# Verify credentials
docker info
```

---

## 📚 Full Documentation

- `SETUP_COMPLETE.md` - This entire setup explained
- `GITHUB_SETUP.md` - Step-by-step GitHub setup
- `REGISTRY_AND_CICD_SETUP.md` - Registry deep dive
- `docker/DOCKER_SETUP.md` - Docker operations

---

## ✅ Verification Checklist

- [ ] Code pushed to GitHub
- [ ] GitHub Actions enabled
- [ ] First workflow run completed
- [ ] No errors in workflow logs
- [ ] Images appear in GitHub Packages
- [ ] Can pull image: `docker pull ghcr.io/...`
- [ ] Can run image locally
- [ ] Healthcheck responds

---

## 🎓 Key Concepts

**GitHub Actions:** Automated CI/CD on every push
**GHCR:** Container registry integrated with GitHub
**Multi-stage builds:** Smaller, faster, optimized images
**Healthchecks:** Docker knows if app is healthy
**Rate limiting:** Protects login endpoint
**Eager loading:** Faster database queries

---

## 💡 Pro Tips

1. **Use semantic versioning for tags:**
   ```bash
   git tag v1.0.0
   git push origin v1.0.0
   # Creates tagged images: ghcr.io/.../backend:v1.0.0
   ```

2. **Test locally before pushing:**
   ```bash
   docker-compose build
   docker-compose up -d
   # Verify everything works
   ```

3. **Monitor Actions:**
   ```bash
   # GitHub CLI
   gh run list
   gh run view <run_id>
   ```

4. **Debug workflow:**
   ```bash
   # Enable debug logging in Actions
   # Settings → Secrets → New repo secret
   # Name: ACTIONS_STEP_DEBUG
   # Value: true
   ```

---

**Ready to deploy? Start with GITHUB_SETUP.md! 🚀**

