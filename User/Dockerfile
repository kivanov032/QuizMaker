# Базовый слой с общими зависимостями
FROM php:8.4-fpm as base

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    librdkafka-dev \
    git \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_pgsql zip \
    && pecl install rdkafka \
    && docker-php-ext-enable rdkafka

RUN pecl install redis && docker-php-ext-enable redis

# Финальный образ для сервиса
FROM base

WORKDIR /app

# Установка Composer с проверкой подписи
COPY composer.* ./
RUN curl -sS https://composer.github.io/installer.sig -o installer.sig \
    && curl -sS https://getcomposer.org/installer -o installer.php \
    && php -r "if (hash_file('SHA384', 'installer.php') !== trim(file_get_contents('installer.sig'))) { unlink('installer.php'); echo 'Invalid installer' . PHP_EOL; exit(1); }" \
    && php installer.php --install-dir=/usr/local/bin --filename=composer \
    && rm installer.* \
    && composer install --no-dev --optimize-autoloader --no-scripts

COPY . .
EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0"]
