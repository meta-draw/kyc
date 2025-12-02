<?php

namespace MetaDraw\Kyc\Services;

use MetaDraw\Kyc\Contracts\KycProviderInterface;
use MetaDraw\Kyc\Models\KycVerification;
use MetaDraw\Kyc\Repositories\KycVerificationRepository;

class KycService
{
    public function __construct(
        protected KycVerificationRepository $repository,
        protected ?KycProviderInterface $provider = null
    ) {}

    /**
     * Create a new KYC verification
     */
    public function createVerification($user, array $data): ?KycVerification
    {
        $data['user_id'] = $user->id;
        
        // Remove validation field before creating
        unset($data['no_existing_verification']);
        
        $verification = $this->repository->create($data);
        
        if (!$verification) {
            return null;
        }
        
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
        if ($result['status'] === 'verified' && $verification->status !== 'verified') {
            $this->repository->update($verification, [
                'status' => 'verified',
                'verified_at' => now(),
            ]);
        } elseif ($result['status'] === 'rejected' && $verification->status !== 'rejected') {
            $this->repository->update($verification, [
                'status' => 'rejected',
                'rejection_reason' => $result['message'] ?? 'Verification failed',
            ]);
        }
        
        return $result;
    }
}