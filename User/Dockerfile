FROM php:8.4-fpm

# Доп. зависимости для rdkafka
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    librdkafka-dev \
    && pecl install rdkafka \
    && docker-php-ext-enable rdkafka \
    && docker-php-ext-install pdo pdo_pgsql zip

# Временная копия composer файлов
COPY composer.json composer.lock ./

# Установка Composer с отключением скриптов
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --optimize-autoloader --no-scripts

# Копируем остальное (включая artisan)
COPY . .

# Настройка прав
WORKDIR /var/www/html
RUN chown -R www-data:www-data /var/www/html/storage \
    && chmod -R 775 /var/www/html/storage

CMD ["sh", "-c", "[ -f artisan ] && php artisan migrate --force || true; php-fpm"]
