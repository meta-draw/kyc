<?php

use Illuminate\Support\Facades\Route;
use MetaDraw\Kyc\Http\Controllers\KycVerificationController;

// Build middleware array from config
$middleware = [
    \MetaDraw\Kyc\Http\Middleware\KycEnabled::class,
];

// Add auth middleware from config
if ($authMiddleware = config('kyc.auth.middleware')) {
    $middleware[] = $authMiddleware;
}

// Add any additional middleware from config
if ($additionalMiddleware = config('kyc.auth.additional_middleware', [])) {
    $middleware = array_merge($middleware, $additionalMiddleware);
}

Route::middleware($middleware)->group(function () {
    Route::prefix('kyc-verification')->group(function () {
        Route::post('/', [KycVerificationController::class, 'store']);
        Route::get('/', [KycVerificationController::class, 'show']);
        Route::post('/upload', [KycVerificationController::class, 'upload']);
    });
});