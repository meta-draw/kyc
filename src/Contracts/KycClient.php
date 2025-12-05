<?php

namespace MetaDraw\Kyc\Contracts;

interface KycClient
{
    public function verify(string $idCard, string $mobile, string $realName): array;
}