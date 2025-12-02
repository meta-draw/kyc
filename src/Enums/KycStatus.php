<?php

namespace MetaDraw\Kyc\Enums;

enum KycStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Verified = 'verified';
    case Rejected = 'rejected';
    case Expired = 'expired';
}