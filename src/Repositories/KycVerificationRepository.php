<?php

namespace MetaDraw\Kyc\Repositories;

use MetaDraw\Kyc\Models\KycVerification;
use Illuminate\Database\Eloquent\Collection;

class KycVerificationRepository
{
    public function __construct(
        protected KycVerification $model
    ) {}

    public function create(array $data): KycVerification
    {
        return $this->model->create($data);
    }

    public function update(KycVerification $verification, array $data): bool
    {
        return $verification->update($data);
    }

    public function findById(int $id): ?KycVerification
    {
        return $this->model->find($id);
    }

    public function findByUserId(int $userId): ?KycVerification
    {
        return $this->model
            ->where('user_id', $userId)
            ->latest()
            ->first();
    }

    public function findActiveByUserId(int $userId): ?KycVerification
    {
        return $this->model
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'processing', 'verified'])
            ->latest()
            ->first();
    }

    public function findPendingOrProcessingByUserId(int $userId): ?KycVerification
    {
        return $this->model
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'processing'])
            ->latest()
            ->first();
    }

    public function updateDocumentUrl(KycVerification $verification, string $type, string $url): bool
    {
        $field = $type === 'id-front' ? 'id_front_url' : 'id_back_url';
        return $verification->update([$field => $url]);
    }

    public function updateStatus(KycVerification $verification, string $status): bool
    {
        return $verification->update(['status' => $status]);
    }
}