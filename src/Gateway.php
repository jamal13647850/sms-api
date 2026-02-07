<?php
declare(strict_types=1);

namespace jamal13647850\smsapi;

/**
 * Gateway interface for SMS providers
 * 
 * All SMS gateway implementations must implement this interface
 * to ensure consistent API across different providers.
 */
interface Gateway
{
    /**
     * Send SMS to single or multiple recipients
     *
     * @param string|array $to Single phone number or array of numbers
     * @param string $message Message content
     * @return array Response with status, resultCode, and resultData
     */
    public function sendSMS(string|array $to, string $message): array;

    /**
     * Send SMS using predefined pattern/template
     *
     * @param string $to Recipient phone number
     * @param string $message Optional message content
     * @param int|string $bodyId Pattern/template ID
     * @param array $parameters Variables to replace in pattern
     * @return array Response with status, resultCode, and resultData
     */
    public function sendSMSByPattern(string $to, string $message, int|string $bodyId, array $parameters): array;

    /**
     * Send same SMS to multiple numbers
     *
     * @param array $to Array of phone numbers
     * @param string $message Message content
     * @return array Response with status, resultCode, and resultData
     */
    public function sendOneSMSToMultiNumber(array $to, string $message): array;

    /**
     * Send different SMS to different numbers
     *
     * @param array $msNum Array of [number => message] pairs
     * @return array Response with status, resultCode, and resultData
     */
    public function sendMultiSMSToMultiNumber(array $msNum): array;

    /**
     * Receive incoming SMS messages
     *
     * @return array Array of received messages
     */
    public function receiveSMS(): array;

    /**
     * Get status of sent message
     *
     * @param mixed $messageId Message ID to check
     * @return string Status string
     */
    public function getSMSStatus($messageId): string;

    /**
     * Get account credit/balance
     *
     * @return int Remaining credit amount
     */
    public function getCredit(): int;

    /**
     * Add contact to address book
     *
     * @param array $contactInfo Contact information
     * @return array Response with status, resultCode, and resultData
     */
    public function addContact(array $contactInfo): array;
}
