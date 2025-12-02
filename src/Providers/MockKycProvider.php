<?php

namespace MetaDraw\Kyc\Providers;

use MetaDraw\Kyc\Contracts\KycProviderInterface;
use MetaDraw\Kyc\Models\KycVerification;

class MockKycProvider implements KycProviderInterface
{
    /**
     * Mock implementation - always returns success
     */
    public function verify(KycVerification $verification): array
    {
        // In a real implementation, this would call the third-party API
        return [
            'success' => true,
            'message' => 'Verification submitted successfully',
            'data' => [
                'reference_id' => 'MOCK-' . uniqid(),
            ],
        ];
    }

    /**
     * Mock implementation - returns verified status if documents are uploaded
     */
    public function checkStatus(string $referenceId): array
    {
        // In a real implementation, this would check with the third-party API
        return [
            'status' => 'verified',
            'message' => 'Verification completed',
            'data' => [
                'reference_id' => $referenceId,
                'verified_at' => now()->toIso8601String(),
            ],
        ];
    }

    /**
     * Mock implementation - always returns success
     */
    public function submitDocuments(string $referenceId, array $documents): array
    {
        // In a real implementation, this would submit documents to the third-party API
        return [
            'success' => true,
            'message' => 'Documents submitted successfully',
        ];
    }
}