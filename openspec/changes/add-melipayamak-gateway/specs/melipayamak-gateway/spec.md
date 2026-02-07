## ADDED Requirements

### Requirement: Gateway implements Gateway interface
The Melipayamak gateway class SHALL implement the `jamal13647850\smsapi\Gateway` interface completely, providing all required methods with correct signatures.

#### Scenario: Class implements all interface methods
- **WHEN** the Melipayamak class is instantiated
- **THEN** it SHALL provide implementations for: sendSMS, sendSMSByPattern, sendOneSMSToMultiNumber, sendMultiSMSToMultiNumber, receiveSMS, getSMSStatus, getCredit, addContact

### Requirement: Constructor accepts authentication credentials with ApiKey
The Melipayamak gateway SHALL accept username, ApiKey (as password parameter), and sender number in its constructor, with optional API URL override.

#### Scenario: Successful instantiation with credentials
- **WHEN** instantiating with `new Melipayamak($username, $apiKey, $from)`
- **THEN** the gateway SHALL store credentials internally for API authentication
- **AND** the second parameter SHALL be the ApiKey from Mellipayamak panel settings (Developers menu)

#### Scenario: Instantiation with custom API URL
- **WHEN** instantiating with `new Melipayamak($username, $apiKey, $from, $customUrl)`
- **THEN** the gateway SHALL use the provided custom URL instead of the default

#### Scenario: ApiKey documentation is clear
- **WHEN** examining constructor documentation
- **THEN** it SHALL clearly state that password parameter expects ApiKey, not account password

### Requirement: Send SMS to single recipient
The gateway SHALL send SMS messages to a single recipient using the Mellipayamak REST API SendSMS endpoint.

#### Scenario: Send SMS to single phone number
- **WHEN** calling `sendSMS('09123456789', 'Hello World')`
- **THEN** the gateway SHALL make a POST request to `https://rest.payamak-panel.com/api/SendSMS/SendSMS`
- **AND** the request SHALL include username, password, from, to, and text parameters
- **AND** it SHALL return a response with status, resultCode, and resultData

### Requirement: Send SMS to multiple recipients
The gateway SHALL support sending the same SMS to multiple recipients by accepting an array of phone numbers.

#### Scenario: Send SMS to multiple phone numbers
- **WHEN** calling `sendSMS(['09123456789', '09129876543'], 'Hello World')`
- **THEN** the gateway SHALL format recipients as comma-separated or JSON encoded
- **AND** it SHALL make a single API call to send to all recipients
- **AND** it SHALL return a response with status, resultCode, and resultData

### Requirement: Send SMS using pattern/template
The gateway SHALL support sending SMS using predefined patterns/templates via the BaseServiceNumber API endpoint.

#### Scenario: Send pattern SMS with bodyId and parameters
- **WHEN** calling `sendSMSByPattern('09123456789', '', 123, ['name' => 'John', 'code' => '1234'])`
- **THEN** the gateway SHALL make a POST request to `https://rest.payamak-panel.com/api/SendSMS/BaseServiceNumber`
- **AND** the request SHALL include username, password, to, bodyId, and text (parameters encoded)
- **AND** it SHALL return a response with status, resultCode, and resultData

### Requirement: Get account credit
The gateway SHALL retrieve the current account credit/balance using the GetCredit API endpoint.

#### Scenario: Successfully retrieve account credit
- **WHEN** calling `getCredit()`
- **THEN** the gateway SHALL make a POST request to `https://rest.payamak-panel.com/api/SendSMS/GetCredit`
- **AND** the request SHALL include username and password parameters
- **AND** it SHALL return the credit amount as an integer

#### Scenario: Handle credit retrieval failure
- **WHEN** calling `getCredit()` and the API returns an error
- **THEN** the gateway SHALL return 0 as the credit amount

### Requirement: Check message delivery status
The gateway SHALL check the delivery status of a sent message using the GetDeliveries2 API endpoint.

#### Scenario: Check status of sent message
- **WHEN** calling `getSMSStatus($messageId)`
- **THEN** the gateway SHALL make a POST request to `https://rest.payamak-panel.com/api/SendSMS/GetDeliveries2`
- **AND** the request SHALL include username, ApiKey, and recId parameters
- **AND** it SHALL return a status string describing the delivery state

#### Scenario: Map delivery status codes
- **WHEN** the API returns delivery status code 1
- **THEN** the status string SHALL indicate "رسیده به گوشی"

#### Scenario: Map failed delivery status
- **WHEN** the API returns delivery status code 2
- **THEN** the status string SHALL indicate "نرسیده به گوشی"

#### Scenario: Map operator error
- **WHEN** the API returns delivery status code 3
- **THEN** the status string SHALL indicate "خطای مخابراتی"

### Requirement: Receive incoming messages
The gateway SHALL retrieve incoming SMS messages using the GetMessages API endpoint.

#### Scenario: Retrieve received messages
- **WHEN** calling `receiveSMS()`
- **THEN** the gateway SHALL make a POST request to `https://rest.payamak-panel.com/api/SendSMS/GetMessages`
- **AND** the request SHALL include username, password, location, index, count, and optional from parameters
- **AND** it SHALL return an array of received messages

### Requirement: Standardized response format
All gateway methods SHALL return responses in the standardized format: `['status' => bool, 'resultCode' => int, 'resultData' => mixed]`.

#### Scenario: Successful API response
- **WHEN** an API call succeeds
- **THEN** the response SHALL have `status` set to true
- **AND** `resultCode` SHALL be 0 or the provider-specific success code
- **AND** `resultData` SHALL contain the API response data

