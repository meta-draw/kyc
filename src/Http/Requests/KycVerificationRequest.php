<?php

namespace MetaDraw\Kyc\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
        ];
    }

    public function messages(): array
    {
        return [
            'nationality.required' => 'Nationality is required',
            'nationality.size' => 'Nationality must be a 2-letter country code',
            'resident_country.required' => 'Resident country is required',
            'resident_country.size' => 'Resident country must be a 2-letter country code',
            'dob.required' => 'Date of birth is required',
            'dob.date' => 'Date of birth must be a valid date',
            'dob.before' => 'Date of birth must be in the past',
            'first_name.required' => 'First name is required',
            'last_name.required' => 'Last name is required',
            'document_type.required' => 'Document type is required',
            'document_type.in' => 'Document type must be passport, id_card, or driver_license',
            'country_of_issue.required' => 'Country of issue is required',
            'country_of_issue.size' => 'Country of issue must be a 2-letter country code',
            'document_number.required' => 'Document number is required',
            'document_issue_date.required' => 'Document issue date is required',
            'document_issue_date.before_or_equal' => 'Document issue date cannot be in the future',
            'document_expiry_date.required' => 'Document expiry date is required',
            'document_expiry_date.after' => 'Document must not be expired',
        ];
    }

    protected function prepareForValidation(): void
    {
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
        ]);
    }
}