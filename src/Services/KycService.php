<?php

namespace MetaDraw\Kyc\Services;

use MetaDraw\Kyc\Contracts\KycProviderInterface;
use MetaDraw\Kyc\Models\KycVerification;
use MetaDraw\Kyc\Repositories\KycVerificationRepository;
use MetaDraw\Kyc\Enums\KycStatus;
use Illuminate\Support\Carbon;

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

    /**
     * Check verification status with third-party provider and update local status
     */
    public function checkVerificationStatus(KycVerification $verification): array
    {
        if (!$verification->reference_id) {
            return [
                'status' => $verification->status->value,
                'message' => 'No reference ID available',
            ];
        }
        
        $result = $this->provider->checkStatus($verification->reference_id);
        
        // Update local status if provider returned new status
        if (isset($result['status'])) {
            $this->updateVerificationStatusFromProvider($verification, $result);
        }
        
        return $result;
    }

    /**
     * Update verification status based on provider response
     */
    private function updateVerificationStatusFromProvider(KycVerification $verification, array $result): void
    {
        $newStatus = $this->mapProviderStatus($result['status']);
        
        // Skip if status hasn't changed
        if ($newStatus === $verification->status) {
            return;
        }
        
        $updateData = ['status' => $newStatus];
        
        // Add additional fields based on status
        if ($newStatus === KycStatus::Verified) {
            $updateData['verified_at'] = Carbon::now();
        }
        
        if ($newStatus === KycStatus::Rejected && isset($result['message'])) {
            $updateData['rejection_reason'] = $result['message'];
        }
        
        $this->repository->update($verification, $updateData);
    }

    /**
     * Map provider status string to KycStatus enum
     */
    private function mapProviderStatus(string $providerStatus): KycStatus
    {
        return match ($providerStatus) {
            'verified' => KycStatus::Verified,
            'rejected' => KycStatus::Rejected,
            'processing' => KycStatus::Processing,
            'expired' => KycStatus::Expired,
            default => KycStatus::Pending,
        };
    }
}