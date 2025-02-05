<?php

return [
    'secret' => env('JWT_SECRET', '42069'),
    'ttl' => env('JWT_TTL', 60),
];
