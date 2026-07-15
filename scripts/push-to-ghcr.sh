#!/bin/bash
# Push Docker images to GHCR
# Usage: ./push-to-ghcr.sh [tag] [registry] [username] [repo]

set -e

# Configuration
REGISTRY="${3:-ghcr.io}"
USERNAME="${4:-husseink1991}"
REPO="${5:-fooderp-pro}"
TAG="${1:-latest}"
BRANCH="${2:-main}"

BACKEND_IMAGE="$REGISTRY/$USERNAME/$REPO/backend:$TAG"
FRONTEND_IMAGE="$REGISTRY/$USERNAME/$REPO/frontend:$TAG"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🐳 FoodERP Pro - Docker Push to GHCR${NC}"
echo -e "${BLUE}═══════════════════════════════════${NC}\n"

# Check if docker is running
if ! docker info > /dev/null 2>&1; then
    echo -e "${RED}❌ Docker daemon is not running${NC}"
    exit 1
fi

# Check if logged in to GHCR
if ! docker login $REGISTRY --username $USERNAME --password "" 2>/dev/null; then
    echo -e "${YELLOW}⚠️  Not logged in to $REGISTRY${NC}"
    echo -e "${BLUE}🔐 Please login:${NC}"
    docker login $REGISTRY
fi

echo -e "${BLUE}📦 Configuration:${NC}"
echo "   Registry: $REGISTRY"
echo "   Username: $USERNAME"
echo "   Repository: $REPO"
echo "   Tag: $TAG"
echo "   Branch: $BRANCH\n"

# Build images
echo -e "${BLUE}🏗️  Building Docker images...${NC}"
cd docker
docker-compose build
cd ..

# Tag and push backend
echo -e "\n${BLUE}🏷️  Tagging backend image...${NC}"
docker tag fooderp_backend:latest $BACKEND_IMAGE
echo -e "${GREEN}✅ Tagged as: $BACKEND_IMAGE${NC}"

echo -e "${BLUE}📤 Pushing backend image...${NC}"
if docker push $BACKEND_IMAGE; then
    echo -e "${GREEN}✅ Backend image pushed successfully${NC}"
else
    echo -e "${RED}❌ Failed to push backend image${NC}"
    exit 1
fi

# Tag and push frontend
echo -e "\n${BLUE}🏷️  Tagging frontend image...${NC}"
docker tag fooderp_frontend:latest $FRONTEND_IMAGE
echo -e "${GREEN}✅ Tagged as: $FRONTEND_IMAGE${NC}"

echo -e "${BLUE}📤 Pushing frontend image...${NC}"
if docker push $FRONTEND_IMAGE; then
    echo -e "${GREEN}✅ Frontend image pushed successfully${NC}"
else
    echo -e "${RED}❌ Failed to push frontend image${NC}"
    exit 1
fi

# Final summary
echo -e "\n${GREEN}═══════════════════════════════════${NC}"
echo -e "${GREEN}✅ All images pushed successfully!${NC}"
echo -e "${GREEN}═══════════════════════════════════${NC}\n"

echo -e "${BLUE}📋 Summary:${NC}"
echo "   Backend:  $BACKEND_IMAGE"
echo "   Frontend: $FRONTEND_IMAGE"
echo ""
echo -e "${BLUE}🚀 To use these images:${NC}"
echo "   1. Update docker-compose.yml to use the image"
echo "   2. Or run: docker pull $BACKEND_IMAGE"
echo "   3. Or run: docker pull $FRONTEND_IMAGE"
echo ""
echo -e "${BLUE}📖 For more info, see REGISTRY_AND_CICD_SETUP.md${NC}\n"
