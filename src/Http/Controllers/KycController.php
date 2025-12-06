<?php

namespace MetaDraw\Kyc\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use MetaDraw\Kyc\Services\KycService;
use MetaDraw\Kyc\Http\Requests\VerifyKycRequest;
use Illuminate\Support\Facades\RateLimiter;

class KycController extends Controller
{
    public function __construct(private KycService $kycService)
    {
    }

    public function verify(VerifyKycRequest $request)
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
        RateLimiter::hit($minuteKey, 60); // 60秒
        RateLimiter::hit($dayKey, 86400); // 24小时

        $result = $this->kycService->verify(
            $request->input('id_card'),
            $request->input('mobile'),
            $request->input('real_name'),
            $request->user()->id
        );

        return response()->json($result);
    }

    public function status(Request $request)
    {
        $result = $this->kycService->status($request->user()->id);

        return response()->json($result);
    }
}