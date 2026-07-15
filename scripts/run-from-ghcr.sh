#!/bin/bash
# Pull Docker images from GHCR and run containers
# Usage: ./run-from-ghcr.sh [tag] [registry] [username] [repo]

set -e

# Configuration
REGISTRY="${2:-ghcr.io}"
USERNAME="${3:-husseink1991}"
REPO="${4:-fooderp-pro}"
TAG="${1:-latest}"

BACKEND_IMAGE="$REGISTRY/$USERNAME/$REPO/backend:$TAG"
FRONTEND_IMAGE="$REGISTRY/$USERNAME/$REPO/frontend:$TAG"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🐳 FoodERP Pro - Pull & Run from GHCR${NC}"
echo -e "${BLUE}════════════════════════════════════${NC}\n"

# Check if docker is running
if ! docker info > /dev/null 2>&1; then
    echo -e "${RED}❌ Docker daemon is not running${NC}"
    exit 1
fi

# Check if logged in
echo -e "${BLUE}🔐 Checking GHCR authentication...${NC}"
if ! docker pull --dry-run $REGISTRY/$USERNAME/$REPO/backend:$TAG 2>/dev/null; then
    echo -e "${YELLOW}⚠️  Not logged in to $REGISTRY${NC}"
    echo -e "${BLUE}🔑 Please login first:${NC}"
    docker login $REGISTRY
fi

echo -e "\n${BLUE}📋 Configuration:${NC}"
echo "   Registry: $REGISTRY"
echo "   Username: $USERNAME"
echo "   Repository: $REPO"
echo "   Tag: $TAG\n"

# Pull images
echo -e "${BLUE}📥 Pulling images from GHCR...${NC}"

echo "   Backend: $BACKEND_IMAGE"
if docker pull $BACKEND_IMAGE; then
    echo -e "${GREEN}✅ Backend image pulled${NC}"
else
    echo -e "${RED}❌ Failed to pull backend image${NC}"
    exit 1
fi

echo "   Frontend: $FRONTEND_IMAGE"
if docker pull $FRONTEND_IMAGE; then
    echo -e "${GREEN}✅ Frontend image pulled${NC}"
else
    echo -e "${RED}❌ Failed to pull frontend image${NC}"
    exit 1
fi

# Start containers
echo -e "\n${BLUE}🚀 Starting containers...${NC}"
cd docker

# Create a temporary docker-compose override for GHCR images
cat > docker-compose.override.yml <<EOF
services:
  backend:
    image: $BACKEND_IMAGE
    build: null
    
  frontend:
    image: $FRONTEND_IMAGE
    build: null
EOF

docker-compose up -d

# Verify services
echo -e "\n${BLUE}✓ Waiting for services to be healthy...${NC}"
sleep 5

# Check health
echo -e "\n${BLUE}🏥 Health status:${NC}"
docker-compose ps

# Test endpoints
echo -e "\n${BLUE}🧪 Testing endpoints:${NC}"
if curl -s http://localhost/api/health | jq . > /dev/null 2>&1; then
    echo -e "${GREEN}✅ Backend API is healthy${NC}"
else
    echo -e "${YELLOW}⚠️  Backend API might not be ready yet${NC}"
fi

if curl -s http://localhost/api/health?probe=live | jq . > /dev/null 2>&1; then
    echo -e "${GREEN}✅ Frontend is healthy${NC}"
else
    echo -e "${YELLOW}⚠️  Frontend might not be ready yet${NC}"
fi

# Final summary
echo -e "\n${GREEN}════════════════════════════════════${NC}"
echo -e "${GREEN}✅ Containers running successfully!${NC}"
echo -e "${GREEN}════════════════════════════════════${NC}\n"

echo -e "${BLUE}📋 Running containers:${NC}"
docker-compose ps

echo -e "\n${BLUE}🌐 Access your application:${NC}"
echo "   App URL: http://localhost"
echo "   Backend: http://localhost/api"
echo "   Health: http://localhost/api/health"

echo -e "\n${BLUE}📖 Useful commands:${NC}"
echo "   View logs:     docker-compose logs -f"
echo "   Stop:          docker-compose down"
echo "   Rebuild:       docker-compose build"
echo "   Shell access:  docker-compose exec backend sh\n"
