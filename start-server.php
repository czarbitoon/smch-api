<?php
// Railway startup script for Laravel

echo "Starting Laravel application...\n";

// Get port from environment, default to 8000
$port = getenv('PORT') ?: '8000';

// Ensure port is numeric
if (!is_numeric($port)) {
    echo "Invalid port: $port, using default 8000\n";
    $port = '8000';
}

// Convert to integer
$port = (int) $port;

echo "Starting server on port: $port\n";

// Build the command
$command = "php artisan serve --host=0.0.0.0 --port={$port}";

echo "Executing: $command\n";

// Start the server and keep it running
passthru($command);
