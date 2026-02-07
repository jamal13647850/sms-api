## 1. Project Setup

- [x] 1.1 Review Mellipayamak REST API documentation (`webservice-rest.pdf`)
- [x] 1.2 Document API endpoints: SendSMS, SendMultipleSMS, BaseServiceNumber, GetDeliveries2, GetMessages, GetCredit
- [x] 1.3 Document authentication: Username + ApiKey (from panel Developers menu)
- [x] 1.4 Examine existing provider implementations (FarazSMS.php) for patterns to follow
- [x] 1.5 Verify AbstractGateway and Gateway interface compatibility

## 2. Create Melipayamak Gateway Class

- [x] 2.1 Create `src/Melipayamak.php` file with proper namespace and strict_types declaration
- [x] 2.2 Define class extending `AbstractGateway` and implementing `Gateway` interface
- [x] 2.3 Add class constants for API base URL (`https://rest.payamak-panel.com/api/SendSMS/`)
- [x] 2.4 Add endpoint constants: SendSMS, SendMultipleSMS, BaseServiceNumber, GetDeliveries2, GetMessages, GetCredit
- [x] 2.5 Define private properties: username, apiKey (as password), from, url
- [x] 2.6 Add comprehensive docblock explaining ApiKey authentication requirement

## 3. Implement Constructor

- [x] 3.1 Create constructor accepting `username`, `password` (expects ApiKey), `from`, optional `url`
- [x] 3.2 Add docblock clarifying that `password` parameter expects ApiKey from panel settings
- [x] 3.3 Store username, ApiKey (as password), from in instance properties
- [x] 3.4 Use default API URL if custom URL not provided
- [x] 3.5 Add constructor parameter types and validation

## 4. Implement Core SMS Methods

- [x] 4.1 Implement `sendSMS(string|array $to, string $message)` method
  - [x] 4.1.1 Support up to 100 recipients (comma-separated)
  - [x] 4.1.2 Use POST request to SendSMS endpoint
  - [x] 4.1.3 Include username, ApiKey, from, to, text parameters
  - [x] 4.1.4 Handle RetStatus=0 (failure) vs RetStatus=1 (success)
  - [x] 4.1.5 Parse Value field for recId on success

- [x] 4.2 Implement `sendSMSByPattern(string $to, string $message, int|string $bodyId, array $parameters)` method
  - [x] 4.2.1 Use POST request to BaseServiceNumber endpoint
  - [x] 4.2.2 Include username, ApiKey, to, bodyId, text (parameters encoded)
  - [x] 4.2.3 Return standardized response

- [x] 4.3 Implement `sendOneSMSToMultiNumber(array $to, string $message)` method
  - [x] 4.3.1 Delegate to sendSMS() method
  - [x] 4.3.2 Ensure max 100 recipient limit is enforced

- [x] 4.4 Implement `sendMultiSMSToMultiNumber(array $msNum)` method using SendMultipleSMS
  - [x] 4.4.1 Use POST request to SendMultipleSMS endpoint (max 100 pairs)
  - [x] 4.4.2 Set Content-Type: application/json header
  - [x] 4.4.3 Format request body with to[] and text[] arrays
  - [x] 4.4.4 Split large batches (>100) into multiple API calls
  - [x] 4.4.5 Aggregate per-recipient results into single response
  - [x] 4.4.6 Handle ReqStatus and Status fields in response

## 5. Implement Utility Methods

- [x] 5.1 Implement `receiveSMS()` method using GetMessages endpoint
- [x] 5.2 Implement `getSMSStatus($messageId)` method using GetDeliveries2 endpoint
- [x] 5.3 Implement `getCredit()` method using GetCredit endpoint
- [x] 5.4 Implement `addContact(array $contactInfo)` method (return not supported status)

## 6. Implement Response Parsing

- [x] 6.1 Override `parseResponse(string $response)` method in Melipayamak class
- [x] 6.2 Parse consistent JSON structure: `{Value, RetStatus, StrRetStatus}`
- [x] 6.3 Handle RetStatus=1 as success, RetStatus=0 as failure
- [x] 6.4 Map error codes using error code arrays
- [x] 6.5 Return standardized format: `['status' => bool, 'resultCode' => int, 'resultData' => mixed]`
- [x] 6.6 Handle JSON decode errors gracefully
- [x] 6.7 Handle null/empty responses from API

## 7. Add Error Code Mapping

