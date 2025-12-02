<?php

namespace MetaDraw\Kyc\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadService
{
    protected string $disk;

    public function __construct()
    {
        $this->disk = config('kyc.storage.disk', 's3');
    }

    /**
     * Upload a KYC document and return the URL
     */
    public function uploadKycDocument(int $userId, string $type, UploadedFile $file): string
    {
        $filename = $this->generateFilename($userId, $type, $file);
        
        $path = Storage::disk($this->disk)->put($filename, $file);
        
        return Storage::disk($this->disk)->url($path);
    }

    /**
     * Delete a file from storage
     */
    public function delete(string $url): bool
    {
        // Extract path from URL
        $path = parse_url($url, PHP_URL_PATH);
        
        if ($path) {
            return Storage::disk($this->disk)->delete($path);
        }
        
        return false;
    }

    /**
     * Generate a unique filename for KYC documents
     */
    protected function generateFilename(int $userId, string $type, UploadedFile $file): string
    {
        return sprintf(
            'kyc/%s/%s-%s.%s',
            $userId,
            $type,
            uniqid(),
            $file->getClientOriginalExtension()
        );
    }
}