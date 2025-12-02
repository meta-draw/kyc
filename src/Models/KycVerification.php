<?php

namespace MetaDraw\Kyc\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'status',
        'rejection_reason',
        'verified_at',
    ];

    protected $casts = [
        'dob' => 'date',
        'document_issue_date' => 'date',
        'document_expiry_date' => 'date',
        'verified_at' => 'datetime',
    ];

    public function getTable()
    {
        return config('kyc.table_prefix', 'kyc_') . 'verifications';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function hasAllDocuments(): bool
    {
        return $this->id_front_url && $this->id_back_url;
    }
}