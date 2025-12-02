<?php

namespace MetaDraw\Kyc\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use MetaDraw\Kyc\Repositories\KycVerificationRepository;

class KycVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nationality' => 'required|string|size:2',
            'resident_country' => 'required|string|size:2',
            'dob' => 'required|date|before:today',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'document_type' => 'required|string|in:passport,id_card,driver_license',
            'country_of_issue' => 'required|string|size:2',
            'document_number' => 'required|string|max:255',
            'document_issue_date' => 'required|date|before_or_equal:today',
            'document_expiry_date' => 'required|date|after:today',
            'no_existing_verification' => 'required|accepted|exclude',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Check if user already has an active verification
        $repository = app(KycVerificationRepository::class);
        $existingVerification = $repository->findActiveByUserId($this->user()->id);
        
        $this->merge([
            'nationality' => strtoupper($this->nationality ?? ''),
            'resident_country' => strtoupper($this->residentCountry ?? ''),
            'first_name' => $this->firstName ?? '',
            'last_name' => $this->lastName ?? '',
            'middle_name' => $this->middleName ?? null,
            'document_type' => $this->documentType ?? '',
            'country_of_issue' => strtoupper($this->countryOfIssue ?? ''),
            'document_number' => $this->documentNumber ?? '',
            'document_issue_date' => $this->documentIssueDate ?? '',
            'document_expiry_date' => $this->documentExpiryDate ?? '',
            'no_existing_verification' => $existingVerification ? false : true,
        ]);
    }
}