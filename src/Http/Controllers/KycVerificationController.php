<?php

namespace MetaDraw\Kyc\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
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
        try {
            DB::beginTransaction();
            
            $verification = $this->kycService->createVerification(
                $request->user(),
                $request->validated()
            );
            
            DB::commit();
            
            return response()->json([
                'isSuccess' => true,
                'message' => 'KYC verification submitted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'isSuccess' => false,
                'message' => 'Failed to submit KYC verification: ' . $e->getMessage(),
            ], 500);
        }
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
        try {
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
            
            // Update the URL in the verification record
            $this->repository->updateDocumentUrl($verification, $request->validated()['type'], $url);
            
            return response()->json([
                'isSuccess' => true,
                'message' => 'Document uploaded successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'isSuccess' => false,
                'message' => 'Failed to upload document: ' . $e->getMessage(),
            ], 500);
        }
    }
}