FROM php:8.5-fpm

RUN apt-get update && apt-get install -y \
    git curl libpq-dev libzip-dev zip unzip libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip bcmath gd \
    && apt-get clean

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN if [ -f "composer.json" ]; then composer install --no-interaction --optimize-autoloader; fi

RUN php artisan storage:link 2>/dev/null || true

CMD ["php-fpm"]
