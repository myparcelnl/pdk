<?php

declare(strict_types=1);

return [
    'cors' => [
        'allowedOrigins'      => ['*'],
        'allowedMethods'      => ['GET', 'OPTIONS'],
        'allowedHeaders'      => [
            'Content-Type',
            'Accept',
            'Accept-Language',
            'Authorization',
            'X-Requested-With',
            'X-CSRF-Token',
        ],
        'exposedHeaders'      => [],
        'maxAge'              => 86400, // 24 hour
        'supportsCredentials' => false,
    ],
];
