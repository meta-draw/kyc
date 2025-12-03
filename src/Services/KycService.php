<?php

namespace MetaDraw\Kyc\Services;

use MetaDraw\Kyc\Contracts\KycClient;
use MetaDraw\Kyc\Repositories\KycVerificationRepository;

class KycService
{
    private KycClient $client;
    private KycVerificationRepository $repository;

    public function __construct(KycClient $client, KycVerificationRepository $repository)
    {
        $this->client = $client;
        $this->repository = $repository;
    }

    public function verify(string $idCard, string $mobile, string $realName, int $userId): array
    {
        $result = $this->client->verify($idCard, $mobile, $realName);
        
        $verification = $this->repository->create(
            userId: $userId,
            idCard: $idCard, 
            mobile: $mobile,
            realName: $realName,
            status: $result['status'],
            reason: $result['reason']
        );

        return [
            'status' => $verification->status,
            'reason' => $verification->reason,
        ];
    }

    public function status($userId): array
    {
        $verification = $this->repository->findByUser($userId);
        
        return [
            'status' => $verification ? $verification->status : false,
            'reason' => $verification?->reason,
        ];
    }

}