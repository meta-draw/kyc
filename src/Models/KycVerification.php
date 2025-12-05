<?php

namespace MetaDraw\Kyc\Models;

use Illuminate\Database\Eloquent\Model;

class KycVerification extends Model
{
    protected $fillable = [
        'user_id',
        'id_card',
        'mobile',
        'real_name',
        'status',
        'reason',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}