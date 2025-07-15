<?php
// Simple script to setup Laravel for production deployment

echo "Setting up Laravel environment...\n";

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

// Generate app key if not set
if (empty(env('APP_KEY'))) {
    echo "Generating application key...\n";
    exec('php artisan key:generate --force');
}

// Clear config cache
echo "Clearing configuration cache...\n";
exec('php artisan config:clear');

echo "Laravel setup complete!\n";
