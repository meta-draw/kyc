# Laravel KYC Package

Laravel KYC verification package based on Aliyun Market's mobile three-factor authentication API. Verifies the consistency between Chinese ID card number, mobile phone number, and real name.

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

Add Aliyun credentials to your `.env` file:

```
KYC_ALIYUN_APP_CODE=your-app-code
```

> **Note**: This package uses Aliyun Market's mobile three-factor authentication API. You need to purchase the service from Aliyun Market and obtain the AppCode.

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
  "id_card": "18-digit Chinese ID card number",
  "mobile": "11-digit Chinese mobile number",
  "real_name": "Chinese real name"
}
```

Example:
```json
{
  "id_card": "110101199001011234",
  "mobile": "13800138000",
  "real_name": "张三"
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
  "reason": "信息不匹配"
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
    '110101199001011234',  // ID card number (18 digits)
    '13800138000',         // Mobile number (11 digits)
    '张三',                // Real name (Chinese name)
    $user->id              // User ID
);

// Query user verification status
$result = $kycService->status($user->id);
```

## Verification Results

- `status`: boolean - 验证结果 (true: 成功, false: 失败)
- `reason`: string|null - 失败原因
  - `null`: 验证成功 (信息一致)
  - `"信息不匹配"`: 身份证号、手机号和姓名不匹配
  - `"无记录"`: 运营商系统中无记录
  - `"参数错误"`: 参数无效或缺失
  - `"系统异常，请联系服务商"`: 系统错误
  - `"第三方服务异常"`: 第三方服务不可用
  - `"接口停用"`: API接口已停用
  - `"其他错误"`: 其他错误，请查看API响应

## Duplicate Verification Prevention

已验证成功的用户再次调用验证接口时，会返回422状态码和"User already verified"错误信息。

## API Response Details

The package handles various response scenarios from Aliyun's API:

### Successful Response (Raw API)
```json
{
    "msg": "成功",
    "success": true,
    "code": 200,
    "data": {
        "order_no": "577564185899175936",
        "result": "0",
        "desc": "一致",
        "channel": "cmcc",
        "sex": "男",
        "birthday": "19930123",
        "address": "江西省遂川县"
    }
}
```

### Error Response (Raw API)
```json
{
    "msg": "参数错误",
    "success": false,
    "code": 400,
    "data": {}
}
```

### Response Processing
- **Success**: `code = 200` and `data.result = "0"`
- **Information Mismatch**: `data.result = "1"`
- **No Record**: `data.result = "2"`
- **API Errors**: `code ≠ 200` (400, 500, 501, 604, 1001)

## License

MIT