<?php

namespace MetaDraw\Kyc\Repositories;

use MetaDraw\Kyc\Models\KycVerification;

class KycVerificationRepository
{
    public function create(int $userId, string $idCard, string $mobile, string $realName, bool $status, ?string $reason): KycVerification
    {
        return KycVerification::create([
            'user_id' => $userId,
            'id_card' => $idCard,
            'mobile' => $mobile,
            'real_name' => $realName,
            'status' => $status,
            'reason' => $reason,
        ]);
    }
    
    public function findByUser(int $userId): ?KycVerification
    {
        return KycVerification::where('user_id', $userId)
            ->latest()
            ->first();
    }
    
    public function findVerifiedByUser(int $userId): ?KycVerification
    {
        return KycVerification::where('user_id', $userId)
            ->where('status', true)
            ->latest()
            ->first();
    }
}