- [x] 7.1 Create SendSMS error codes array with Persian translations:
  - [x] 0: "نام کاربری یا رمز عبور اشتباه می باشد"
  - [x] 1: "درخواست با موفقیت انجام شد"
  - [x] 2: "اعتبار کافی نمی باشد"
  - [x] 3: "محدودیت در ارسال روزانه"
  - [x] 4: "محدودیت در حجم ارسال"
  - [x] 5: "شماره فرستنده معتبر نمی باشد"
  - [x] 6: "سامانه در حال بروزرسانی می باشد"
  - [x] 7: "متن حاوی کلمه فیلتر شده می باشد"
  - [x] 9: "ارسال از خطوط عمومی از طریق وب سرویس امکان پذیر نمی باشد"
  - [x] 10: "کاربر مورد نظر فعال نمی باشد"
  - [x] 11: "ارسال نشده"
  - [x] 12: "مدارک کاربر کامل نمی باشد"
  - [x] 14: "متن حاوی لینک می باشد"
  - [x] 15: "عدم وجود لغو 11 در انتهای متن پیامک"
  - [x] 16: "شماره گیرنده ای یافت نشد"
  - [x] 17: "متن پیامک خالی می باشد"
  - [x] 18: "شماره موبایل معتبر نمی باشد"
- [x] 7.2 Add IP-related security error codes:
  - [x] 108: "مسدود شدن IP به دلیل تلاش ناموفق استفاده از API"
  - [x] 109: "الزام تنظیم IP مجاز برای استفاده از API"
  - [x] 110: "الزام استفاده از ApiKey به جای رمز عبور"
  - [x] 111: "درخواست کننده نامعتبر است"
- [x] 7.3 Create delivery status code mapping:
  - [x] 0: "ارسال شده به مخابرات"
  - [x] 1: "رسیده به گوشی"
  - [x] 2: "نرسیده به گوشی"
  - [x] 3: "خطای مخابراتی"
  - [x] 5: "خطای نامشخص"
  - [x] 8: "رسیده به مخابرات"
  - [x] 16: "نرسیده به مخابرات"
  - [x] 35: "لیست سیاه"
  - [x] 100: "نامشخص"
  - [x] 200: "ارسال شده"
  - [x] 300: "فیلتر شده"
  - [x] 400: "در لیست ارسال"
  - [x] 500: "عدم پذیرش"
- [x] 7.4 Handle unknown error codes with fallback message: "Unknown error ({code})"

## 8. Add Helper Methods

- [x] 8.1 Create private method to execute POST requests with form parameters
- [x] 8.2 Ensure proper cURL configuration (timeout, SSL, headers)
- [x] 8.3 Use inherited `executeRequest()` from AbstractGateway

## 9. Testing & Validation

- [x] 9.1 Verify PSR-12 compliance (4-space indentation, proper braces)
- [x] 9.2 Verify PHP 8.2+ features (strict types, type hints, union types)
- [x] 9.3 Run `composer dump-autoload` to register new class
- [x] 9.4 Verify class loads without errors

## 10. Documentation

- [x] 10.1 Add file-level docblock with class description
- [x] 10.2 Add method docblocks for all public methods (params, return, throws)
- [x] 10.3 Add inline comments for complex logic
- [ ] 10.4 Update README.md with Mellipayamak usage example (optional)

## 11. Optional Test Integration

- [x] 11.1 Create test entries in test.php for Mellipayamak
- [x] 11.2 Add example instantiation with credentials
- [ ] 11.3 Test basic sendSMS functionality if credentials available

## 12. Documentation & ApiKey Clarification

- [x] 12.1 Add file-level docblock with class description
- [x] 12.2 Add prominent docblock on constructor explaining ApiKey authentication
- [x] 12.3 Document that `password` parameter expects ApiKey from panel
- [x] 12.4 Add inline comments for complex logic
- [x] 12.5 Document SendMultipleSMS JSON format requirement
- [x] 12.6 Document recipient limits (100 for SendSMS, 100 for SendMultipleSMS)

## 13. Final Review

- [x] 13.1 Review all Gateway interface methods are implemented
- [x] 13.2 Verify response format matches standard across all methods
- [x] 13.3 Check phone number normalization is applied
- [x] 13.4 Ensure all error codes from PDF are mapped (0-18, 108-111, delivery codes)
- [x] 13.5 Verify ApiKey authentication is correctly implemented
- [x] 13.6 Test SendMultipleSMS JSON format and Content-Type header
- [x] 13.7 Verify no breaking changes to existing providers
