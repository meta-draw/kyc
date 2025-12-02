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
        protected KycProviderInterface $provider
    ) {}

    /**
     * Create a new KYC verification
     */
    public function createVerification($user, array $data): array
    {
        $data['user_id'] = $user->id;
        
        $verification = $this->repository->create($data);
        
        // Submit to third-party provider
        $result = $this->provider->verify($verification);
        
        if ($result['success'] && isset($result['data']['reference_id'])) {
            $updateData = [
                'reference_id' => $result['data']['reference_id'],
            ];
            
            // Update status based on provider response
            if (isset($result['status'])) {
                $updateData['status'] = match ($result['status']) {
                    'verified' => KycStatus::Verified,
                    'rejected' => KycStatus::Rejected,
                    'processing' => KycStatus::Processing,
                    default => KycStatus::Pending,
                };
                
                if ($result['status'] === 'verified') {
                    $updateData['verified_at'] = now();
                }
                
                if ($result['status'] === 'rejected' && isset($result['message'])) {
                    $updateData['rejection_reason'] = $result['message'];
                }
            }
            
            $this->repository->update($verification, $updateData);
        }
        
        return $result;
    }


    /**
     * Check verification status with third-party provider
     */
    public function checkVerificationStatus(KycVerification $verification): array
    {
        if (!$verification->reference_id) {
            return [
                'status' => $verification->status,
                'message' => 'No reference ID available',
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