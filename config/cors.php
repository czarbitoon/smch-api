<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CORS Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers.
    |
    */

    // API Routes that should handle CORS
    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
    ],

    // Allowed HTTP Methods
    'allowed_methods' => [
        'GET',
        'POST',
        'PUT',
        'DELETE',
        'OPTIONS',
    ],

    // Allowed Origins
    'allowed_origins' => [
        'http://localhost:3000',    // Frontend development
        'http://localhost:5173',    // Vite development server
        'http://localhost:55345',   // Mobile development
        'http://localhost:8000',    // API development
        env('FRONTEND_URL', '*'),   // Production frontend URL
        env('MOBILE_URL', '*'),     // Production mobile URL
    ],

    // Allowed Headers
    'allowed_headers' => [
        'Authorization',
        'X-Requested-With',
        'Content-Type',
        'Accept',
        'Origin',
        'X-XSRF-TOKEN',
        'X-CSRF-TOKEN',
    ],

    // Headers exposed to the client
    'exposed_headers' => [
        'Authorization',
        'X-XSRF-TOKEN',
    ],

    // Cache duration for preflight requests (in seconds)
    'max_age' => 7200,

    // Support credentials like cookies, authorization headers
    'supports_credentials' => true,
];
