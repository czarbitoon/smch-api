#!/bin/sh

echo "=== SIMPLE RAILWAY STARTUP ==="

# Source the debug script first
sh debug-railway.sh

# Set environment variables for Railway MySQL
export DB_HOST=${MYSQLHOST:-localhost}
export DB_PORT=${MYSQLPORT:-3306}
export DB_DATABASE=${MYSQLDATABASE:-railway}
export DB_USERNAME=${MYSQLUSER:-root}
export DB_PASSWORD=${MYSQLPASSWORD:-}

echo "Database connection will use: $DB_HOST:$DB_PORT/$DB_DATABASE"

# Create storage directories
mkdir -p storage/framework/{cache,sessions,views}
mkdir -p storage/logs
mkdir -p bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Don't cache anything yet, just try to start
php artisan config:clear

echo "Starting Laravel development server..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
