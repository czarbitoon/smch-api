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
        'login',
        'logout',
        'register',
        'storage/*',
    ],

    // Allowed HTTP Methods
    'allowed_methods' => [
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
        'OPTIONS',
    ],

    // Allowed Origins - Be more specific for production
   'allowed_origins' => [
    'http://localhost:3000',
    'http://localhost:5173',
    'https://smch-web.vercel.app',
    'https://smch-cqa02kf1r-czarbitoons-projects.vercel.app', // Add this exact one
    'https://contend-fender-evasion.ngrok-free.dev', // Add the backend itself
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
        'X-Socket-ID',               // For broadcasting
        'Access-Control-Allow-Origin',
    ],

    // Headers exposed to the client
    'exposed_headers' => [
        'Authorization',
        'X-XSRF-TOKEN',
        'Content-Disposition',       // For file downloads
    ],

    // Cache duration for preflight requests (in seconds)
    'max_age' => 7200,

    // Support credentials like cookies, authorization headers
    'supports_credentials' => true,
];
