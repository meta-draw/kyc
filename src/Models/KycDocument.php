<?php

namespace MetaDraw\Kyc\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KycDocument extends Model
{
    protected $fillable = [
        'verification_id',
        'type',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
    ];

    public function getTable()
    {
        return config('kyc.table_prefix', 'kyc_') . 'documents';
    }

    public function verification(): BelongsTo
    {
        return $this->belongsTo(KycVerification::class, 'verification_id');
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }
}