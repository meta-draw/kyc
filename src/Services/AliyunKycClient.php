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
                400 => 'Parameter error',
                500 => 'System error, please contact service provider',
                501 => 'Third-party service error',
                604 => 'Interface disabled',
                1001 => 'Other error',
                default => $response['msg'] ?? 'Unknown error',
            };
        }
        
        $result = $response['data']['result'] ?? null;
        
        return match ($result) {
            '0' => null,
            '1' => 'Information does not match',
            '2' => 'No record found',
            default => 'Unknown verification result',
        };
    }
}