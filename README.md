# SMS API Library

A comprehensive PHP library for sending SMS messages through various Iranian SMS providers.

[![MIT License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://php.net)

## Overview

This package provides a unified interface for sending SMS messages through different SMS gateways/providers in Iran. It follows SOLID principles with a clean architecture pattern that allows you to easily switch between different SMS service providers.

## Features

- **Unified Interface**: Use the same code for all SMS providers
- **Type Safety**: Full PHP 8.2+ type declarations with strict types
- **Error Handling**: Comprehensive error handling with detailed messages
- **Phone Number Validation**: Automatic normalization and validation of Iranian phone numbers
- **Pattern/Template Support**: Send SMS using predefined patterns
- **Credit Management**: Check account balance across all providers
- **Modern Architecture**: Abstract base class to reduce code duplication

## Supported Providers

| Provider | sendSMS | Pattern | Credit | Receive SMS | Status |
|----------|---------|---------|--------|-------------|--------|
| FarazSMS | ✅ | ✅ | ✅ | ❌ | ✅ |
| SMS.ir | ✅ | ✅ | ✅ | ✅ | ✅ |
| FaraPayamak | ✅ | ✅ | ✅ | ✅ | ✅ |
| Payamito | ✅ | ✅ | ✅ | ✅ | ✅ |
| MedianaSMS | ✅ | ✅ | ✅ | ❌ | ❌ |
| Elanak (SOAP) | ✅ | ❌ | ✅ | ✅ | ✅ |

## Requirements

- PHP 8.2 or higher
- curl extension enabled
- json extension enabled
- soap extension enabled (for Elanak provider)

## Installation

Install the package via Composer:

```bash
composer require jamal13647850/sms-api
```

## Configuration

Copy the example environment file and configure your credentials:

```bash
cp .env.example .env
```

Edit `.env` and fill in your actual API credentials. **Never commit the `.env` file to version control!**

## Basic Usage

```php
<?php
declare(strict_types=1);

require_once 'vendor/autoload.php';

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
    echo "SMS sent successfully! Message ID: " . $result['resultData'];
} else {
    echo "Failed to send SMS. Error ({$result['resultCode']}): " . $result['resultData'];
}

// Check your account balance
$credit = $sms->getCredit();
echo "Your remaining credit: " . $credit;
```

## Response Format

All methods return a standardized response array:

```php
[
    'status' => true|false,      // Boolean indicating success/failure
    'resultCode' => 0,           // Provider-specific result code
    'resultData' => mixed        // Message ID, error message, or data
]
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

### 4. Check Account Credit

```php
$credit = $sms->getCredit();
```

### 5. Get Message Delivery Status

```php
$status = $sms->getSMSStatus('message-id-here');
// Returns: 'sent', 'delivered', 'failed', 'pending', or 'unknown'
```

### 6. Receive Incoming SMS

```php
$messages = $sms->receiveSMS();
```

## Provider-Specific Configuration

### FarazSMS

```php
use jamal13647850\smsapi\FarazSMS;

$gateway = new FarazSMS(
    'your_username',
    'your_password',
    'your_sender_number', 
    'https://ippanel.com/services.jspd' // optional URL
);
```

### SMS.ir

```php
use jamal13647850\smsapi\SMSir;

$gateway = new SMSir(
    'your_api_key',
    'your_sender_number', 
    'https://api.sms.ir/v1/send/' // optional URL
);
```

### FaraPayamak / Payamito

```php
use jamal13647850\smsapi\FaraPayamak;

$gateway = new FaraPayamak(
    'your_username',
    'your_password',
    'your_sender_number',
    'https://rest.payamak-panel.com/api/SendSMS/' // optional URL
);
```

### Elanak

```php
use jamal13647850\smsapi\Elanak;

$gateway = new Elanak(
    'your_username',
    'your_password',
    'your_sender_number',
    'http://158.58.186.243/webservice/' // optional URL
);
```

### MedianaSMS

```php
use jamal13647850\smsapi\MedianaSMS;

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

// Switch to SMS.ir
$smsirGateway = new SMSir('api_key', 'number');
$sms = new SMS($smsirGateway);
$sms->sendSMS('09120000000', 'Test message');
```

## Architecture

This library uses a clean architecture pattern:

- **Gateway Interface**: Defines the contract all providers must implement
- **AbstractGateway**: Provides common functionality (HTTP requests, phone validation, etc.)
- **Provider Classes**: Implement provider-specific logic
- **SMS Facade**: Provides a unified interface for consumers

```
┌─────────────┐     ┌──────────────┐     ┌─────────────────┐
│     SMS     │────▶│   Gateway    │◀────│ AbstractGateway │
│   (Facade)  │     │ (Interface)  │     │   (Base Class)  │
└─────────────┘     └──────────────┘     └─────────────────┘
                                                  │
            ┌──────────┬──────────┬──────────┬────┴─────┬──────────┐
            ▼          ▼          ▼          ▼          ▼          ▼
        ┌──────┐  ┌────────┐ ┌──────────┐ ┌────────┐ ┌────────┐ ┌────────┐
        │Faraz │  │ SMS.ir │ │FaraPayamak│ │Payamito│ │Mediana │ │ Elanak │
        │ SMS  │  │        │ │           │ │        │ │  SMS   │ │ (SOAP) │
        └──────┘  └────────┘ └───────────┘ └────────┘ └────────┘ └────────┘
```

## Error Handling

The library provides detailed error information:

```php
$result = $sms->sendSMS('09120000000', 'Test message');

if ($result['status']) {
    // Success
    $messageId = $result['resultData'];
} else {
    // Error
    $errorCode = $result['resultCode'];
    $errorMessage = $result['resultData'];
    
    echo "Error {$errorCode}: {$errorMessage}";
}
```

Common error codes:
- `0` or positive: Success
- `-1`: General error
- `-2`: cURL error
- `-3`: JSON decode error
- `-4`: Invalid response format
- HTTP status codes: HTTP errors

## Testing

Run the test suite (after configuring credentials):

```bash
php test.php
```

The test file includes commented-out examples for all providers. Uncomment the ones you want to test.

## Contributing

Contributions are welcome! Here's how you can help:

1. Fork the repository
2. Create your feature branch: `git checkout -b feature/my-new-feature`
3. Commit your changes: `git commit -am 'Add some feature'`
4. Push to the branch: `git push origin feature/my-new-feature`
5. Submit a pull request

### Code Style

- Follow PSR-12 coding standards
- Use strict types: `declare(strict_types=1);`
- Add type hints for all parameters and return types
- Write clear documentation comments

## Security

- **Never hardcode credentials** in your code
- Use environment variables or secure configuration management
- Keep your API keys private
- The `.env` file is already in `.gitignore` - don't remove it

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Author

**Sayyed Jamal Ghasemi**  
📧 jamal13647850@gmail.com  
🔗 [LinkedIn](https://www.linkedin.com/in/jamal1364/)
