<?php
declare (strict_types = 1);

namespace jamal13647850\smsapi;
class SMS{
    protected $gateway;
    public function __construct(Gateway $gateway){
        $this->gateway = $gateway;
    }

    public function sendSMS(string|array $to,string $message):array{
        return $this->gateway->sendSMS($to,$message);
    }

    public function sendSMSByPattern(string $to,string $message,int $bodyId):array{
        return $this->gateway->sendSMSByPattern($to,$message,$bodyId);
    }
    public function sendOneSMSToMultiNumber(array $to,string $message){
        $this->gateway->sendOneSMSToMultiNumber($to,$message);
    }
    public function sendMultiSMSToMultiNumber(array $msNum){
        $this->gateway->sendMultiSMSToMultiNumber($msNum);
    }
    public function ReciveSMS(){
        return $this->gateway->ReciveSMS();
    }
    public function getSMSStatus($messageId):string{
        return $this->gateway->getSMSStatus($messageId);
    }
    public function getCredit():int{
        return $this->gateway->getCredit();
    }
    public function addContact(array $contactInfo){
        $this->gateway->addContact($contactInfo);
    }
}