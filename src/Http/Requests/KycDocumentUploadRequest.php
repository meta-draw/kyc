<?php

namespace MetaDraw\Kyc\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KycDocumentUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|string|in:id-front,id-back',
            'data' => 'required|file|image|max:10240', // 10MB max
        ];
    }
}