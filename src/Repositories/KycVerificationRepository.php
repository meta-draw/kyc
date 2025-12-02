<?php

namespace MetaDraw\Kyc\Repositories;

use MetaDraw\Kyc\Models\KycVerification;
use MetaDraw\Kyc\Enums\KycStatus;

class KycVerificationRepository
{
    public function create(array $data): KycVerification
    {
        return KycVerification::query()->create($data);
    }

    public function findByUserId(int $userId): ?KycVerification
    {
        return KycVerification::query()
            ->where('user_id', $userId)
            ->latest()
            ->first();
    }

    public function findPendingOrProcessingByUserId(int $userId): ?KycVerification
    {
        return KycVerification::query()
            ->where('user_id', $userId)
            ->whereIn('status', [KycStatus::Pending, KycStatus::Processing])
            ->latest()
            ->first();
    }

    public function updateDocumentUrl(KycVerification $verification, string $type, string $url): bool
    {
        $field = $type === 'id-front' ? 'id_front_url' : 'id_back_url';
        return $verification->update([$field => $url]);
    }

    public function findById(int $id): ?KycVerification
    {
        return KycVerification::query()->find($id);
    }

    public function update(KycVerification $verification, array $data): bool
    {
        return $verification->update($data);
    }
}