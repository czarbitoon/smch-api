#!/bin/bash

# Railway startup script for Laravel API
echo "Starting Railway deployment..."

# Make sure we have the correct PHP version
echo "PHP Version: $(php -v)"

# Install dependencies if vendor doesn't exist
if [ ! -d "vendor" ]; then
    echo "Installing Composer dependencies..."
    composer install --no-dev --optimize-autoloader
fi

# Generate application key if not set
if [ -z "$APP_KEY" ]; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Clear and cache config
echo "Clearing and caching configuration..."
php artisan config:clear
php artisan config:cache

# Clear and cache routes
echo "Caching routes..."
php artisan route:clear
php artisan route:cache

# Clear and cache views
echo "Caching views..."
php artisan view:clear
php artisan view:cache

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# Seed database if needed
echo "Seeding database..."
php artisan db:seed --force --class=DatabaseSeeder

# Set proper permissions
echo "Setting storage permissions..."
chmod -R 775 storage bootstrap/cache

# Clear all caches
echo "Clearing application cache..."
php artisan cache:clear

# Start the application
echo "Starting PHP server..."
php artisan serve --host=0.0.0.0 --port=$PORT
