<?php
declare(strict_types=1);

namespace jamal13647850\smsapi;

/**
 * SMS.ir Gateway Implementation
 * 
 * Provides integration with SMS.ir REST API v1.
 * 
 * @author Sayyed Jamal Ghasemi
 * @see https://api.sms.ir
 */
class SMSir extends AbstractGateway
{
    private const DEFAULT_URL = 'https://api.sms.ir/v1/send/';

    private string $baseUrl;
    private string $apiKey;
    private string $from;

    /**
     * Constructor
     *
     * @param string $apiKey API key for authentication
     * @param string $from Sender line number
     * @param string|null $url Optional API base URL override
     */
    public function __construct(
        string $apiKey,
        string $from,
        ?string $url = null
    ) {
        $this->baseUrl = $url ?? self::DEFAULT_URL;
        $this->apiKey = $apiKey;
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
        $url = $this->baseUrl . 'bulk';
        
        $params = [
            'lineNumber' => $this->from,
            'messageText' => $message,
            'mobiles' => $this->normalizePhoneNumbers($to),
        ];

        return $this->executeJsonRequest($url, $params);
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
        $url = $this->baseUrl . 'bulk';
        
        $messages = [];
        foreach ($msNum as $number => $message) {
            $messages[] = [
                'mobile' => $this->normalizePhoneNumbers($number),
                'messageText' => $message,
            ];
        }

        $params = [
            'lineNumber' => $this->from,
            'messages' => $messages,
        ];

        return $this->executeJsonRequest($url, $params);
    }

    /**
     * Send SMS using predefined pattern/template
     *
     * @param string $to Recipient phone number
     * @param string $message Optional message (not used with patterns)
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
        $url = $this->baseUrl . 'verify';
        
        // Convert parameters array to SMS.ir format
        $paramsList = [];
        foreach ($parameters as $key => $value) {
            $paramsList[] = [
                'name' => $key,
                'value' => $value,
            ];
        }

        $params = [
            'mobile' => $this->normalizePhoneNumbers($to),
            'templateId' => $bodyId,
            'parameters' => $paramsList,
        ];

        return $this->executeJsonRequest($url, $params);
    }

    /**
     * Receive incoming SMS messages
     *
     * @return array Received messages
     */
    public function receiveSMS(): array
    {
        $url = $this->baseUrl . 'receive';
        
        return $this->executeGetRequest($url);
    }

    /**
     * Get message delivery status
     *
     * @param mixed $messageId Message ID
     * @return string Status string
     */
    public function getSMSStatus($messageId): string
    {
        $url = $this->baseUrl . 'status?id=' . urlencode((string)$messageId);
        
        $response = $this->executeGetRequest($url);
        
        if ($response['status'] && isset($response['resultData']['status'])) {
            return $response['resultData']['status'];
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
        $url = str_replace('/send/', '/credit/', $this->baseUrl);
        
        $response = $this->executeGetRequest($url);
        
        if ($response['status'] && isset($response['resultData'])) {
            return (int) $response['resultData'];
        }

        return 0;
    }

    /**
     * Add contact to address book
     *
     * @param array $contactInfo Contact information
     * @return array Response with status, resultCode, and resultData
     */
    public function addContact(array $contactInfo): array
    {
        // SMS.ir contact management requires separate API
        return [
            'status' => false,
            'resultCode' => -1,
            'resultData' => 'Contact management not implemented. Use SMS.ir dashboard.'
        ];
    }

    /**
     * Execute GET request
     *
     * @param string $url Request URL
     * @return array Response data
     */
    private function executeGetRequest(string $url): array
    {
        return $this->executeRequest($url, [
            CURLOPT_HTTPGET => true,
            CURLOPT_HTTPHEADER => [
                'X-API-KEY: ' . $this->apiKey,
                'Accept: application/json'
            ],
        ]);
    }

    /**
     * Execute JSON POST request
     *
     * @param string $url Request URL
     * @param array $params Request parameters
     * @return array Response data
     */
    private function executeJsonRequest(string $url, array $params): array
    {
        $jsonData = json_encode($params, JSON_THROW_ON_ERROR);

        return $this->executeRequest($url, [
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => [
                'X-API-KEY: ' . $this->apiKey,
                'Content-Type: application/json',
                'Accept: application/json'
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

        // SMS.ir returns status in 'status' field
        if (isset($data['status']) && $data['status'] === 1) {
            return [
                'status' => true,
                'resultCode' => 1,
                'resultData' => $data['data'] ?? $data['message'] ?? 'Success'
            ];
        }

        // Error response
        return [
            'status' => false,
            'resultCode' => $data['status'] ?? -1,
            'resultData' => $data['message'] ?? $data['error'] ?? 'Unknown error'
        ];
    }
}
