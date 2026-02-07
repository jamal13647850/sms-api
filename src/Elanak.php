<?php
declare(strict_types=1);

namespace jamal13647850\smsapi;

/**
 * Elanak Gateway Implementation
 * 
 * Provides integration with Elanak SOAP-based SMS service.
 * 
 * @author Sayyed Jamal Ghasemi
 */
class Elanak implements Gateway
{
    private const DEFAULT_SOAP_URL = 'http://158.58.186.243/webservice/';

    private \SoapClient $sendSoap;
    private \SoapClient $numberSoap;
    private \SoapClient $timeSoap;
    private string $messageType;
    private string $username;
    private string $password;
    private string $fromNum;

    /**
     * Constructor
     *
     * @param string $username API username
     * @param string $password API password
     * @param string $fromNum Sender number
     * @param string|null $soapUrl Optional SOAP service URL
     */
    public function __construct(
        string $username,
        string $password,
        string $fromNum,
        ?string $soapUrl = null
    ) {
        $baseUrl = $soapUrl ?? self::DEFAULT_SOAP_URL;
        
        $this->sendSoap = new \SoapClient($baseUrl . 'send.php?wsdl');
        $this->numberSoap = new \SoapClient($baseUrl . 'number.php?wsdl');
        $this->timeSoap = new \SoapClient($baseUrl . 'time.php?wsdl');
        
        $this->messageType = '0';
        $this->username = $username;
        $this->password = $password;
        $this->fromNum = $fromNum;
    }

    /**
     * Set message type
     *
     * @param string $messageType Message type code
     * @return void
     */
    public function setMessageType(string $messageType): void
    {
        $this->messageType = $messageType;
    }

    /**
     * Set username
     *
     * @param string $username API username
     * @return void
     */
    public function setUserName(string $username): void
    {
        $this->username = $username;
    }

    /**
     * Set password
     *
     * @param string $password API password
     * @return void
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * Set from number
     *
     * @param string $fromNum Sender number
     * @return void
     */
    public function setFromNum(string $fromNum): void
    {
        $this->fromNum = $fromNum;
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
        try {
            $recipients = is_array($to) ? $to : [$to];
            
            $result = $this->sendSoap->SendSMS(
                $this->fromNum,
                $recipients,
                $message,
                $this->messageType,
                $this->username,
                $this->password
            );

            return [
                'status' => true,
                'resultCode' => 0,
                'resultData' => $result
            ];
        } catch (\SoapFault $e) {
            return [
                'status' => false,
                'resultCode' => -1,
                'resultData' => 'SOAP Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send SMS using predefined pattern/template
     *
     * @param string $to Recipient phone number
     * @param string $message Message content
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
        // Elanak doesn't support pattern-based SMS
        return [
            'status' => false,
            'resultCode' => -1,
            'resultData' => 'Pattern-based SMS not supported by Elanak'
        ];
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
        try {
            $result = $this->sendSoap->GetInbox(
                $this->username,
                $this->password,
                '', // from number filter
                '', // to number filter
                0,  // start date
                0,  // end date
                100 // limit
            );

            return [
                'status' => true,
                'resultCode' => 0,
                'resultData' => $result
            ];
        } catch (\SoapFault $e) {
            return [
                'status' => false,
                'resultCode' => -1,
                'resultData' => 'SOAP Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get message delivery status
     *
     * @param mixed $messageId Message ID
     * @return string Status string
     */
    public function getSMSStatus($messageId): string
    {
        try {
            $result = $this->sendSoap->GetStatus(
                $this->username,
                $this->password,
                (int)$messageId
            );

            $statusMap = [
                0 => 'pending',
                1 => 'sent',
                2 => 'delivered',
                3 => 'failed',
            ];

            return $statusMap[$result] ?? 'unknown';
        } catch (\SoapFault $e) {
            return 'unknown';
        }
    }

    /**
     * Get account credit/balance
     *
     * @return int Remaining credit amount
     */
    public function getCredit(): int
    {
        try {
            $result = $this->sendSoap->GetCredit(
                $this->username,
                $this->password
            );

            return (int) $result;
        } catch (\SoapFault $e) {
            return 0;
        }
    }

    /**
     * Add contact to address book
     *
     * @param array $contactInfo Contact information
     * @return array Response with status, resultCode, and resultData
     */
    public function addContact(array $contactInfo): array
    {
        try {
            $result = $this->numberSoap->AddNumber(
                $this->username,
                $this->password,
                $contactInfo['phone'] ?? '',
                $contactInfo['name'] ?? '',
                $contactInfo['groupId'] ?? 0
            );

            return [
                'status' => true,
                'resultCode' => 0,
                'resultData' => $result
            ];
        } catch (\SoapFault $e) {
            return [
                'status' => false,
                'resultCode' => -1,
                'resultData' => 'SOAP Error: ' . $e->getMessage()
            ];
        }
    }
}
