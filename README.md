# Laravel KYC Package

A comprehensive KYC (Know Your Customer) verification package for Laravel applications.

## Requirements

- PHP 8.0 or higher
- Laravel 11.0 or higher

## Installation

You can install the package via composer:

```bash
composer require meta-draw/kyc
```

### Publish Configuration

```bash
php artisan vendor:publish --tag=kyc-config
```

### Run Migrations

```bash
php artisan migrate
```

## Configuration

### Authentication

The package supports flexible authentication configuration. By default, it uses `auth:api` middleware.

If your project uses a different authentication middleware (e.g., JWT), you can configure it:

```php
// config/kyc.php
'auth' => [
    'middleware' => 'auth:api', // Default value, works with JWT if configured as api guard
],
```

Or via environment variable:

```bash
# If you need to use a different middleware
KYC_AUTH_MIDDLEWARE=jwt.auth
```

You can also add additional middleware:

```php
// config/kyc.php
'auth' => [
    'middleware' => 'auth:api',
    'additional_middleware' => ['throttle:60,1'],
],
```

## API Endpoints

### 1. Submit KYC Verification

```
POST /api/kyc-verification
```

Request body:
```json
{
    "nationality": "US",
    "residentCountry": "US",
    "dob": "1990-01-01",
    "firstName": "John",
    "lastName": "Doe",
    "middleName": "Michael",
    "documentType": "passport",
    "countryOfIssue": "US",
    "documentNumber": "123456789",
    "documentIssueDate": "2020-01-01",
    "documentExpiryDate": "2030-01-01"
}
```

Response:
```json
{
    "isSuccess": true,
    "message": "KYC verification submitted successfully"
}
```

### 2. Get KYC Status

```
GET /api/kyc-verification
```

Response:
```json
{
    "status": "pending" // or "processing", "verified", "rejected", "expired", "not_submitted"
}
```

### 3. Upload Documents

```
POST /api/kyc-verification/upload
```

Form data:
- `type`: "id-front" or "id-back"
- `data`: Image file (max 10MB)

Response:
```json
{
    "isSuccess": true,
    "message": "Document uploaded successfully"
}
```

## Usage in Your Application

### Get User's KYC Status

```php
use MetaDraw\Kyc\Models\KycVerification;

$verification = KycVerification::where('user_id', $user->id)
    ->latest()
    ->first();

if ($verification && $verification->isVerified()) {
    // User is KYC verified
}
```

### Check if User Has Uploaded All Documents

```php
if ($verification->hasAllDocuments()) {
    // User has uploaded both front and back of ID
}
```

## Storage Configuration

By default, the package uploads files to S3. You can configure the storage disk:

```php
// config/kyc.php
'storage' => [
    'disk' => env('KYC_STORAGE_DISK', 's3'),
],
```

Or via environment variable:

```bash
KYC_STORAGE_DISK=s3
```

Make sure your S3 credentials are properly configured in `config/filesystems.php`.

## License

The MIT License (MIT).