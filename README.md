# SMS API Library

A comprehensive PHP library for sending SMS messages through various Iranian SMS providers.

[![MIT License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

## Overview

This package provides a unified interface for sending SMS messages through different SMS gateways/providers in Iran. It follows SOLID principles with a simple adapter pattern that allows you to easily switch between different SMS service providers.

## Supported Providers

- FarazSMS
- SMSir
- FaraPayamak
- Payamito
- Elanak
- MedianaSMS

## Requirements

- PHP 8.0 or higher
- curl extension enabled

## Installation

Install the package via Composer:

```bash
composer require jamal13647850/sms-api
```

## Basic Usage

```php
<?php

use jamal13647850\smsapi\FarazSMS;
use jamal13647850\smsapi\SMS;

// Create a gateway instance (e.g., FarazSMS)
$gateway = new FarazSMS(
    'your_username', 
    'your_password', 
    'your_sender_number'
);

// Create the SMS service with your chosen gateway
$sms = new SMS($gateway);

// Send a simple SMS
$result = $sms->sendSMS('09120000000', 'Hello, this is a test message');

// Check the result
if ($result['status']) {
    echo "SMS sent successfully!";
} else {
    echo "Failed to send SMS. Error: " . print_r($result, true);
}

// Check your account balance
$credit = $sms->getCredit();
echo "Your remaining credit: " . $credit;
```

## Features

### 1. Send SMS to a Single Number

```php
$result = $sms->sendSMS('09120000000', 'Your message here');
```

### 2. Send SMS to Multiple Numbers

```php
$numbers = ['09120000000', '09987654321'];
$result = $sms->sendSMS($numbers, 'Your message here');
```

### 3. Send SMS Using Predefined Patterns

Some providers support pattern-based messages (templates):

```php
$parameters = [
    'name' => 'John',
    'code' => '1234'
];
$result = $sms->sendSMSByPattern('09120000000', '', 12345, $parameters);
```

Note: The pattern ID (12345 in this example) and parameter format may vary between providers.

### 4. Check Account Credit

```php
$credit = $sms->getCredit();
```

## Provider-Specific Configuration

### FarazSMS

```php
$gateway = new FarazSMS(
    'your_username',
    'your_password',
    'your_sender_number', 
    'https://ippanel.com/services.jspd' // optional URL
);
```

### SMSir

```php
$gateway = new SMSir(
    'your_api_key',
    'your_sender_number', 
    'https://api.sms.ir/v1/send/' // optional URL
);
```

### FaraPayamak / Payamito

```php
$gateway = new FaraPayamak(
    'your_username',
    'your_password',
    'your_sender_number',
    'https://rest.payamak-panel.com/api/SendSMS/' // optional URL
);
```

### Elanak

```php
$gateway = new Elanak(
    'http://158.58.186.243/webservice/', // optional URL
    'your_username',
    'your_password',
    'your_sender_number'
);
```

### MedianaSMS

```php
$gateway = new MedianaSMS(
    'your_username',
    'your_password',
    'your_sender_number',
    'https://ippanel.com/services.jspd' // optional URL
);
```

## Switching Between Providers

One of the main advantages of this library is the ability to easily switch between different SMS providers:

```php
// Using FarazSMS
$farazGateway = new FarazSMS('username', 'password', 'number');
$sms = new SMS($farazGateway);
$sms->sendSMS('09120000000', 'Test message');

// Switch to SMSir
$smsirGateway = new SMSir('api_key', 'number');
$sms = new SMS($smsirGateway);
$sms->sendSMS('09120000000', 'Test message');
```

## Error Handling

Most methods return an array with status information:

```php
$result = $sms->sendSMS('09120000000', 'Test message');

if ($result['status']) {
    // Success
    $messageId = $result['resultData'];
} else {
    // Error
    $errorCode = $result['resultCode'];
    $errorMessage = $result['resultData'];
}
```

## Advanced Usage

### Working with Response Data

Different providers return different response formats. The library standardizes these responses, but provider-specific details may still be available:

```php
$result = $sms->sendSMS('09120000000', 'Test message');

// Common fields across all providers
$success = $result['status']; // boolean
$resultCode = $result['resultCode']; // int
$resultData = $result['resultData']; // mixed (can be message ID, array of IDs, etc.)

// Some providers may include additional data in the response
```

## Contributing

Contributions are welcome! Here's how you can help:

1. Fork the repository
2. Create your feature branch: `git checkout -b my-new-feature`
3. Commit your changes: `git commit -am 'Add some feature'`
4. Push to the branch: `git push origin my-new-feature`
5. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.