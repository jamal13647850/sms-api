<?php

declare(strict_types=1);

namespace jamal13647850\smsapi;

defined('ABSPATH') || exit();


class MedianaSMS implements Gateway
{
    private string $url;
    private string $uname;
    private string $pass;
    private string $from;

    public function __construct(string $uname, string $pass, string $from, string $url = 'https://ippanel.com/services.jspd')
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
        $param = [
            'uname' => $this->uname,
            'pass' => $this->pass,
            'from' => $this->from,
            'message' => $message,
            'to' => is_array($to)?json_encode($to):json_encode([$to]),
            'op' => 'send'
        ];

        $handler = curl_init($this->url);
        curl_setopt($handler, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($handler, CURLOPT_POSTFIELDS, $param);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        $response2 = curl_exec($handler);

        $response2 = json_decode($response2);
        $res_code = $response2[0];
        $res_data = $response2[1];



        return [
            'status'=>$res_code==0?true:false,
            'resultCode'=>$res_code,        
            'resultData'=>$res_data        
        ];
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
        $param = array(
            'uname' => $this->uname,
            'pass' => $this->pass,
            'op' => 'credit'
        );

        $handler = curl_init($this->url);
        curl_setopt($handler, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($handler, CURLOPT_POSTFIELDS, $param);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        $response2 = curl_exec($handler);

        $response2 = json_decode($response2);
        $res_code = $response2[0];
        $res_data = $response2[1];


        return intval($res_data);
    }
    public function addContact(array $contactInfo)
    {
    }

    public function sendSMSByPattern(string $to, string $message, int $bodyId): array
    {
        return [];
    }
}
