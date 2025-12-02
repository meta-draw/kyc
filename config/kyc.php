<?php

return [
    /**
     * Enable or disable KYC verification
     */
    'enabled' => env('KYC_ENABLED', true),

    /**
     * KYC verification levels
     */
    'levels' => [
        'basic' => [
            'name' => 'Basic Verification',
            'requirements' => ['email', 'phone'],
        ],
        'advanced' => [
            'name' => 'Advanced Verification', 
            'requirements' => ['email', 'phone', 'identity', 'address'],
        ],
    ],

    /**
     * Database table prefix for KYC tables
     */
    'table_prefix' => 'kyc_',

    /**
     * API endpoints configuration
     */
    'api' => [
        'base_url' => env('KYC_API_BASE_URL'),
        'timeout' => env('KYC_API_TIMEOUT', 30),
    ],
];