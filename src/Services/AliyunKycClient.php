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
        
        if (empty($this->appCode)) {
            throw new \InvalidArgumentException('KYC_ALIYUN_APP_CODE is required');
        }
    }

    public function verify(string $idCard, string $mobile, string $realName): array
    {
        try {
            $response = Http::withHeaders([
                    'Authorization' => 'APPCODE ' . $this->appCode,
                ])
                ->timeout(30)
                ->get($this->host . $this->path, [
                    'idcard' => $idCard,
                    'mobile' => $mobile,
                    'name' => $realName,
                ]);

            if (!$response->successful()) {
                Log::error('Aliyun KYC API HTTP Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                return [
                    'status' => false,
                    'reason' => 'API request failed with status: ' . $response->status(),
                ];
            }

            $apiResponse = $response->json();
            
            if (!is_array($apiResponse)) {
                Log::error('Aliyun KYC API Invalid Response', ['response' => $response->body()]);
                
                return [
                    'status' => false,
                    'reason' => 'Invalid API response format',
                ];
            }
            
            Log::info('Aliyun KYC API Response', $apiResponse);
            
            return [
                'status' => $this->determineStatus($apiResponse),
                'reason' => $this->determineReason($apiResponse),
            ];
        } catch (\Exception $e) {
            Log::error('Aliyun KYC API Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'status' => false,
                'reason' => 'API call failed: ' . $e->getMessage(),
            ];
        }
    }

    private function determineStatus(array $response): bool
    {
        $success = $response['success'] ?? false;
        $code = $response['code'] ?? null;
        
        // Aliyun APIs commonly use code 200 for HTTP success, 0 for business success
        return $success === true && ($code === 200 || $code === 0);
    }

    private function determineReason(array $response): ?string
    {
        $success = $response['success'] ?? false;
        $code = $response['code'] ?? null;
        
        if ($success === true && ($code === 200 || $code === 0)) {
            return null;
        }
        
        return $response['msg'] ?? 'Unknown verification result';
    }
}