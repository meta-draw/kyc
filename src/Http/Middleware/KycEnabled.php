<?php

namespace MetaDraw\Kyc\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class KycEnabled
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!config('kyc.enabled', true)) {
            return response()->json([
                'isSuccess' => false,
                'message' => 'KYC verification is currently disabled',
            ], 503);
        }

        return $next($request);
    }
}