<?php
declare(strict_types=1);

namespace jamal13647850\smsapi;

/**
 * Melipayamak Gateway Implementation
 * 
 * Provides integration with Melipayamak (melipayamak.ir) SMS service.
 * 
 * IMPORTANT: This gateway uses ApiKey authentication. The `password` parameter
 * in the constructor expects the ApiKey from your Melipayamak panel settings
 * (found under the "توسعه‌دهندگان" menu), not your account password.
 * 
 * @author Sayyed Jamal Ghasemi
 * @see https://melipayamak.ir
 */
class Melipayamak extends AbstractGateway
{
    /**
     * API Base URL for Melipayamak REST API
     */
    private const DEFAULT_URL = 'https://rest.payamak-panel.com/api/SendSMS/';

    /**
     * API Endpoint constants
     */
    private const ENDPOINT_SEND_SMS = 'SendSMS';
    private const ENDPOINT_SEND_MULTIPLE_SMS = 'SendMultipleSMS';
    private const ENDPOINT_BASE_SERVICE_NUMBER = 'BaseServiceNumber';
    private const ENDPOINT_GET_DELIVERIES_2 = 'GetDeliveries2';
    private const ENDPOINT_GET_MESSAGES = 'GetMessages';
    private const ENDPOINT_GET_CREDIT = 'GetCredit';

    /**
     * Instance properties for authentication and configuration
     */
    private string $url;
    private string $username;
    private string $apiKey; // This is the ApiKey from panel settings, not password
    private string $from;

    /**
     * Error messages mapping for SendSMS result codes
     * Based on Melipayamak REST API documentation
     */
    private array $errorMessages = [
        0 => 'نام کاربری یا رمز عبور اشتباه می باشد',
        1 => 'درخواست با موفقیت انجام شد',
        2 => 'اعتبار کافی نمی باشد',
        3 => 'محدودیت در ارسال روزانه',
        4 => 'محدودیت در حجم ارسال',
        5 => 'شماره فرستنده معتبر نمی باشد',
        6 => 'سامانه در حال بروزرسانی می باشد',
        7 => 'متن حاوی کلمه فیلتر شده می باشد',
        9 => 'ارسال از خطوط عمومی از طریق وب سرویس امکان پذیر نمی باشد',
        10 => 'کاربر مورد نظر فعال نمی باشد',
        11 => 'ارسال نشده',
        12 => 'مدارک کاربر کامل نمی باشد',
        14 => 'متن حاوی لینک می باشد',
        15 => 'عدم وجود لغو 11 در انتهای متن پیامک',
        16 => 'شماره گیرنده ای یافت نشد',
        17 => 'متن پیامک خالی می باشد',
        18 => 'شماره موبایل معتبر نمی باشد',
    ];

    /**
     * IP-related security error codes
     */
    private array $securityErrorMessages = [
        108 => 'مسدود شدن IP به دلیل تلاش ناموفق استفاده از API',
        109 => 'الزام تنظیم IP مجاز برای استفاده از API',
        110 => 'الزام استفاده از ApiKey به جای رمز عبور',
        111 => 'درخواست کننده نامعتبر است',
    ];

    /**
     * Delivery status code mapping
     * Based on GetDeliveries2 endpoint response
     */
    private array $deliveryStatusMessages = [
        0 => 'ارسال شده به مخابرات',
        1 => 'رسیده به گوشی',
        2 => 'نرسیده به گوشی',
        3 => 'خطای مخابراتی',
        5 => 'خطای نامشخص',
        8 => 'رسیده به مخابرات',
        16 => 'نرسیده به مخابرات',
        35 => 'لیست سیاه',
        100 => 'نامشخص',
        200 => 'ارسال شده',
        300 => 'فیلتر شده',
        400 => 'در لیست ارسال',
        500 => 'عدم پذیرش',
    ];

    /**
     * Constructor
     *
     * IMPORTANT: The `password` parameter expects the ApiKey from your
     * Melipayamak panel settings (found under "توسعه‌دهندگان" menu),
     * NOT your account password. Melipayamak REST API uses ApiKey
     * authentication instead of password-based authentication.
     *
     * @param string $username API username (panel login username)
     * @param string $password ApiKey from panel Developers menu (NOT account password)
     * @param string $from Sender number (panel line number)
     * @param string|null $url Optional API URL override
     */
    public function __construct(
        string $username,
        string $password,
        string $from,
        ?string $url = null
    ) {
        $this->url = $url ?? self::DEFAULT_URL;
        $this->username = $username;
        $this->apiKey = $password; // ApiKey stored in apiKey property for clarity
        $this->from = $from;
    }

