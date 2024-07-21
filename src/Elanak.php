<?php
declare (strict_types = 1);

namespace jamal13647850\smsapi;

class Elanak implements Gateway {
    private $sendSoap,$numberSoap,$timeSoap;

    private $messageType,$userName,$password,$fromNum;

    
    public function __construct($soapUrl="http://158.58.186.243/webservice/",$userName,$password,$fromNum)
    {
        $this->sendSoap=new \SoapClient($soapUrl."send.php?wsdl");
        $this->numberSoap=new \SoapClient($soapUrl."number.php?wsdl");
        $this->timeSoap=new \SoapClient($soapUrl."time.php?wsdl");
        $this->messageType ="0";
        $this->userName =$userName;
        $this->password =$password;
        $this->fromNum =$fromNum;
    }

    public function setMessageType($messageType){
        $this->messageType = $messageType;
    }
    public function setUserName($userName){
        $this->userName = $userName;  
    }
    public function setPassword($password){
        $this->password = $password;
    }
    public function setFromNum($fromNum){
        $this->fromNum = $fromNum;
    }

    public function sendSMS(string $to,string $message):array{
        $this->sendSoap->Username = $this->userName;
        $this->sendSoap->Password = $this->password;
        $this->sendSoap->fromNum  = $this->fromNum;
        $this->sendSoap->toNum    = [$to];
        $this->sendSoap->Content  = $message;
        $this->sendSoap->Type     = $this->messageType;
        return $this->sendSoap->SendSMS($this->sendSoap->fromNum,$this->sendSoap->toNum,$this->sendSoap->Content,$this->sendSoap->Type,$this->sendSoap->Username,$this->sendSoap->Password);
    }
    public function sendSMSByPattern(string $to, string $message, int $bodyId,array $parameters): array
    {
        return [];
    }
    public function sendOneSMSToMultiNumber(array $to,string $message){}
    public function sendMultiSMSToMultiNumber(array $msNum){}
    public function ReciveSMS(){}
    public function getSMSStatus($messageId):string{
        return "";
    }
    public function getCredit():int{
        return 0;
    }
    public function addContact(array $contactInfo){}
}