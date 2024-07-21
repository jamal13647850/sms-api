<?php

declare(strict_types=1);

namespace jamal13647850\smsapi;

defined('ABSPATH') || exit();


class SMSir implements Gateway
{
    private string $url;
    private string $apikey;
    private string $from;

    public function __construct(string $apikey, string $from, string $url = 'https://api.sms.ir/v1/send/')
    {
        $this->url = $url;
        $this->apikey = $apikey;

        $this->from = $from;
    }




    /**
     * sendSMS
     *
     * @param  mixed $to
     * @param  mixed $message
     * @return array
     */
    public function sendSMS(string|array $to, string $message, $sendDateTime = null): array
    {
        $this->url .= 'bulk';

        if ($sendDateTime === null) {
            $param    = ['messageText' => $message, 'mobiles' => (array) $to, 'lineNumber' => $this->from];
        } else {
            $param    = ['messageText' => $message, 'mobiles' => (array) $to, 'lineNumber' => $this->from, 'sendDateTime' => $sendDateTime];
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($param),
            CURLOPT_HTTPHEADER => array('X-API-KEY: ' . $this->apikey,  'Content-Type: application/json'),
        ));
        $response = curl_exec($curl);
        curl_close($curl);


        return (array)json_decode($response);
    }
    public function sendOneSMSToMultiNumber(array $to, string $message, $sendDateTime = null)
    {
        $this->url .= 'bulk';

        if ($sendDateTime === null) {
            $param    = ['messageText' => $message, 'mobiles' => (array) $to, 'lineNumber' => $this->from];
        } else {
            $param    = ['messageText' => $message, 'mobiles' => (array) $to, 'lineNumber' => $this->from, 'sendDateTime' => $sendDateTime];
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($param),
            CURLOPT_HTTPHEADER => array('X-API-KEY: ' . $this->apikey,  'Content-Type: application/json'),
        ));
        $response = curl_exec($curl);
        curl_close($curl);


        return (array)json_decode($response);
    }
    public function sendMultiSMSToMultiNumber(array $msNum)
    {
    }
    public function sendSMSByPattern(string $to, string $message='',int $bodyId,array $parameters): array
    {

        $this->url .= 'verify';
        $param    = ['mobile' => $to, 'templateId' => $bodyId, 'parameters' => $parameters];


        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url, 
            CURLOPT_RETURNTRANSFER => true, 
            CURLOPT_ENCODING => '', 
            CURLOPT_MAXREDIRS => 10, 
            CURLOPT_TIMEOUT => 0, 
            CURLOPT_FOLLOWLOCATION => true, 
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, 
            CURLOPT_CUSTOMREQUEST => 'POST', 
            CURLOPT_POSTFIELDS =>json_encode($param), 
            CURLOPT_HTTPHEADER => array('Content-Type: application/json','Accept: text/plain','X-API-KEY: ' . $this->apikey),));
        $response = curl_exec($curl);
        curl_close($curl);

        return (array)json_decode($response);
    }
    public function ReciveSMS()
    {
    }
    public function getSMSStatus($messageId): string
    {
        return "";
    }
    public function getCredit(): int
    {
        return 0;
    }
    public function addContact(array $contactInfo)
    {
    }
}

