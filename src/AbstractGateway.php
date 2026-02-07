<?php
declare(strict_types=1);

namespace jamal13647850\smsapi;

/**
 * Abstract base class for SMS gateway implementations
 * 
 * This class provides common functionality shared across all SMS providers
 * including HTTP request handling, response parsing, and error handling.
 */
abstract class AbstractGateway implements Gateway
{
    protected const DEFAULT_TIMEOUT = 30;
    protected const DEFAULT_CONNECT_TIMEOUT = 10;

    /**
     * Execute HTTP request using cURL
     *
     * @param string $url The request URL
     * @param array $options cURL options to override defaults
     * @return array Response data with status, resultCode, and resultData
     */
    protected function executeRequest(string $url, array $options = []): array
    {
        $curl = curl_init($url);
        
        if ($curl === false) {
            return [
                'status' => false,
                'resultCode' => -1,
                'resultData' => 'Failed to initialize cURL'
            ];
        }

        $defaultOptions = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => self::DEFAULT_TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => self::DEFAULT_CONNECT_TIMEOUT,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_ENCODING => '',
        ];

        curl_setopt_array($curl, $defaultOptions + $options);

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
     * Parse API response
     *
     * @param string $response Raw response string
     * @return array Parsed response with status, resultCode, and resultData
     */
    abstract protected function parseResponse(string $response): array;

    /**
     * Validate Iranian mobile phone number
     *
     * @param string $number Phone number to validate
     * @return bool True if valid, false otherwise
     */
    protected function validatePhoneNumber(string $number): bool
    {
        // Supports formats: 09123456789, 9123456789, +989123456789
        return preg_match('/^(\+98|0)?9\d{9}$/', $number) === 1;
    }

    /**
     * Normalize phone number to standard format (09xxxxxxxx)
     *
     * @param string|array $numbers Single number or array of numbers
     * @return string|array Normalized number(s)
     */
    protected function normalizePhoneNumbers(string|array $numbers): string|array
    {
        $normalize = function (string $number): string {
            // Remove any non-digit characters
            $number = preg_replace('/\D/', '', $number);
            
            // Convert +98 or 98 prefix to 0
            if (str_starts_with($number, '98') && strlen($number) === 12) {
                $number = '0' . substr($number, 2);
            } elseif (str_starts_with($number, '9') && strlen($number) === 10) {
                $number = '0' . $number;
            }
            
            return $number;
        };

        if (is_array($numbers)) {
            return array_map($normalize, $numbers);
        }

        return $normalize($numbers);
    }

    /**
     * Format array of recipients for API
     *
     * @param string|array $to Single number or array of numbers
     * @return string JSON encoded recipients
     */
    protected function formatRecipients(string|array $to): string
    {
        $recipients = is_array($to) ? $to : [$to];
        return json_encode($this->normalizePhoneNumbers($recipients));
    }
}
