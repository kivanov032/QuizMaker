FROM php:8.4-fpm-alpine3.19

# Ставим только критичные зависимости (без opcache и т.п.)
RUN apk add --no-cache \
    postgresql-dev \
    && docker-php-ext-install pdo_pgsql

# Копируем код (предполагаем, что composer зависимости уже установлены локально)
COPY . /var/www/html
WORKDIR /var/www/html

# Запускаем сервис (замените на свою команду)
CMD ["php-fpm"]
