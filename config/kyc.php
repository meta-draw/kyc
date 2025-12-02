<?php

return [
    /**
     * Enable or disable KYC verification
     */
    'enabled' => env('KYC_ENABLED', true),

    /**
     * Authentication configuration
     */
    'auth' => [
        /**
         * Authentication middleware to use
         * Examples: 'auth:sanctum', 'auth:api', 'jwt.auth', custom middleware
         */
        'middleware' => env('KYC_AUTH_MIDDLEWARE', 'auth:api'),
        
        /**
         * Additional middleware to apply
         */
        'additional_middleware' => [],
    ],

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
    
    /**
     * Route configuration
     */
    'routes' => [
        /**
         * API route prefix
         */
        'prefix' => 'api',
        
        /**
         * Route name prefix
         */
        'name' => 'kyc.',
        
        /**
         * Enable/disable routes registration
         */
        'enabled' => true,
    ],
];