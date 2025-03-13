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
        'http://localhost:3000',    // Frontend
        'http://localhost:52625',   // Mobile
        'http://localhost:8000',    // Development
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
