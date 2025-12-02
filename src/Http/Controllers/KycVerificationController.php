<?php

namespace MetaDraw\Kyc\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use MetaDraw\Kyc\Http\Requests\KycVerificationRequest;
use MetaDraw\Kyc\Http\Requests\KycDocumentUploadRequest;
use MetaDraw\Kyc\Services\KycService;
use MetaDraw\Kyc\Services\UploadService;
use MetaDraw\Kyc\Repositories\KycVerificationRepository;

class KycVerificationController extends Controller
{
    public function __construct(
        protected KycService $kycService,
        protected UploadService $uploadService,
        protected KycVerificationRepository $repository
    ) {}

    /**
     * Create a new KYC verification
     */
    public function store(KycVerificationRequest $request): JsonResponse
    {
        $verification = $this->kycService->createVerification(
            $request->user(),
            $request->validated()
        );
        
        if ($verification) {
            return response()->json([
                'isSuccess' => true,
                'message' => 'KYC verification submitted successfully',
            ]);
        }
        
        return response()->json([
            'isSuccess' => false,
            'message' => 'Failed to submit KYC verification',
        ], 500);
    }

    /**
     * Get current KYC verification status
     */
    public function show(Request $request): JsonResponse
    {
        $verification = $this->repository->findByUserId($request->user()->id);
            
        if (!$verification) {
            return response()->json([
                'status' => 'not_submitted'
            ]);
        }
        
        // Check with third-party provider for latest status
        $this->kycService->checkVerificationStatus($verification);
        
        return response()->json([
            'status' => $verification->fresh()->status
        ]);
    }

    /**
     * Upload KYC documents
     */
    public function upload(KycDocumentUploadRequest $request): JsonResponse
    {
        $verification = $this->repository->findPendingOrProcessingByUserId($request->user()->id);
            
        if (!$verification) {
            return response()->json([
                'isSuccess' => false,
                'message' => 'No active KYC verification found',
            ], 404);
        }
        
        // Upload file and get URL
        $url = $this->uploadService->uploadKycDocument(
            $verification->user_id,
            $request->validated()['type'],
            $request->file('data')
        );
        
        if ($url) {
            // Update the URL in the verification record
            $updated = $this->repository->updateDocumentUrl($verification, $request->validated()['type'], $url);
            
            if ($updated) {
                return response()->json([
                    'isSuccess' => true,
                    'message' => 'Document uploaded successfully',
                ]);
            }
        }
        
        return response()->json([
            'isSuccess' => false,
            'message' => 'Failed to upload document',
        ], 500);
    }
}