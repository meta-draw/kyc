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
    public function createVerification($user, array $data): KycVerification
    {
        // Check if user already has an active verification
        $existingVerification = $this->repository->findActiveByUserId($user->id);
            
        if ($existingVerification) {
            throw new \Exception('An active KYC verification already exists');
        }
        
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
     * Process documents after upload
     */
    public function processDocumentUpload(KycVerification $verification): void
    {
        if ($verification->hasAllDocuments()) {
            if ($this->provider && $verification->reference_id) {
                // Submit documents to third-party provider
                $result = $this->provider->submitDocuments(
                    $verification->reference_id,
                    [
                        'id_front_url' => $verification->id_front_url,
                        'id_back_url' => $verification->id_back_url,
                    ]
                );
                
                if ($result['success']) {
                    $this->repository->updateStatus($verification, 'processing');
                }
            } else {
                // No provider configured, just mark as processing
                $this->repository->updateStatus($verification, 'processing');
            }
        }
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