<?php
define('ABSPATH', __DIR__); 
require "vendor/autoload.php";
use jamal13647850\smsapi\FarazSMS;
use jamal13647850\smsapi\SMS;


try {
    $farazGateway = new FarazSMS('09128112859', 'faraz0083337687', '3000505');
    $sms = new SMS($farazGateway);
    //$res=$sms->sendSMS('09124118355', 'پیامک ارسالی توسط توسعه دهنده لغو۱۱');
    //print_r($res);


    $res=$sms->sendSMSByPattern('09124118355', '','qdwzu30f5m',['code'=>'1232']);
    print_r($res);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
