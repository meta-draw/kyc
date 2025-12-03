# Laravel KYC Package

Laravel KYC verification package based on Tencent Cloud's mobile three-factor authentication.

## Installation

```bash
composer require meta-draw/kyc
```

## Configuration

Publish configuration file:

```bash
php artisan vendor:publish --tag=kyc-config
```

Run migrations:

```bash
php artisan migrate
```

Add Tencent Cloud credentials to your `.env` file:

```
KYC_TENCENT_SECRET_ID=your-secret-id
KYC_TENCENT_SECRET_KEY=your-secret-key
```

## Route Middleware Configuration

Configure route middleware based on your authentication method:

```env
# No authentication (default)
KYC_ROUTE_MIDDLEWARE=

# JWT authentication
KYC_ROUTE_MIDDLEWARE=auth:api

# Sanctum authentication
KYC_ROUTE_MIDDLEWARE=auth:sanctum

# Multiple middleware
KYC_ROUTE_MIDDLEWARE=auth:api,throttle:60,1
```

## API Endpoints

### Verification Endpoint

```
POST /api/kyc/verify
```

Request parameters:
```json
{
  "id_card": "ID card number",
  "mobile": "Mobile number",
  "real_name": "Real name"
}
```

Success response:
```json
{
  "status": true,
  "reason": null
}
```

Failure response:
```json
{
  "status": false,
  "reason": "Information does not match"
}
```

### Status Query

```
GET /api/kyc/status
```

Returns the same format as the verification endpoint with the user's latest verification status.

## Code Usage

```php
use MetaDraw\Kyc\Services\KycService;

$kycService = app(KycService::class);

// Verify user
$result = $kycService->verify(
    '110101199001011234',  // ID card number
    '13800138000',         // Mobile number
    'John Doe',            // Real name
    $user->id              // User ID
);

// Query user verification status
$result = $kycService->status($user->id);
```

## Verification Results

- `status`: boolean - Verification result (true: success, false: failure)
- `reason`: string|null - Failure reason
  - `null`: Verification successful
  - `"Information does not match"`: Information mismatch  
  - `"No record found in carrier system"`: No record in carrier system
  - `"API call failed"`: API call failed

## Duplicate Verification Prevention

Users who have already been successfully verified will receive a 422 status code and "User already verified" error message when calling the verification endpoint again.

## License

MIT