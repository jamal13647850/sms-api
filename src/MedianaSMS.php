<?php
declare(strict_types=1);

namespace jamal13647850\smsapi;

/**
 * MedianaSMS Gateway Implementation
 * 
 * Provides integration with MedianaSMS (ippanel.com) service.
 * Similar to FarazSMS but with Mediana-specific configurations.
 * 
 * @author Sayyed Jamal Ghasemi
 */
class MedianaSMS extends AbstractGateway
{
    private const DEFAULT_URL = 'https://ippanel.com/services.jspd';

    private string $url;
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
        $this->url = $url ?? self::DEFAULT_URL;
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
        if (empty($message)) {
            return [
                'status' => false,
                'resultCode' => 1,
                'resultData' => 'متن پیام خالی می باشد.'
            ];
        }

        $params = [
            'uname' => $this->username,
            'pass' => $this->password,
            'from' => $this->from,
            'message' => $message,
            'to' => $this->formatRecipients($to),
            'op' => 'send'
        ];

        return $this->executePostRequest($params);
    }

    /**
     * Send SMS using predefined pattern/template
     *
     * @param string $to Recipient phone number
     * @param string $message Optional message
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
        // MedianaSMS uses the same pattern API as FarazSMS
        $queryParams = [
            'username' => $this->username,
            'password' => $this->password,
            'from' => $this->from,
            'to' => json_encode([$this->normalizePhoneNumbers($to)]),
            'input_data' => json_encode($parameters),
            'pattern_code' => $bodyId,
        ];

        $url = 'https://ippanel.com/patterns/pattern?' . http_build_query($queryParams);

        return $this->executeRequest($url, [
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $parameters,
        ]);
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
        // MedianaSMS doesn't provide receive SMS via REST API
        return [
            'status' => false,
            'resultCode' => -1,
            'resultData' => 'Receive SMS not supported by MedianaSMS REST API'
        ];
    }

    /**
     * Get message delivery status
     *
     * @param mixed $messageId Message ID
     * @return string Status string
     */
    public function getSMSStatus($messageId): string
    {
        // MedianaSMS doesn't provide message status via REST API
        return 'unknown';
    }

    /**
     * Get account credit/balance
     *
     * @return int Remaining credit amount
     */
    public function getCredit(): int
    {
        $params = [
            'uname' => $this->username,
            'pass' => $this->password,
            'op' => 'credit'
        ];

        $response = $this->executePostRequest($params);

        if ($response['status'] && is_numeric($response['resultData'])) {
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
        // Contact management not implemented for MedianaSMS
        return [
            'status' => false,
            'resultCode' => -1,
            'resultData' => 'Add contact not implemented'
        ];
    }

    /**
     * Execute POST request with form parameters
     *
     * @param array $params Form parameters
     * @return array Parsed response
     */
    private function executePostRequest(array $params): array
    {
        return $this->executeRequest($this->url, [
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $params,
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

        if (!is_array($data) || count($data) < 2) {
            return [
                'status' => false,
                'resultCode' => -4,
                'resultData' => 'Invalid response format'
            ];
        }

        [$code, $result] = $data;
        $code = (int) $code;

        if ($code === 0) {
            return [
                'status' => true,
                'resultCode' => 0,
                'resultData' => $result
            ];
        }

        return [
            'status' => false,
            'resultCode' => $code,
            'resultData' => "Error code: {$code}"
        ];
    }
}
