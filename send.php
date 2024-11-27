<?php

use Infobip\Configuration;
use Infobip\Api\SmsApi;
use Infobip\Model\SmsDestination;
use Infobip\Model\SmsTextualMessage;
use Infobip\Model\SmsAdvancedTextualRequest;

require __DIR__ . '/vendor/autoload.php';

$message = $_POST["message"];
$phoneNumber = $_POST["phoneNumber"];

$apiURL = "z3dp53.api.infobip.com";
$apiKey = "71983c148b65ebaeb3a4dbcf32ca39b2-0d7fbfda-dd1d-4b5e-9416-57e3c57f6336";

$configuration = new Configuration(host: $apiURL, apiKey: $apiKey); 
$api = new SmsApi(config: $configuration);

$destination = new SmsDestination(to: $phoneNumber);

$theMessage = new SmsTextualMessage(
    destinations: [$destination],   
    text: $message,
    from: "Syntax Flow"
);

// Send SMS Message
$request = new SmsAdvancedTextualRequest(messages: [$theMessage]);
$response = $api->sendSmsMessage($request);

echo 'SMS Message sent';
?>