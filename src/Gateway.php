<?php
declare (strict_types = 1);

namespace jamal13647850\smsapi;

interface Gateway{
    public function sendSMS(string $to,string $message):array;
    public function sendSMSByPattern(string $to, string $message,int|string $bodyId,array $parameters):array;
    public function sendOneSMSToMultiNumber(array $to,string $message);
    public function sendMultiSMSToMultiNumber(array $msNum);
    public function ReciveSMS();
    public function getSMSStatus($messageId):string;
    public function getCredit():int;
    public function addContact(array $contactInfo);
}