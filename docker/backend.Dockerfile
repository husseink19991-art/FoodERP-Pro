# Stage 1: Builder
FROM php:8.3-fpm-alpine AS builder

RUN apk add --no-cache \
    mariadb-connector-c-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl

RUN docker-php-ext-install pdo_mysql zip bcmath

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock* ./

RUN composer install --no-interaction --no-progress --prefer-dist --optimize-autoloader --no-dev

COPY . .

RUN cp .env.example .env || echo "No .env.example found"

# Stage 2: Runtime
FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    mariadb-connector-c-dev \
    libzip-dev \
    zip \
    unzip

RUN docker-php-ext-install pdo_mysql zip bcmath

WORKDIR /var/www/html

COPY --from=builder /var/www/html /var/www/html

RUN chown -R www-data:www-data /var/www/html

USER www-data

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
