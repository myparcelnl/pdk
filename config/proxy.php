<?php

declare(strict_types=1);

return [
    'cors' => [
        'allowedOrigins'      => ['self'], // Only allow requests from the same origin by default
        'allowedMethods'      => ['GET', 'POST', 'OPTIONS'],
        'allowedHeaders'      => [
            'Content-Type',
            'Accept',
            'Accept-Language',
            'Authorization',
            'X-Requested-With',
            'X-CSRF-Token',
        ],
        'exposedHeaders'      => [
            'Content-Type',
            'X-Request-ID',
            'X-Response-Time',
        ],
        'maxAge'              => 86400, // 24 hour
        'supportsCredentials' => false,
    ],
];
