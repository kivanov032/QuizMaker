# Этап 1: Установка зависимостей Composer
FROM composer:latest AS composer
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# Этап 2: Финальный образ
FROM php:8.4-fpm-alpine

# Зависимости и расширения PHP
RUN apk add --no-cache \
    git unzip libzip-dev libpq-dev postgresql-client \
    libpng-dev freetype-dev libjpeg-turbo-dev zlib-dev \
    librdkafka-dev && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) gd pdo_pgsql pgsql zip pcntl && \
    docker-php-ext-enable rdkafka && \
    rm -rf /var/cache/apk/*

WORKDIR /var/www/html

# Копируем зависимости и код
COPY --from=composer /app/vendor ./vendor
COPY . .

# Настройка Laravel
ENV APP_ENV=production
RUN chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 750 storage bootstrap/cache && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

EXPOSE 9000
HEALTHCHECK --interval=30s --timeout=3s \
    CMD curl -fs http://localhost:9000/ping || exit 1
CMD ["php-fpm"]
