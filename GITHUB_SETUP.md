# 🚀 GitHub Setup Instructions

## Step 1: Initialize Git Repository (if not already done)

```bash
# Navigate to project root
cd FoodERP_Pro_Synchronized

# Initialize git
git init

# Add all files
git add .

# Initial commit
git commit -m "Initial commit: FoodERP Pro with Docker setup"
```

## Step 2: Connect to GitHub Repository

```bash
# Add remote (replace with your actual repo URL)
git remote add origin https://github.com/husseink1991/FoodERP-Pro.git

# Verify remote
git remote -v

# Push to GitHub
git branch -M main
git push -u origin main
```

## Step 3: Enable GitHub Actions

1. Go to GitHub repo: https://github.com/husseink1991/FoodERP-Pro
2. Click **Settings** tab
3. Go to **Actions** → **General**
4. Under "Actions permissions", select: **Allow all actions and reusable workflows**
5. Click **Save**

## Step 4: Configure GitHub Actions Secrets (Optional)

For codecov and other services (already optional in workflows):

1. Go to **Settings** → **Secrets and variables** → **Actions**
2. Click **New repository secret**
3. Add secrets as needed:

| Secret | Value |
|--------|-------|
| `CODECOV_TOKEN` | From https://codecov.io (optional) |

**Note:** `GITHUB_TOKEN` is automatic, no setup needed!

## Step 5: Create GitHub Personal Access Token for GHCR

1. Go to: https://github.com/settings/tokens?type=beta
2. Click **Generate new token**
3. Token name: `ghcr-push`
4. Expiration: 90 days (renewable)
5. Select scopes:
   - ✅ `write:packages`
   - ✅ `read:packages`
   - ✅ `delete:packages`
6. Click **Generate token** and **copy it**

## Step 6: Login to GHCR Locally

```bash
# Option 1: Using token directly
echo "your_token_here" | docker login ghcr.io -u husseink1991 --password-stdin

# Option 2: Using GitHub CLI (recommended)
gh auth login
gh auth token | docker login ghcr.io -u husseink1991 --password-stdin

# Option 3: Interactive login
docker login ghcr.io
# Username: husseink1991
# Password: paste_your_token
```

## Step 7: Verify Setup

```bash
# Test GHCR login
docker run hello-world

# Test GitHub Actions by triggering a workflow
# Option 1: Push code to main branch
git push origin main

# Option 2: Manual trigger (if set up)
# Go to Actions tab → Select workflow → "Run workflow"

# Check workflow progress
# Go to Actions tab and watch the jobs run
```

---

## 🔍 Verify Everything is Working

### Check GitHub Actions

1. Go to repo → **Actions** tab
2. Should see workflow runs
3. Click on latest run to see details
4. Check individual jobs (lint, tests, build, push)

### Check GHCR Images

```bash
# Login to GHCR
docker login ghcr.io -u husseink1991

# List images
curl -H "Authorization: token YOUR_PAT" \
  https://api.github.com/user/packages?package_type=container

# Or in Docker Desktop:
# Go to Docker Settings → Images → Check ghcr.io images
```

### Check Image via Web

1. Go to: https://github.com/husseink1991/FoodERP-Pro/packages
2. Should see **backend** and **frontend** packages
3. Click to view tags and details

---

## 📋 Quick Checklist

- [ ] Repository created on GitHub
- [ ] Local git repo initialized and connected
- [ ] Initial code pushed to main branch
- [ ] GitHub Actions enabled in Settings
- [ ] Personal Access Token created with write:packages scope
- [ ] Logged in to GHCR locally (`docker login ghcr.io`)
- [ ] First workflow run completed successfully
- [ ] Images visible in GitHub packages

---

## 🐛 Troubleshooting

### Workflow not running

1. Check Actions are enabled: Settings → Actions → General
2. Check file exists: `.github/workflows/ci-cd.yml`
3. Verify YAML syntax: Use https://www.yamllint.com/
4. Commit and push: `git push origin main`

### Docker login fails

```bash
# Re-authenticate
docker logout ghcr.io
docker login ghcr.io -u husseink1991

# Or check token expiration
# Go to https://github.com/settings/tokens and regenerate if needed
```

### Images not appearing in GHCR

1. Check workflow logs in Actions tab
2. Look for "Build and push" step
3. Common issues:
   - Docker build failing (check tests)
   - Push step skipped (only on main/develop branch)
   - Insufficient permissions (check PAT scopes)

### Tests failing in CI/CD

1. Go to Actions → Latest run → Failed job
2. Click on failed step for error details
3. Fix locally and push again
4. Common causes:
   - Missing dependencies
   - Database connection issues
   - Syntax errors

---

## 📚 Next Steps

1. **First Push**: `git push origin main` → Triggers CI/CD
2. **Monitor**: Watch Actions tab for workflow execution
3. **Verify Images**: Check GHCR packages in GitHub
4. **Deploy**: Use GHCR images in production (see REGISTRY_AND_CICD_SETUP.md)

---

## 🔗 Useful Links

- Your Repository: https://github.com/husseink1991/FoodERP-Pro
- GitHub Packages: https://github.com/husseink1991/FoodERP-Pro/packages
- GitHub Actions Docs: https://docs.github.com/en/actions
- GHCR Documentation: https://docs.github.com/en/packages/working-with-a-github-packages-registry/working-with-the-container-registry
- Workflow Syntax: https://docs.github.com/en/actions/writing-workflows/workflow-syntax-for-github-actions

