<?php

namespace MetaDraw\Kyc\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use MetaDraw\Kyc\Contracts\KycClient;

class TencentKycClient implements KycClient
{
    private string $secretId;
    private string $secretKey;
    private string $apiUrl = 'https://ap-beijing.cloudmarket-apigw.com/service-4epp7bin/phone3element';

    public function __construct()
    {
        $this->secretId = config('kyc.tencent.secret_id');
        $this->secretKey = config('kyc.tencent.secret_key');
    }

    public function verify(string $idCard, string $mobile, string $realName): array
    {
        $datetime = Carbon::now('UTC')->format('D, d M Y H:i:s T');
        $signStr = "x-date: {$datetime}";
        $sign = base64_encode(hash_hmac('sha1', $signStr, $this->secretKey, true));
        $auth = sprintf('{"id": "%s", "x-date": "%s", "signature": "%s"}', $this->secretId, $datetime, $sign);
        
        $response = Http::asForm()
            ->withHeaders([
                'Authorization' => $auth,
            ])
            ->post($this->apiUrl, [
                'idCard' => $idCard,
                'mobile' => $mobile,
                'realName' => $realName,
            ]);

        $apiResponse = $response->json();
        
        Log::info('Tencent KYC API Response', $apiResponse);
        
        return [
            'status' => $this->determineStatus($apiResponse),
            'reason' => $this->determineReason($apiResponse),
        ];
    }

    private function determineStatus(array $response): bool
    {
        if (($response['error_code'] ?? -1) !== 0) {
            return false;
        }

        $verificationResult = $response['result']['VerificationResult'] ?? null;
        return $verificationResult === '1';
    }

    private function determineReason(array $response): ?string
    {
        if (($response['error_code'] ?? -1) !== 0) {
            return $response['reason'] ?? 'Unknown verification result';
        }

        $verificationResult = $response['result']['VerificationResult'] ?? null;
        
        return match ($verificationResult) {
            '1' => null,
            '-1' => 'Information does not match',
            '0' => 'No record found in carrier system',
            default => 'Unknown verification result',
        };
    }
}