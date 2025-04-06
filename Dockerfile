FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libgd-dev \
    zip \
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
# First install the dependencies for GD
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy existing application directory
COPY . .

# Install dependencies
RUN composer install

# Generate application key
RUN php artisan key:generate

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage

# Expose port 8000
EXPOSE 8000

# Start PHP built-in server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]