    /**
     * Send SMS to single or multiple recipients
     *
     * Supports up to 100 recipients. For more recipients, use sendOneSMSToMultiNumber
     * or split into multiple calls.
     *
     * @param string|array $to Single phone number or array of numbers (max 100)
     * @param string $message Message content
     * @return array Response with status, resultCode, and resultData
     */
    public function sendSMS(string|array $to, string $message): array
    {
        if (empty($message)) {
            return [
                'status' => false,
                'resultCode' => 17,
                'resultData' => $this->errorMessages[17]
            ];
        }

        // Normalize and validate recipients
        $recipients = is_array($to) ? $to : [$to];
        $normalizedRecipients = $this->normalizePhoneNumbers($recipients);

        if (empty($normalizedRecipients)) {
            return [
                'status' => false,
                'resultCode' => 16,
                'resultData' => $this->errorMessages[16]
            ];
        }

        // Check recipient limit (100 max for SendSMS)
        if (count($normalizedRecipients) > 100) {
            return [
                'status' => false,
                'resultCode' => -1,
                'resultData' => 'Maximum 100 recipients allowed per request. Use sendOneSMSToMultiNumber for larger batches.'
            ];
        }

        $params = [
            'username' => $this->username,
            'password' => $this->apiKey,
            'from' => $this->from,
            'to' => implode(',', $normalizedRecipients),
            'text' => $message,
            'isFlash' => false,
        ];

        $endpoint = $this->url . self::ENDPOINT_SEND_SMS;
        return $this->executePostRequest($endpoint, $params);
    }

    /**
     * Send SMS using predefined pattern/template
     *
     * Uses the BaseServiceNumber endpoint for pattern-based SMS.
     * The bodyId parameter corresponds to the pattern/template ID.
     *
     * @param string $to Recipient phone number
     * @param string $message Optional message content (used as text parameter)
     * @param int|string $bodyId Pattern/template ID
     * @param array $parameters Variables to replace in pattern
     * @return array Response with status, resultCode, and resultData
     */
    public function sendSMSByPattern(
        string $to,
        string $message,
        int|string $bodyId,
        array $parameters
    ): array {
        // Normalize the recipient number
        $normalizedTo = $this->normalizePhoneNumbers($to);

        $params = [
            'username' => $this->username,
            'password' => $this->apiKey,
            'to' => $normalizedTo,
            'bodyId' => $bodyId,
            'text' => $this->encodePatternParameters($parameters),
        ];

        $endpoint = $this->url . self::ENDPOINT_BASE_SERVICE_NUMBER;
        return $this->executePostRequest($endpoint, $params);
    }

    /**
     * Send same SMS to multiple numbers
     *
     * Delegates to sendSMS() method. Supports up to 100 recipients.
     * For larger batches, the method will split into multiple API calls.
     *
     * @param array $to Array of phone numbers
     * @param string $message Message content
     * @return array Response with status, resultCode, and resultData
     */
    public function sendOneSMSToMultiNumber(array $to, string $message): array
    {
        // Split large recipient lists into chunks of 100
        $chunks = array_chunk($to, 100);
        $results = [];
        $allSuccess = true;
        $combinedResultData = [];

        foreach ($chunks as $chunk) {
            $result = $this->sendSMS($chunk, $message);
            $results[] = $result;

            if (!$result['status']) {
                $allSuccess = false;
            }

            // Collect all result data
            if (isset($result['resultData'])) {
                $combinedResultData[] = $result['resultData'];
            }
        }

        // Return aggregated result
        return [
            'status' => $allSuccess,
            'resultCode' => $allSuccess ? 1 : -1,
            'resultData' => $combinedResultData,
        ];
    }

