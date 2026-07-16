# Stage 1: Builder
FROM node:20-alpine AS builder

WORKDIR /app

COPY package*.json ./

# Use npm ci if package-lock.json exists, otherwise npm install
RUN if [ -f package-lock.json ]; then npm ci --prefer-offline --no-audit --legacy-peer-deps; else npm install --prefer-offline --no-audit --legacy-peer-deps; fi

COPY . .

RUN npm run build

# Ensure public directory exists
RUN mkdir -p /app/public || true

# Stage 2: Runtime
FROM node:20-alpine

WORKDIR /app

ENV NODE_ENV=production

RUN addgroup -g 1001 -S nodejs && adduser -S nextjs -u 1001

# Copy public directory (may be empty or with static assets)
COPY --from=builder /app/public ./public/

# Copy Next.js standalone output
COPY --from=builder --chown=nextjs:nodejs /app/.next/standalone ./

# Copy Next.js static files
COPY --from=builder --chown=nextjs:nodejs /app/.next/static ./.next/static

# Copy package.json for reference
COPY --chown=nextjs:nodejs package.json ./

USER nextjs

EXPOSE 3000

CMD ["node", "server.js"]
