<?php

namespace MetaDraw\Kyc\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use MetaDraw\Kyc\Models\KycVerification;

class KycService
{
    /**
     * Create a new KYC verification
     */
    public function createVerification($user, array $data): KycVerification
    {
        // Check if user already has an active verification
        $existingVerification = KycVerification::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'processing', 'verified'])
            ->first();
            
        if ($existingVerification) {
            throw new \Exception('An active KYC verification already exists');
        }
        
        $data['user_id'] = $user->id;
        
        return KycVerification::create($data);
    }

    /**
     * Upload a document to S3 and return the URL
     */
    public function uploadDocument(KycVerification $verification, string $type, UploadedFile $file): string
    {
        // Generate a unique filename
        $filename = sprintf(
            'kyc/%s/%s-%s.%s',
            $verification->user_id,
            $type,
            uniqid(),
            $file->getClientOriginalExtension()
        );
        
        // Upload to S3 (or configured disk)
        $disk = config('kyc.storage.disk', 's3');
        $path = Storage::disk($disk)->put($filename, $file);
        
        // Return the full URL
        return Storage::disk($disk)->url($path);
    }

    /**
     * Process KYC verification (placeholder for actual verification logic)
     */
    public function processVerification(KycVerification $verification): void
    {
        // This is where you would integrate with external KYC verification services
        // For now, we'll just update the status
        
        if ($verification->hasAllDocuments()) {
            // Simulate verification process
            $verification->update([
                'status' => 'verified',
                'verified_at' => now(),
            ]);
        }
    }
}