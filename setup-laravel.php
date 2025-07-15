<?php
// Simple script to setup Laravel for production deployment

echo "Setting up Laravel environment...\n";

// Debug environment variables
echo "Environment variables:\n";
echo "APP_ENV: " . (getenv('APP_ENV') ?: 'not set') . "\n";
echo "APP_KEY: " . (getenv('APP_KEY') ? 'set' : 'not set') . "\n";
echo "DB_CONNECTION: " . (getenv('DB_CONNECTION') ?: 'not set') . "\n";
echo "MYSQLHOST: " . (getenv('MYSQLHOST') ?: 'not set') . "\n";
echo "PORT: " . (getenv('PORT') ?: 'not set') . "\n";

// Create necessary directories
$directories = [
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
    'bootstrap/cache'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
        echo "Created directory: $dir\n";
    }
}

// Set permissions
exec('chmod -R 775 storage bootstrap/cache');
echo "Set storage permissions\n";

// Clear config cache
echo "Clearing configuration cache...\n";
exec('php artisan config:clear');

echo "Laravel setup complete!\n";
