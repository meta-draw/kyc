<?php

use Illuminate\Support\Facades\Route;
use MetaDraw\Kyc\Http\Controllers\KycController;

$middleware = config('kyc.routes.middleware', []);

Route::prefix('api/kyc')->middleware($middleware)->group(function () {
    Route::post('verify', [KycController::class, 'verify'])->middleware('kyc.verify.throttle');
    Route::get('status', [KycController::class, 'status']);
});