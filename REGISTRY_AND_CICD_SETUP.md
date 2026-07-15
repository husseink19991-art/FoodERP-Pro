# FoodERP Pro - Docker Registry & CI/CD Setup Guide

## 📦 Part 1: GitHub Container Registry (GHCR) Setup

### Prerequisites
- GitHub account with FoodERP-Pro repository
- Docker installed locally (version 20.10+)
- GitHub CLI installed (optional but recommended)

### Step 1: Create GitHub Personal Access Token (PAT)

1. Go to GitHub Settings → Developer settings → Personal access tokens → Tokens (classic)
2. Click "Generate new token (classic)"
3. Give it a name: `GHCR_PUSH_TOKEN`
4. Select scopes:
   - ✅ `write:packages` - to push Docker images
   - ✅ `read:packages` - to pull Docker images
   - ✅ `delete:packages` - to delete packages (optional)
5. Click "Generate token" and **copy it** (you won't see it again)

### Step 2: Configure Docker for GHCR

#### Option A: Using CLI (Recommended)
```bash
# Login to GHCR
echo YOUR_PAT_HERE | docker login ghcr.io -u husseink1991 --password-stdin

# Or use GitHub CLI
gh auth login
gh auth token | docker login ghcr.io -u husseink1991 --password-stdin
```

#### Option B: Manual Login
```bash
docker login ghcr.io
# Username: husseink1991
# Password: (paste your PAT)
```

### Step 3: Verify GHCR Login
```bash
docker run hello-world ghcr.io/hello-world
```

---

## 🚀 Part 2: Push Images to GHCR

### Method 1: Manual Push (Local Development)

```bash
cd docker

# Build images locally with GHCR tags
docker-compose build

# Tag images for GHCR
docker tag fooderp_backend:latest ghcr.io/husseink1991/fooderp-pro/backend:latest
docker tag fooderp_frontend:latest ghcr.io/husseink1991/fooderp-pro/frontend:latest

# Push to GHCR
docker push ghcr.io/husseink1991/fooderp-pro/backend:latest
docker push ghcr.io/husseink1991/fooderp-pro/frontend:latest
```

### Method 2: Automated Push (CI/CD with GitHub Actions)

The CI/CD pipeline is already configured in `.github/workflows/ci-cd.yml`.

**How it works:**
1. Push code to `main` or `develop` branch
2. GitHub Actions automatically:
   - Runs linting (PHP Pint, ESLint)
   - Runs tests (PHPUnit, frontend tests)
   - Builds Docker images
   - Pushes to GHCR with tags:
     - `latest` (for main branch only)
     - Branch name (e.g., `develop`, `main`)
     - Commit SHA (e.g., `develop-abc123d`)
     - Semantic version (if tagged with v1.0.0)

**Image Tags Generated:**
```
ghcr.io/husseink1991/fooderp-pro/backend:latest
ghcr.io/husseink1991/fooderp-pro/backend:main
ghcr.io/husseink1991/fooderp-pro/backend:main-abc123d
ghcr.io/husseink1991/fooderp-pro/backend:v1.0.0
```

### Method 3: Manual Push Script

Create `scripts/push-to-ghcr.sh`:

```bash
#!/bin/bash
set -e

REGISTRY="ghcr.io"
REPO="husseink1991/fooderp-pro"
TAG="${1:-latest}"

echo "🔐 Logging in to GHCR..."
echo "$GITHUB_TOKEN" | docker login $REGISTRY -u $GITHUB_ACTOR --password-stdin

echo "🏗️ Building images..."
cd docker
docker-compose build

echo "🏷️  Tagging images..."
docker tag fooderp_backend:latest $REGISTRY/$REPO/backend:$TAG
docker tag fooderp_frontend:latest $REGISTRY/$REPO/frontend:$TAG

echo "📤 Pushing to GHCR..."
docker push $REGISTRY/$REPO/backend:$TAG
docker push $REGISTRY/$REPO/frontend:$TAG

echo "✅ Images pushed successfully!"
echo "Backend: $REGISTRY/$REPO/backend:$TAG"
echo "Frontend: $REGISTRY/$REPO/frontend:$TAG"
```

Run it:
```bash
chmod +x scripts/push-to-ghcr.sh
GITHUB_TOKEN=your_pat ./scripts/push-to-ghcr.sh v1.0.0
```

---

## 🔄 Part 3: GitHub Actions CI/CD Pipeline

### Workflows Configured

#### 1. **push-to-registry.yml** - Simple build & push
- Triggers on: push to main/develop, manual trigger
- Builds and pushes to GHCR
- No testing (fast feedback)

#### 2. **ci-cd.yml** - Full CI/CD pipeline (Recommended)
- Triggers on: push, pull requests, daily schedule
- Jobs:
  - **Lint**: PHP Pint, ESLint
  - **Backend Tests**: PHPUnit with MySQL + Redis
  - **Frontend Tests**: Next.js build
  - **Build Images**: Only on main/develop push

### Setting Up Repository Secrets

1. Go to GitHub repo → Settings → Secrets and variables → Actions
2. Click "New repository secret"
3. Add these secrets:

| Secret Name | Value |
|---|---|
| `GHCR_TOKEN` | Your GHCR PAT (optional, uses GITHUB_TOKEN by default) |
| `CODECOV_TOKEN` | From codecov.io (optional, for coverage reports) |

**Note:** `GITHUB_TOKEN` is automatically available in Actions - no manual setup needed!

### Monitoring CI/CD Runs

1. Go to repo → Actions tab
2. Click on workflow name to see detailed logs
3. Check individual job steps for errors
4. View build artifacts (if any)

---

## 📦 Part 4: Using GHCR Images in Deployment

### Switch from Local Build to GHCR Image

In `docker/docker-compose.yml`, comment out `build:` and uncomment `image:`:

```yaml
  backend:
    # build:
    #   context: ../backend
    #   dockerfile: ../docker/backend.Dockerfile
    
    # Use pre-built image from GHCR (production)
    image: ghcr.io/husseink1991/fooderp-pro/backend:latest
    
  frontend:
    # build:
    #   context: ../frontend
    #   dockerfile: ../docker/frontend.Dockerfile
    
    # Use pre-built image from GHCR (production)
    image: ghcr.io/husseink1991/fooderp-pro/frontend:latest
```

### Pull and Run from GHCR

```bash
# Login to GHCR
docker login ghcr.io

# Pull latest images
docker pull ghcr.io/husseink1991/fooderp-pro/backend:latest
docker pull ghcr.io/husseink1991/fooderp-pro/frontend:latest

# Start containers
cd docker
docker-compose up -d
```

---

## 🔒 Part 5: Private Repository Setup (Optional)

### Make GHCR Package Private

1. Go to GitHub repo → Packages section
2. Click package (backend or frontend)
3. Settings → Visibility → Change to Private
4. Only users with repo access can pull

### Grant Access to Deployment Server

On your deployment server:

```bash
# Create GitHub token with read:packages scope
# Then login
docker login ghcr.io -u YOUR_USERNAME -p YOUR_PAT

# Pull and run
docker pull ghcr.io/husseink1991/fooderp-pro/backend:latest
docker-compose up -d
```

---

## 🚨 Part 6: Troubleshooting

### Issue: "unauthorized: authentication required"
```bash
# Login again
docker logout ghcr.io
docker login ghcr.io -u husseink1991
```

### Issue: "manifest not found"
```bash
# Image doesn't exist or wrong tag
docker images | grep ghcr.io
# Re-push: docker push ghcr.io/...
```

### Issue: GitHub Actions workflow fails
1. Check Actions tab for error logs
2. Common causes:
   - Missing dependencies in package.json/composer.json
   - Tests failing
   - Docker build context issues

### Issue: Images too large
```bash
# Check image size
docker images ghcr.io/husseink1991/fooderp-pro/*

# Reduce size:
# - Update Dockerfiles with multi-stage builds ✅ (already done)
# - Remove unnecessary dependencies
# - Use .dockerignore ✅ (already done)
```

---

## ✅ Checklist

- [ ] Created GitHub PAT with `write:packages` scope
- [ ] Logged in to GHCR with `docker login ghcr.io`
- [ ] Pushed images manually or via CI/CD
- [ ] Verified images appear in GitHub repo → Packages
- [ ] Updated docker-compose.yml to use GHCR images
- [ ] Configured GitHub Actions secrets (if needed)
- [ ] Set repository to private (if needed)
- [ ] Tested pulling and running GHCR images locally

---

## 📚 Additional Resources

- [GHCR Documentation](https://docs.github.com/en/packages/working-with-a-github-packages-registry/working-with-the-container-registry)
- [Docker Build Push Action](https://github.com/docker/build-push-action)
- [GitHub Actions Secrets](https://docs.github.com/en/actions/security-guides/using-secrets-in-github-actions)
- [Semantic Versioning](https://semver.org/)

---

## 🎯 Next Steps

1. **Local Development**: Use `docker-compose build` + local images
2. **Testing**: Merge to develop branch → CI/CD tests and pushes
3. **Production**: Merge to main branch → Latest tag pushed
4. **Deployment**: Pull from GHCR and run on server
5. **Releases**: Tag commits with v1.0.0 → Versioned images created

