<?php

namespace MetaDraw\Kyc\Services;

use MetaDraw\Kyc\Contracts\KycProviderInterface;
use MetaDraw\Kyc\Models\KycVerification;
use MetaDraw\Kyc\Repositories\KycVerificationRepository;
use MetaDraw\Kyc\Enums\KycStatus;
use Carbon\Carbon;

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
                    $updateData['verified_at'] = Carbon::now();
                }
                
                if ($result['status'] === 'rejected' && isset($result['message'])) {
                    $updateData['rejection_reason'] = $result['message'];
                }
            }
            
            $this->repository->update($verification, $updateData);
        }
        
        return $result;
    }
}