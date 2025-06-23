FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    unzip \
    oniguruma-dev \
    libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo_mysql mbstring exif pcntl bcmath

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

# Ensure .env exists before artisan commands
RUN if [ ! -f .env ]; then cp .env.example .env; fi

RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev \
    && php artisan key:generate \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Ensure Laravel storage and cache directories exist and are writable
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
