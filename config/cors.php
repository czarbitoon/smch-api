<?php

return [
    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'login',
        'logout',
        'register',
        'storage/*',
    ],
    

    'allowed_methods' => ['*'],

    'allowed_origins' => explode(',', env('ALLOWED_ORIGINS', 'http://localhost:5173')),

    'allowed_origins_patterns' => [
        '#^https://.*\.vercel\.app$#',
    ],

    'allowed_headers' => [
        'Content-Type',
        'X-Requested-With',
        'Authorization',
        'Accept',
        'Origin',
        'X-XSRF-TOKEN',
        'X-CSRF-TOKEN',
        'ngrok-skip-browser-warning',
    ],

    'exposed_headers' => [
        'Authorization',
        'X-XSRF-TOKEN',
    ],

    'max_age' => 7200,

    'supports_credentials' => true,
];