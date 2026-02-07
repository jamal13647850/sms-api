<?php
declare(strict_types=1);

namespace jamal13647850\smsapi;

/**
 * SMS service facade class
 * 
 * Provides a unified interface for sending SMS messages through
 * any configured gateway provider.
 */
class SMS
{
    protected Gateway $gateway;

    public function __construct(Gateway $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * Send SMS to single or multiple recipients
     *
     * @param string|array $to Phone number(s)
     * @param string $message Message content
     * @return array Response from gateway
     */
    public function sendSMS(string|array $to, string $message): array
    {
        return $this->gateway->sendSMS($to, $message);
    }

    /**
     * Send SMS using pattern/template
     *
     * @param string $to Recipient number
     * @param string $message Message or template content
     * @param int|string $bodyId Template ID
     * @param array $parameters Template variables
     * @return array Response from gateway
     */
    public function sendSMSByPattern(string $to, string $message, int|string $bodyId, array $parameters): array
    {
        return $this->gateway->sendSMSByPattern($to, $message, $bodyId, $parameters);
    }

    /**
     * Send same message to multiple numbers
     *
     * @param array $to Array of phone numbers
     * @param string $message Message content
     * @return array Response from gateway
     */
    public function sendOneSMSToMultiNumber(array $to, string $message): array
    {
        return $this->gateway->sendOneSMSToMultiNumber($to, $message);
    }

    /**
     * Send different messages to different numbers
     *
     * @param array $msNum Number-message pairs
     * @return array Response from gateway
     */
    public function sendMultiSMSToMultiNumber(array $msNum): array
    {
        return $this->gateway->sendMultiSMSToMultiNumber($msNum);
    }

    /**
     * Receive incoming messages
     *
     * @return array Received messages
     */
    public function receiveSMS(): array
    {
        return $this->gateway->receiveSMS();
    }

    /**
     * Get message delivery status
     *
     * @param mixed $messageId Message ID
     * @return string Status string
     */
    public function getSMSStatus($messageId): string
    {
        return $this->gateway->getSMSStatus($messageId);
    }

    /**
     * Get account credit balance
     *
     * @return int Credit amount
     */
    public function getCredit(): int
    {
        return $this->gateway->getCredit();
    }

    /**
     * Add contact to address book
     *
     * @param array $contactInfo Contact information
     * @return array Response from gateway
     */
    public function addContact(array $contactInfo): array
    {
        return $this->gateway->addContact($contactInfo);
    }
}
