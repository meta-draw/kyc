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
            $this->repository->update($verification, ['reference_id' => $result['data']['reference_id']]);
        }
        
        // Update status if provider returned one
        $this->updateVerificationStatusFromProvider($verification->id, $result);
        
        return $result;
    }

    /**
     * Check verification status with third-party provider and update local status
     */
    public function checkVerificationStatus(int $verificationId): array
    {
        $verification = $this->repository->findById($verificationId);
        
        if (!$verification) {
            return [
                'status' => 'not_found',
                'message' => 'Verification not found',
            ];
        }
        
        if (!$verification->reference_id) {
            return [
                'status' => $verification->status->value,
                'message' => 'No reference ID available',
            ];
        }
        
        $result = $this->provider->checkStatus($verification->reference_id);
        $this->updateVerificationStatusFromProvider($verificationId, $result);
        
        return $result;
    }

    /**
     * Update verification status based on provider response
     */
    private function updateVerificationStatusFromProvider(int $verificationId, array $result): void
    {
        $verification = $this->repository->findById($verificationId);
        
        if (!$verification) {
            return;
        }
        
        $newStatus = $result['status'];
        
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
}