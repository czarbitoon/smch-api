<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    'allowed_origins' => ['http://localhost:3000', 'http://localhost:63261', 'http://localhost:*', 'http://localhost:8000'], // Frontend, Mobile and dynamic ports
    'allowed_headers' => ['*', 'Authorization', 'X-Requested-With', 'Content-Type', 'Accept', 'Origin', 'X-XSRF-TOKEN', 'X-CSRF-TOKEN'],
    'exposed_headers' => ['*'],
    'max_age' => 7200,  // Increase cache time to improve performance
    'supports_credentials' => true,
];
