#!/bin/sh

echo "Starting Railway deployment..."

# Set environment variables if they exist
if [ ! -z "$MYSQLHOST" ]; then
    export DB_HOST=$MYSQLHOST
    export DB_PORT=$MYSQLPORT
    export DB_DATABASE=$MYSQLDATABASE
    export DB_USERNAME=$MYSQLUSER
    export DB_PASSWORD=$MYSQLPASSWORD
    echo "Using Railway MySQL environment variables"
    echo "DB_HOST: $DB_HOST"
    echo "DB_PORT: $DB_PORT"
    echo "DB_DATABASE: $DB_DATABASE"
    echo "DB_USERNAME: $DB_USERNAME"
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

# Generate app key if not exists
if [ -z "$APP_KEY" ]; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

echo "PHP Version: $(php -v | head -1)"
echo "Laravel Version: $(php artisan --version)"

# Test if we can connect to the database
echo "Testing database connection..."
php -r "
try {
    \$host = getenv('DB_HOST') ?: 'localhost';
    \$port = getenv('DB_PORT') ?: '3306';
    \$database = getenv('DB_DATABASE') ?: 'railway';
    \$username = getenv('DB_USERNAME') ?: 'root';
    \$password = getenv('DB_PASSWORD') ?: '';

    echo 'Attempting connection to: ' . \$host . ':' . \$port . '/' . \$database . ' as ' . \$username . PHP_EOL;

    \$pdo = new PDO('mysql:host=' . \$host . ';port=' . \$port . ';dbname=' . \$database, \$username, \$password, [
        PDO::ATTR_TIMEOUT => 5,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo 'Database connection successful!' . PHP_EOL;
} catch (Exception \$e) {
    echo 'Database connection failed: ' . \$e->getMessage() . PHP_EOL;
    echo 'Will attempt to start without database migration.' . PHP_EOL;
}
"

# Clear config cache first
echo "Clearing configuration cache..."
php artisan config:clear

# Try to run migrations (but don't fail if it doesn't work)
echo "Attempting to run migrations..."
php artisan migrate --force || echo "Migration failed, continuing anyway..."

# Cache configurations
echo "Caching configurations..."
php artisan config:cache || echo "Config cache failed, continuing..."

# Start the application
echo "Starting PHP application on port ${PORT:-8000}..."
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}

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
