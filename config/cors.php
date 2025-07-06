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
        'http://localhost:3000',     // React dev server
        'http://localhost:5173',     // Vite dev server
        'http://localhost:8000',     // Laravel dev server
        'http://localhost',          // General localhost
        'http://127.0.0.1:8000',     // Laravel alternate
        'http://127.0.0.1:3000',     // React alternate
        'http://127.0.0.1:5173',     // Vite alternate
        'capacitor://localhost',     // Capacitor on mobile
        'app://smch-mobile',         // Mobile app scheme
        'http://localhost:8081',     // React Native Metro bundler
        'https://smch-api-production.up.railway.app', // New API endpoint
        'https://smch-web.vercel.app', // Production frontend domain
        'https://smch-web-production.up.railway.app/'
        // Add your production domains here, e.g. 'https://yourdomain.com'
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
