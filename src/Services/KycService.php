<?php

namespace MetaDraw\Kyc\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use MetaDraw\Kyc\Models\KycVerification;
use MetaDraw\Kyc\Models\KycDocument;

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
     * Upload a document for KYC verification
     */
    public function uploadDocument(KycVerification $verification, string $type, UploadedFile $file): KycDocument
    {
        // Check if document type already exists
        $existingDocument = $verification->documents()
            ->where('type', $type)
            ->first();
            
        if ($existingDocument) {
            // Delete old file
            Storage::disk('public')->delete($existingDocument->file_path);
            $existingDocument->delete();
        }
        
        // Store new file
        $path = $file->store('kyc-documents/' . $verification->id, 'public');
        
        return KycDocument::create([
            'verification_id' => $verification->id,
            'type' => $type,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);
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