<?php

namespace MetaDraw\Kyc\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use MetaDraw\Kyc\Contracts\KycClient;

class AliyunKycClient implements KycClient
{
    private string $appCode;
    private string $host = 'https://slytransf.market.alicloudapi.com';
    private string $path = '/mobile_transfer';

    public function __construct()
    {
        $this->appCode = config('kyc.aliyun.app_code');
    }

    public function verify(string $idCard, string $mobile, string $realName): array
    {
        $response = Http::withHeaders([
                'Authorization' => 'APPCODE ' . $this->appCode,
            ])
            ->get($this->host . $this->path, [
                'idcard' => $idCard,
                'mobile' => $mobile,
                'name' => $realName,
            ]);

        $apiResponse = $response->json();
        
        Log::info('Aliyun KYC API Response', $apiResponse);
        
        return [
            'status' => $this->determineStatus($apiResponse),
            'reason' => $this->determineReason($apiResponse),
        ];
    }

    private function determineStatus(array $response): bool
    {
        if (($response['code'] ?? null) !== 200) {
            return false;
        }
        
        $result = $response['data']['result'] ?? null;
        return $result === '0';
    }

    private function determineReason(array $response): ?string
    {
        $code = $response['code'] ?? null;
        
        if ($code !== 200) {
            return match ($code) {
                400 => '参数错误',
                500 => '系统异常，请联系服务商',
                501 => '第三方服务异常',
                604 => '接口停用',
                1001 => '其他错误',
                default => $response['msg'] ?? '未知错误',
            };
        }
        
        $result = $response['data']['result'] ?? null;
        
        return match ($result) {
            '0' => null,
            '1' => '信息不匹配',
            '2' => '无记录',
            default => '未知验证结果',
        };
    }
}