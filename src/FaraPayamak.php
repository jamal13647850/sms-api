<?php

declare(strict_types=1);

namespace jamal13647850\smsapi;

defined('ABSPATH') || exit();


class FaraPayamak implements Gateway
{
    private string $url;
    private string $uname;
    private string $pass;
    private string $from;

    public function __construct(string $uname, string $pass, string $from, string $url = 'https://rest.payamak-panel.com/api/SendSMS/')
    {
        $this->url = $url;
        $this->uname = $uname;
        $this->pass = $pass;
        $this->from = $from;
    }



    /**
     * sendSMS
     *
     * @param  mixed $to
     * @param  mixed $message
     * @return array
     */
    public function sendSMS(string|array $to, string $message): array
    {
        if (is_array($to)) {
            $to = implode(",", $to);
        }
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url.'SendSMS',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'username=' . $this->uname . '&password=' . $this->pass . '&to=' . $to . '&from=' . $this->from . '&text=' . $message,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return (array)json_decode($response);
    }
    public function sendOneSMSToMultiNumber(array $to, string $message)
    {
    }
    public function sendMultiSMSToMultiNumber(array $msNum)
    {
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
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url.'GetCredit',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'username=' . $this->uname . '&password=' . $this->pass ,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $results= (array)json_decode($response);

        return (int)($results['RetStatus']==1?$results['Value']:0);
    }
    public function addContact(array $contactInfo)
    {
    }
}
