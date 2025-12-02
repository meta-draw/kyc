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

    public function messages(): array
    {
        return [
            'type.required' => 'Document type is required',
            'type.in' => 'Document type must be id-front or id-back',
            'data.required' => 'Document file is required',
            'data.file' => 'Document must be a file',
            'data.image' => 'Document must be an image (jpg, jpeg, png, bmp, gif, svg, or webp)',
            'data.max' => 'Document size must not exceed 10MB',
        ];
    }
}