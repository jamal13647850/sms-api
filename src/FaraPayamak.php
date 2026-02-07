<?php
declare(strict_types=1);

namespace jamal13647850\smsapi;

/**
 * FaraPayamak Gateway Implementation
 * 
 * Provides integration with FaraPayamak REST API.
 * 
 * @author Sayyed Jamal Ghasemi
 * @see https://rest.payamak-panel.com
 */
class FaraPayamak extends AbstractGateway
{
    private const DEFAULT_URL = 'https://rest.payamak-panel.com/api/SendSMS/';

    private string $baseUrl;
    private string $username;
    private string $password;
    private string $from;

    /**
     * Constructor
     *
     * @param string $username API username
     * @param string $password API password
     * @param string $from Sender number
     * @param string|null $url Optional API URL override
     */
    public function __construct(
        string $username,
        string $password,
        string $from,
        ?string $url = null
    ) {
        $this->baseUrl = $url ?? self::DEFAULT_URL;
        $this->username = $username;
        $this->password = $password;
        $this->from = $from;
    }

    /**
     * Send SMS to single or multiple recipients
     *
     * @param string|array $to Single phone number or array of numbers
     * @param string $message Message content
     * @return array Response with status, resultCode, and resultData
     */
    public function sendSMS(string|array $to, string $message): array
    {
        $url = $this->baseUrl . 'SendSMS';
        
        $normalizedTo = $this->normalizePhoneNumbers($to);
        $toString = is_array($normalizedTo) ? implode(',', $normalizedTo) : $normalizedTo;

        $postFields = http_build_query([
            'username' => $this->username,
            'password' => $this->password,
            'to' => $toString,
            'from' => $this->from,
            'text' => $message,
            'isFlash' => false,
        ]);

        return $this->executeFormRequest($url, $postFields);
    }

    /**
     * Send SMS using predefined pattern/template
     *
     * @param string $to Recipient phone number
     * @param string $message Message content (used as template text)
     * @param int|string $bodyId Template ID
     * @param array $parameters Variables to replace in pattern
     * @return array Response with status, resultCode, and resultData
     */
    public function sendSMSByPattern(
        string $to,
        string $message,
        int|string $bodyId,
        array $parameters
    ): array {
        $url = $this->baseUrl . 'BaseServiceNumber';
        
        $postFields = http_build_query([
            'username' => $this->username,
            'password' => $this->password,
            'to' => $this->normalizePhoneNumbers($to),
            'bodyId' => $bodyId,
            'text' => $message,
        ]);

        return $this->executeFormRequest($url, $postFields);
    }

    /**
     * Send same SMS to multiple numbers
     *
     * @param array $to Array of phone numbers
     * @param string $message Message content
     * @return array Response with status, resultCode, and resultData
     */
    public function sendOneSMSToMultiNumber(array $to, string $message): array
    {
        return $this->sendSMS($to, $message);
    }

    /**
     * Send different SMS to different numbers
     *
     * @param array $msNum Array of [number => message] pairs
     * @return array Response with status, resultCode, and resultData
     */
    public function sendMultiSMSToMultiNumber(array $msNum): array
    {
        // FaraPayamak doesn't support bulk different messages
        // Send individually and aggregate results
        $results = [];
        $allSuccess = true;

        foreach ($msNum as $number => $message) {
            $result = $this->sendSMS($number, $message);
            $results[$number] = $result;
            if (!$result['status']) {
                $allSuccess = false;
            }
        }

        return [
            'status' => $allSuccess,
            'resultCode' => $allSuccess ? 0 : -1,
            'resultData' => $results
        ];
    }

    /**
     * Receive incoming SMS messages
     *
     * @return array Received messages
     */
    public function receiveSMS(): array
    {
        $url = $this->baseUrl . 'GetReceiveMessages';
        
        $postFields = http_build_query([
            'username' => $this->username,
            'password' => $this->password,
            'location' => 1, // Inbox
            'from' => '',
            'to' => '',
            'text' => '',
        ]);

        return $this->executeFormRequest($url, $postFields);
    }

    /**
     * Get message delivery status
     *
     * @param mixed $messageId Message ID
     * @return string Status string
     */
    public function getSMSStatus($messageId): string
    {
        $url = $this->baseUrl . 'GetDeliveries2';
        
        $postFields = http_build_query([
            'username' => $this->username,
            'password' => $this->password,
            'recId' => (string)$messageId,
        ]);

        $response = $this->executeFormRequest($url, $postFields);
        
        if ($response['status'] && isset($response['resultData'])) {
            // FaraPayamak returns delivery status codes
            $statusCode = $response['resultData'];
            $statusMap = [
                1 => 'sent',
                2 => 'delivered',
                3 => 'failed',
                4 => 'pending',
                5 => 'unknown',
            ];
            return $statusMap[$statusCode] ?? 'unknown';
        }

        return 'unknown';
    }

    /**
     * Get account credit/balance
     *
     * @return int Remaining credit amount
     */
    public function getCredit(): int
    {
        $url = $this->baseUrl . 'GetCredit';
        
        $postFields = http_build_query([
            'username' => $this->username,
            'password' => $this->password,
        ]);

        $response = $this->executeFormRequest($url, $postFields);
        
        // FaraPayamak returns credit in Value field when successful
        if ($response['status'] && isset($response['resultData']['Value'])) {
            return (int) $response['resultData']['Value'];
        }

        return 0;
    }

    /**
     * Add contact to address book
     *
     * @param array $contactInfo Contact information with keys: phone, name, etc.
     * @return array Response with status, resultCode, and resultData
     */
    public function addContact(array $contactInfo): array
    {
        $url = $this->baseUrl . 'AddContact';
        
        $postFields = http_build_query([
            'username' => $this->username,
            'password' => $this->password,
            'mobile' => $contactInfo['phone'] ?? '',
            'name' => $contactInfo['name'] ?? '',
            'groupId' => $contactInfo['groupId'] ?? 0,
        ]);

        return $this->executeFormRequest($url, $postFields);
    }

    /**
     * Execute form-encoded POST request
     *
     * @param string $url Request URL
     * @param string $postFields Form-encoded parameters
     * @return array Response data
     */
    private function executeFormRequest(string $url, string $postFields): array
    {
        return $this->executeRequest($url, [
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded'
            ],
        ]);
    }

    /**
     * Parse API response
     *
     * @param string $response Raw response string
     * @return array Parsed response with status, resultCode, and resultData
     */
    protected function parseResponse(string $response): array
    {
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'status' => false,
                'resultCode' => -3,
                'resultData' => 'Invalid JSON response: ' . json_last_error_msg()
            ];
        }

        // FaraPayamak uses RetStatus field: 1 = success, other = error
        if (isset($data['RetStatus']) && $data['RetStatus'] === 1) {
            return [
                'status' => true,
                'resultCode' => 1,
                'resultData' => $data['Value'] ?? $data['StrRetStatus'] ?? 'Success'
            ];
        }

        // Error response
        return [
            'status' => false,
            'resultCode' => $data['RetStatus'] ?? -1,
            'resultData' => $data['StrRetStatus'] ?? $data['Value'] ?? 'Unknown error'
        ];
    }
}
