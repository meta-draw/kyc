<?php

namespace MetaDraw\Kyc\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use MetaDraw\Kyc\Models\KycVerification;
use MetaDraw\Kyc\Models\KycDocument;
use MetaDraw\Kyc\Http\Requests\KycVerificationRequest;
use MetaDraw\Kyc\Http\Requests\KycDocumentUploadRequest;
use MetaDraw\Kyc\Services\KycService;

class KycVerificationController extends Controller
{
    protected KycService $kycService;

    public function __construct(KycService $kycService)
    {
        $this->kycService = $kycService;
    }

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
        $verification = KycVerification::where('user_id', $request->user()->id)
            ->latest()
            ->first();
            
        if (!$verification) {
            return response()->json([
                'status' => 'not_submitted'
            ]);
        }
        
        return response()->json([
            'status' => $verification->status
        ]);
    }

    /**
     * Upload KYC documents
     */
    public function upload(KycDocumentUploadRequest $request): JsonResponse
    {
        try {
            $verification = KycVerification::where('user_id', $request->user()->id)
                ->whereIn('status', ['pending', 'processing'])
                ->latest()
                ->first();
                
            if (!$verification) {
                return response()->json([
                    'isSuccess' => false,
                    'message' => 'No active KYC verification found',
                ], 404);
            }
            
            $document = $this->kycService->uploadDocument(
                $verification,
                $request->validated()['type'],
                $request->file('data')
            );
            
            // Check if all documents are uploaded and update status
            if ($verification->hasAllDocuments()) {
                $verification->update(['status' => 'processing']);
            }
            
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