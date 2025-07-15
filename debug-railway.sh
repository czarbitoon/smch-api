#!/bin/sh

echo "=== RAILWAY DEBUGGING SCRIPT ==="
echo "Current directory: $(pwd)"
echo "Files in current directory:"
ls -la

echo ""
echo "=== ENVIRONMENT VARIABLES ==="
echo "APP_KEY: ${APP_KEY:0:20}..."
echo "APP_ENV: $APP_ENV"
echo "APP_DEBUG: $APP_DEBUG"
echo "APP_URL: $APP_URL"
echo "DB_CONNECTION: $DB_CONNECTION"
echo "DB_HOST: $DB_HOST"
echo "DB_PORT: $DB_PORT"
echo "DB_DATABASE: $DB_DATABASE"
echo "DB_USERNAME: $DB_USERNAME"
echo "PORT: $PORT"

echo ""
echo "=== RAILWAY MYSQL VARS ==="
echo "MYSQLHOST: $MYSQLHOST"
echo "MYSQLPORT: $MYSQLPORT"
echo "MYSQLDATABASE: $MYSQLDATABASE"
echo "MYSQLUSER: $MYSQLUSER"

echo ""
echo "=== PHP VERSION ==="
php -v

echo ""
echo "=== COMPOSER VERSION ==="
composer --version

echo ""
echo "=== LARAVEL VERSION ==="
php artisan --version

echo ""
echo "=== CONFIG CHECK ==="
php artisan config:show database.default 2>/dev/null || echo "Config command failed"

echo ""
echo "=== ROUTE LIST ==="
php artisan route:list 2>/dev/null || echo "Route list failed"

echo ""
echo "=== STORAGE PERMISSIONS ==="
ls -la storage/
ls -la bootstrap/cache/ 2>/dev/null || echo "Bootstrap cache directory not found"

echo ""
echo "=== STARTING APPLICATION ==="
