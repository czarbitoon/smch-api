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

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:8081',
        'http://localhost:5173',
        'https://contend-fender-evasion.ngrok-free.dev',
        'http://192.168.16.1:8000',
        'https://smch-web.vercel.app'
    ],

    'allowed_origins_patterns' => [
        // Allow any Vercel domain (production + previews)
        '#^https://.*\.vercel\.app$#',
        // Allow ngrok domains for local development
        '#^https://.*\.ngrok(?:-free)?\.dev$#',
        // Allow localhost for development
        '#^https?://localhost.*$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [
        'Authorization',
        'X-XSRF-TOKEN',
    ],

    // Set to 0 during debugging to force browser to re-check CORS every time
    // Change back to 7200 once the demo is stable
    'max_age' => 0,

    'supports_credentials' => true,
];