    /**
     * Send different SMS to different numbers
     *
     * Uses SendMultipleSMS endpoint which accepts JSON array of recipients
     * and messages. Maximum 100 recipient-message pairs per request.
     * 
     * Format for $msNum: ['0912XXXXXXX' => 'message 1', '0935XXXXXXX' => 'message 2']
     *
     * @param array $msNum Array of [number => message] pairs
     * @return array Response with status, resultCode, and resultData
     */
    public function sendMultiSMSToMultiNumber(array $msNum): array
    {
        if (empty($msNum)) {
            return [
                'status' => false,
                'resultCode' => 16,
                'resultData' => $this->errorMessages[16]
            ];
        }

        // Check limit (max 100 pairs)
        if (count($msNum) > 100) {
            // Split into chunks
            $chunks = array_chunk($msNum, 100, true);
            $results = [];
            $allSuccess = true;

            foreach ($chunks as $chunk) {
                $result = $this->sendMultiSMSToMultiNumberChunk($chunk);
                $results[] = $result;
                if (!$result['status']) {
                    $allSuccess = false;
                }
            }

            return [
                'status' => $allSuccess,
                'resultCode' => $allSuccess ? 1 : -1,
                'resultData' => $results
            ];
        }

        return $this->sendMultiSMSToMultiNumberChunk($msNum);
    }

    /**
     * Send a chunk of different messages to different numbers (max 100 pairs)
     *
     * @param array $msNum Array of [number => message] pairs (max 100)
     * @return array Response with status, resultCode, and resultData
     */
    private function sendMultiSMSToMultiNumberChunk(array $msNum): array
    {
        $toArray = [];
        $textArray = [];

        foreach ($msNum as $number => $message) {
            $normalizedNumber = $this->normalizePhoneNumbers($number);
            $toArray[] = $normalizedNumber;
            $textArray[] = $message;
        }

        $payload = [
            'username' => $this->username,
            'password' => $this->apiKey,
            'from' => $this->from,
            'to' => $toArray,
            'text' => $textArray,
            'isFlash' => false,
        ];

        $endpoint = $this->url . self::ENDPOINT_SEND_MULTIPLE_SMS;

        // Send as JSON with proper Content-Type header
        $curl = curl_init($endpoint);

        if ($curl === false) {
            return [
                'status' => false,
                'resultCode' => -1,
                'resultData' => 'Failed to initialize cURL'
            ];
        }

        $jsonPayload = json_encode($payload, JSON_THROW_ON_ERROR);

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jsonPayload,
            CURLOPT_TIMEOUT => self::DEFAULT_TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => self::DEFAULT_CONNECT_TIMEOUT,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($response === false) {
            return [
                'status' => false,
                'resultCode' => -2,
                'resultData' => "cURL Error: {$error}"
            ];
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            return [
                'status' => false,
                'resultCode' => $httpCode,
                'resultData' => "HTTP Error: {$httpCode}"
            ];
        }

        return $this->parseResponse($response);
    }

    /**
     * Receive incoming SMS messages
     *
     * Uses the GetMessages endpoint to retrieve messages.
     * Location: 1 = incoming messages, 2 = outgoing messages
     *
     * @return array Response with status, resultCode, and resultData
     */
    public function receiveSMS(): array
    {
        $params = [
            'username' => $this->username,
            'password' => $this->apiKey,
            'location' => 1, // 1 = incoming messages
            'from' => '', // Optional: filter by sender
            'index' => 0, // Starting index
            'count' => 100, // Max records to retrieve
        ];

        $endpoint = $this->url . self::ENDPOINT_GET_MESSAGES;
        return $this->executePostRequest($endpoint, $params);
    }

    /**
     * Get message delivery status
     *
     * Uses the GetDeliveries2 endpoint to check delivery status.
     * Returns status code which maps to deliveryStatusMessages array.
     *
     * @param mixed $messageId Message ID (recId from sendSMS response)
     * @return string Status string describing delivery state
     */
    public function getSMSStatus($messageId): string
    {
        $params = [
            'username' => $this->username,
            'password' => $this->apiKey,
            'recId' => $messageId,
        ];

        $endpoint = $this->url . self::ENDPOINT_GET_DELIVERIES_2;
        $response = $this->executePostRequest($endpoint, $params);

        if ($response['status'] && isset($response['resultData'])) {
            $statusCode = (int) $response['resultData'];
            return $this->deliveryStatusMessages[$statusCode] ?? "نامشخص (کد: {$statusCode})";
        }

        return 'unknown';
    }

