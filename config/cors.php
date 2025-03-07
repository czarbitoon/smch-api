<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    'allowed_origins' => ['http://localhost:3000', 'http://localhost:58149', 'http://localhost:*', 'http://localhost:8000'], // Frontend, Mobile and dynamic ports
    'allowed_headers' => ['*', 'Authorization', 'X-Requested-With', 'Content-Type', 'Accept', 'Origin'],
    'exposed_headers' => ['*'],
    'max_age' => 0,
    'supports_credentials' => true,
];
