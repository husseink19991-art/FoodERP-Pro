# Stage 1: Builder
FROM php:8.3-fpm-alpine AS builder

# Update Alpine package manager and install build dependencies
RUN apk update && \
    apk add --no-cache --virtual .build-deps \
    g++ \
    gcc \
    make \
    musl-dev \
    autoconf \
    linux-headers && \
    apk add --no-cache \
    mariadb-connector-c-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl && \
    docker-php-ext-configure bcmath && \
    docker-php-ext-install \
    pdo_mysql \
    zip \
    bcmath && \
    apk del .build-deps && \
    rm -rf /var/cache/apk/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock* ./

RUN composer install --no-interaction --no-progress --prefer-dist --optimize-autoloader --no-dev

COPY . .

RUN cp .env.example .env || echo "No .env.example found"

# Stage 2: Runtime
FROM php:8.3-fpm-alpine

# Install only runtime dependencies (no build tools)
RUN apk add --no-cache \
    mariadb-connector-c-dev \
    libzip-dev \
    zip \
    unzip && \
    rm -rf /var/cache/apk/*

# Copy PHP extensions from builder
COPY --from=builder /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/
COPY --from=builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/

# Install extensions in runtime (they're already compiled, just load them)
RUN docker-php-ext-enable pdo_mysql zip bcmath

WORKDIR /var/www/html

COPY --from=builder /var/www/html /var/www/html

RUN chown -R www-data:www-data /var/www/html

USER www-data

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
