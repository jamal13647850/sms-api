## Why

The SMS API library currently supports multiple Iranian SMS providers (FarazSMS, SMS.ir, MedianaSMS, Elanak, Payamito, FaraPayamak) but lacks support for Mellipayamak (melipayamak.ir), which is one of the major SMS service providers in Iran with over 100,000 registered users. Adding Mellipayamak support will expand the library's provider coverage, giving users more flexibility and choice for their SMS infrastructure needs.

## What Changes

- Add new `Melipayamak` class in `src/Melipayamak.php` implementing the `Gateway` interface
- Support Mellipayamak REST API endpoints (`https://rest.payamak-panel.com/api/SendSMS/`)
- Implement all required Gateway methods:
  - `sendSMS()` - Send SMS to single or multiple recipients
  - `sendSMSByPattern()` - Send SMS using predefined templates (via `sendByBaseNumber` API)
  - `sendOneSMSToMultiNumber()` - Send same message to multiple numbers
  - `sendMultiSMSToMultiNumber()` - Send different messages to different numbers
  - `receiveSMS()` - Receive incoming messages (via `getMessages` API)
  - `getSMSStatus()` - Check message delivery status (via `isDelivered` API)
  - `getCredit()` - Get account credit/balance
  - `addContact()` - Add contact to address book (return not supported status)
- Add Mellipayamak error code mapping for Persian error messages
- Follow existing patterns from `FarazSMS` implementation for consistency
- Support username/ApiKey authentication as required by Mellipayamak REST API (password parameter receives ApiKey from panel settings)

## Capabilities

### New Capabilities
- `melipayamak-gateway`: Implementation of Mellipayamak SMS provider gateway supporting REST API integration for sending SMS, receiving messages, checking delivery status, and managing account credit.

### Modified Capabilities
- None. This change only adds new functionality without modifying existing capabilities.

## Impact

**Code Impact:**
- New file: `src/Melipayamak.php`
- No modifications to existing provider implementations
- No breaking changes to existing APIs

**Dependencies:**
- Requires `ext-curl` PHP extension (already required by other providers)
- No new external library dependencies

**API Compatibility:**
- Fully compatible with existing `Gateway` interface
- Returns standardized response format: `['status' => bool, 'resultCode' => int, 'resultData' => mixed]`
- Supports PHP 8.2+ features (union types, strict typing)

**Testing:**
- Can be tested using existing `test.php` pattern
- Follows same credential-based configuration as other providers