#### Scenario: Failed API response
- **WHEN** an API call fails or returns an error
- **THEN** the response SHALL have `status` set to false
- **AND** `resultCode` SHALL contain the provider-specific error code
- **AND** `resultData` SHALL contain the error message (in Persian where available)

### Requirement: Error code mapping for SendSMS
The gateway SHALL map Mellipayamak SendSMS error codes to meaningful Persian error messages.

#### Scenario: API returns invalid credentials (code 0)
- **WHEN** the API returns error code 0
- **THEN** resultData SHALL contain "نام کاربری یا رمز عبور اشتباه می باشد"

#### Scenario: API returns insufficient credit (code 2)
- **WHEN** the API returns error code 2
- **THEN** resultData SHALL contain "اعتبار کافی نمی باشد"

#### Scenario: API returns daily limit reached (code 3)
- **WHEN** the API returns error code 3
- **THEN** resultData SHALL contain "محدودیت در ارسال روزانه"

#### Scenario: API returns invalid sender (code 5)
- **WHEN** the API returns error code 5
- **THEN** resultData SHALL contain "شماره فرستنده معتبر نمی باشد"

#### Scenario: API returns filtered words error (code 7)
- **WHEN** the API returns error code 7
- **THEN** resultData SHALL contain "متن حاوی کلمه فیلتر شده می باشد"

#### Scenario: API returns empty message (code 17)
- **WHEN** the API returns error code 17
- **THEN** resultData SHALL contain "متن پیامک خالی می باشد"

#### Scenario: API returns invalid mobile (code 18)
- **WHEN** the API returns error code 18
- **THEN** resultData SHALL contain "شماره موبایل معتبر نمی باشد"

#### Scenario: API returns IP security error (code 108)
- **WHEN** the API returns error code 108
- **THEN** resultData SHALL contain "مسدود شدن IP به دلیل تلاش ناموفق استفاده از API"

#### Scenario: API returns unknown error code
- **WHEN** the API returns an unmapped error code
- **THEN** resultData SHALL contain "Unknown error (code)" with the actual code number

### Requirement: Phone number normalization
The gateway SHALL normalize phone numbers to standard format (09xxxxxxxx) before sending to API.

#### Scenario: Normalize various phone formats
- **WHEN** processing phone numbers in format +989123456789, 989123456789, or 9123456789
- **THEN** the gateway SHALL convert them to 09123456789 format
- **AND** the normalized numbers SHALL be used in API requests

### Requirement: Send different messages to different numbers using SendMultipleSMS
The gateway SHALL support sending different SMS messages to different recipients using the SendMultipleSMS endpoint (max 100 pairs).

#### Scenario: Send bulk different messages via SendMultipleSMS
- **WHEN** calling `sendMultiSMSToMultiNumber(['09123456789' => 'Message 1', '09129876543' => 'Message 2'])`
- **THEN** the gateway SHALL make a POST request to `https://rest.payamak-panel.com/api/SendSMS/SendMultipleSMS`
- **AND** the request SHALL have Content-Type: application/json
- **AND** the request body SHALL include to[] array and text[] array (same length)
- **AND** it SHALL return a response with status, resultCode, and resultData containing per-recipient results

#### Scenario: Handle SendMultipleSMS limit
- **WHEN** calling sendMultiSMSToMultiNumber with more than 100 recipients
- **THEN** the gateway SHALL split into multiple API calls (max 100 per call)
- **AND** it SHALL aggregate all results into a single response

#### Scenario: Handle SendMultipleSMS error response
- **WHEN** the API returns an error for SendMultipleSMS
- **THEN** resultData SHALL contain ReqStatus and Message with error details
- **AND** overall status SHALL be false

### Requirement: Send same message to multiple numbers
The gateway SHALL support sending identical SMS to multiple recipients.

#### Scenario: Send one SMS to multiple numbers
- **WHEN** calling `sendOneSMSToMultiNumber(['09123456789', '09129876543'], 'Hello')`
- **THEN** the gateway SHALL delegate to `sendSMS()` method
- **AND** it SHALL return the same response format

### Requirement: Add contact not supported
The gateway SHALL indicate that contact management is not supported by Mellipayamak integration.

#### Scenario: Attempt to add contact
- **WHEN** calling `addContact(['name' => 'Test', 'mobile' => '09123456789'])`
- **THEN** the gateway SHALL return status false
- **AND** resultCode SHALL be -1
- **AND** resultData SHALL contain "Add contact not implemented"

### Requirement: Use AbstractGateway infrastructure
The gateway SHALL utilize the AbstractGateway base class for common functionality.

#### Scenario: Inherit from AbstractGateway
- **WHEN** the Melipayamak class extends AbstractGateway
- **THEN** it SHALL use inherited methods for cURL execution
- **AND** it SHALL use inherited methods for phone number validation and normalization
- **AND** it SHALL override `parseResponse()` to handle Mellipayamak-specific response format

### Requirement: PSR-12 compliance
The gateway code SHALL follow PSR-12 coding standards and PHP 8.2+ features.

#### Scenario: Code follows standards
- **WHEN** examining the Melipayamak.php file
- **THEN** it SHALL include `declare(strict_types=1)`
- **AND** it SHALL use type hints for all parameters and return values
- **AND** it SHALL use PHP 8.2+ features like union types where appropriate
- **AND** it SHALL follow 4-space indentation and proper brace placement
