#!/usr/bin/env bash

echo "Railway Deployment Script Starting..."

# Set environment variables if they exist
if [ ! -z "$MYSQLHOST" ]; then
    export DB_HOST=$MYSQLHOST
    export DB_PORT=$MYSQLPORT
    export DB_DATABASE=$MYSQLDATABASE
    export DB_USERNAME=$MYSQLUSER
    export DB_PASSWORD=$MYSQLPASSWORD
    echo "Using Railway MySQL environment variables"
fi

# Create directories and set permissions
echo "Creating necessary directories..."
mkdir -p storage/framework/{cache,sessions,views}
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Set proper permissions
echo "Setting permissions..."
chmod -R 775 storage
chmod -R 775 bootstrap/cache

echo "Running composer"
composer install --no-dev --optimize-autoloader --no-interaction

# Generate app key if not exists
if [ -z "$APP_KEY" ]; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Clear all caches first
echo "Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo "Caching config..."
php artisan config:cache
echo "Caching routes..."
php artisan route:cache
echo "Running migrations..."
php artisan migrate --force

# Seed database if needed
echo "Seeding database..."
php artisan db:seed --force --class=DatabaseSeeder || echo "Seeding skipped or failed"
