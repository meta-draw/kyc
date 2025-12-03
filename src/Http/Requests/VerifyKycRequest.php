<?php

namespace MetaDraw\Kyc\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use MetaDraw\Kyc\Repositories\KycVerificationRepository;

class VerifyKycRequest extends FormRequest
{
    public function authorize(): bool
    {
        $repository = app(KycVerificationRepository::class);
        $existingVerification = $repository->findVerifiedByUser($this->user()->id);
        
        abort_if($existingVerification, 422, 'User already verified');
        
        return true;
    }

    public function rules(): array
    {
        return [
            'id_card' => 'required|string|size:18',
            'mobile' => 'required|string|size:11|regex:/^1[3-9]\d{9}$/',
            'real_name' => 'required|string|min:2|max:50',
        ];
    }
}