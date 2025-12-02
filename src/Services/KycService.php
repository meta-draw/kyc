<?php

namespace MetaDraw\Kyc\Services;

use MetaDraw\Kyc\Contracts\KycProviderInterface;
use MetaDraw\Kyc\Models\KycVerification;
use MetaDraw\Kyc\Repositories\KycVerificationRepository;
use MetaDraw\Kyc\Enums\KycStatus;

class KycService
{
    public function __construct(
        protected KycVerificationRepository $repository,
        protected ?KycProviderInterface $provider = null
    ) {}

    /**
     * Create a new KYC verification
     */
    public function createVerification($user, array $data): KycVerification
    {
        $data['user_id'] = $user->id;
        
        $verification = $this->repository->create($data);
        
        // Submit to third-party provider if available
        if ($this->provider) {
            $result = $this->provider->verify($verification);
            
            if ($result['success'] && isset($result['data']['reference_id'])) {
                $this->repository->update($verification, [
                    'reference_id' => $result['data']['reference_id'],
                ]);
            }
        }
        
        return $verification;
    }


    /**
     * Check verification status with third-party provider
     */
    public function checkVerificationStatus(KycVerification $verification): array
    {
        if (!$this->provider || !$verification->reference_id) {
            return [
                'status' => $verification->status,
                'message' => 'No provider configured or reference ID available',
            ];
        }
        
        $result = $this->provider->checkStatus($verification->reference_id);
        
        // Update local status based on provider response
        if ($result['status'] === 'verified' && $verification->status !== KycStatus::Verified) {
            $this->repository->update($verification, [
                'status' => KycStatus::Verified,
                'verified_at' => now(),
            ]);
        } elseif ($result['status'] === 'rejected' && $verification->status !== KycStatus::Rejected) {
            $this->repository->update($verification, [
                'status' => KycStatus::Rejected,
                'rejection_reason' => $result['message'] ?? 'Verification failed',
            ]);
        }
        
        return $result;
    }
}