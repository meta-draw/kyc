<?php

return [
    'tencent' => [
        'secret_id' => env('KYC_TENCENT_SECRET_ID'),
        'secret_key' => env('KYC_TENCENT_SECRET_KEY'),
    ],

    'routes' => [
        // Route middleware configuration, e.g., ['auth:api'] or ['auth:sanctum']
        'middleware' => env('KYC_ROUTE_MIDDLEWARE') ? explode(',', env('KYC_ROUTE_MIDDLEWARE')) : [],
    ],
];