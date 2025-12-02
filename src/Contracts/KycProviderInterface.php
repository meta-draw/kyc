<?php

namespace MetaDraw\Kyc\Contracts;

use MetaDraw\Kyc\Models\KycVerification;

interface KycProviderInterface
{
    /**
     * Verify KYC information with third-party provider
     *
     * @param KycVerification $verification
     * @return array{success: bool, message: string, data?: array}
     */
    public function verify(KycVerification $verification): array;

    /**
     * Check verification status with third-party provider
     *
     * @param string $referenceId
     * @return array{status: string, message?: string, data?: array}
     */
    public function checkStatus(string $referenceId): array;
}