    /**
     * Get account credit/balance
     *
     * Uses the GetCredit endpoint to retrieve remaining credit amount.
     *
     * @return int Remaining credit amount (returns 0 on error)
     */
    public function getCredit(): int
    {
        $params = [
            'username' => $this->username,
            'password' => $this->apiKey,
        ];

        $endpoint = $this->url . self::ENDPOINT_GET_CREDIT;
        $response = $this->executePostRequest($endpoint, $params);

        if ($response['status'] && is_numeric($response['resultData'])) {
            return (int) $response['resultData'];
        }

        return 0;
    }

    /**
     * Add contact to address book
     *
     * Note: Melipayamak API supports contact management but requires
     * additional endpoints. This method returns not-supported status.
     *
     * @param array $contactInfo Contact information
     * @return array Response with status, resultCode, and resultData
     */
    public function addContact(array $contactInfo): array
    {
        return [
            'status' => false,
            'resultCode' => -1,
            'resultData' => 'Add contact not implemented for Melipayamak gateway'
        ];
    }

    /**
     * Execute POST request with form parameters
     *
     * @param string $endpoint API endpoint URL
     * @param array $params Form parameters
     * @return array Parsed response
     */
    private function executePostRequest(string $endpoint, array $params): array
    {
        $response = $this->executeRequest($endpoint, [
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($params),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
            ],
        ]);

        return $response;
    }

    /**
     * Parse API response
     *
     * Melipayamak returns JSON with consistent structure:
     * {Value: "data", RetStatus: 1|0, StrRetStatus: "Ok|Error"}
     *
     * RetStatus = 1 indicates success, RetStatus = 0 indicates failure
     *
     * @param string $response Raw response string
     * @return array Parsed response with status, resultCode, and resultData
     */
    protected function parseResponse(string $response): array
    {
        // Handle empty/null responses
        if (empty($response)) {
            return [
                'status' => false,
                'resultCode' => -5,
                'resultData' => 'Empty response from API'
            ];
        }

        try {
            $data = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return [
                'status' => false,
                'resultCode' => -3,
                'resultData' => 'Invalid JSON response: ' . $e->getMessage()
            ];
        }

        // Validate response structure
        if (!is_array($data) || !isset($data['RetStatus'])) {
            return [
                'status' => false,
                'resultCode' => -4,
                'resultData' => 'Invalid response format from API'
            ];
        }

        $retStatus = (int) $data['RetStatus'];
        $value = $data['Value'] ?? null;
        $strRetStatus = $data['StrRetStatus'] ?? '';

        // Success case
        if ($retStatus === 1) {
            return [
                'status' => true,
                'resultCode' => 1,
                'resultData' => $value
            ];
        }

        // Error case - determine error code and message
        $errorCode = is_numeric($value) ? (int) $value : -1;
        $errorMessage = $this->getErrorMessage($errorCode, $strRetStatus);

        return [
            'status' => false,
            'resultCode' => $errorCode,
            'resultData' => $errorMessage
        ];
    }

    /**
     * Get error message for a given error code
     *
     * Searches through error message arrays and returns appropriate message.
     * Falls back to StrRetStatus from API or generic message.
     *
     * @param int $errorCode Error code from API
     * @param string $strRetStatus Status string from API response
     * @return string Error message
     */
    private function getErrorMessage(int $errorCode, string $strRetStatus): string
    {
        // Check SendSMS error messages
        if (isset($this->errorMessages[$errorCode])) {
            return $this->errorMessages[$errorCode];
        }

        // Check security error messages (108-111)
        if (isset($this->securityErrorMessages[$errorCode])) {
            return $this->securityErrorMessages[$errorCode];
        }

        // Use API-provided status string if available
        if (!empty($strRetStatus) && $strRetStatus !== 'Error') {
            return $strRetStatus;
        }

        // Generic fallback
        return "خطای ناشناخته (کد: {$errorCode})";
    }

    /**
     * Encode pattern parameters for BaseServiceNumber endpoint
     *
     * Converts parameters array to a semicolon-separated string format.
     * Example: ['name' => 'John', 'code' => '1234'] => "John;1234"
     *
     * @param array $parameters Associative array of parameter values
     * @return string Semicolon-separated parameter values
     */
    private function encodePatternParameters(array $parameters): string
    {
        return implode(';', array_values($parameters));
    }
}
