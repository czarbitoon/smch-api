<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers.
    |
    */

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'login',
        'logout',
        'register',
        'storage/*',
    ],

    // Using '*' here is fine for methods as long as origins are restricted
    'allowed_methods' => ['*'],

    'allowed_origins' => explode(',', env('ALLOWED_ORIGINS', 'http://localhost:5173')),

    'allowed_origins_patterns' => [
        // Allow any Vercel domain (production + previews)
        '#^https://.*\.vercel\.app$#',
        // Allow ngrok domains for local development
        '#^https://.*\.ngrok(?:-free)?\.dev$#',
        // Allow localhost for development
        '#^https?://localhost.*$#',
    ],

    'allowed_headers' => [
        'Content-Type',
        'X-Requested-With',
        'Authorization',
        'Accept',
        'Origin',
        'X-XSRF-TOKEN',
        'X-CSRF-TOKEN',
        // Crucial for Ngrok bypass
        'ngrok-skip-browser-warning',
    ],

    'exposed_headers' => [
        'Authorization',
        'X-XSRF-TOKEN',
    ],

    // Set to 0 during debugging to force browser to re-check CORS every time
    // Change back to 7200 once the demo is stable
    'max_age' => 0,

    'supports_credentials' => true,
];