<?php

return [
    'aliyun' => [
        'app_code' => env('KYC_ALIYUN_APP_CODE'),
    ],

    'routes' => [
        // Route middleware configuration, e.g., ['auth:api'] or ['auth:sanctum']
        'middleware' => env('KYC_ROUTE_MIDDLEWARE') ? explode(',', env('KYC_ROUTE_MIDDLEWARE')) : [],
    ],
];