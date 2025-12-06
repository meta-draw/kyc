<?php

namespace MetaDraw\Kyc\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class KycVerifyThrottle
{
    public function handle(Request $request, Closure $next)
    {
        $userId = $request->user()->id;
        $minuteKey = 'kyc-verify-minute:' . $userId;
        $dayKey = 'kyc-verify-day:' . $userId;

        // 检查分钟限制
        if (RateLimiter::tooManyAttempts($minuteKey, 1)) {
            return response()->json(['error' => '请求过于频繁，请1分钟后再试'], 429);
        }

        // 检查每日限制
        if (RateLimiter::tooManyAttempts($dayKey, 10)) {
            return response()->json(['error' => '今日验证次数已达上限（10次）'], 429);
        }

        // 增加计数
        RateLimiter::hit($minuteKey, 60);
        RateLimiter::hit($dayKey, 86400);

        return $next($request);
    }
}