<?php

namespace MetaDraw\Kyc\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaDraw\Kyc\Enums\KycStatus;

class KycVerification extends Model
{
    protected $fillable = [
        'user_id',
        'nationality',
        'resident_country',
        'dob',
        'first_name',
        'last_name',
        'middle_name',
        'document_type',
        'country_of_issue',
        'document_number',
        'document_issue_date',
        'document_expiry_date',
        'id_front_url',
        'id_back_url',
        'reference_id',
        'status',
        'rejection_reason',
        'verified_at',
    ];

    protected $casts = [
        'dob' => 'date',
        'document_issue_date' => 'date',
        'document_expiry_date' => 'date',
        'verified_at' => 'datetime',
        'status' => KycStatus::class,
    ];

    public function getTable()
    {
        return config('kyc.table_prefix', 'kyc_') . 'verifications';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }
}