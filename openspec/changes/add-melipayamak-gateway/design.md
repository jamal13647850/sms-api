## Context

The SMS API library follows an Adapter pattern where each SMS provider implements the `Gateway` interface. The library currently has 6 providers implemented (FarazSMS, SMS.ir, MedianaSMS, Elanak, Payamito, FaraPayamak), all following similar patterns by extending `AbstractGateway`.

Mellipayamak (melipayamak.ir) provides both REST and SOAP APIs. The official REST API documentation (`webservice-rest.pdf`) reveals important details:

**API Base URL:** `https://rest.payamak-panel.com/api/SendSMS/`

**Authentication:** Username + ApiKey (not password). ApiKey must be retrieved from panel settings under "توسعه‌دهندگان" menu.

**Key API Endpoints:**
- `SendSMS` - Send SMS to up to 100 recipients (comma-separated or JSON array)
- `SendMultipleSMS` - Send different texts to different numbers (max 100, JSON format)
- `BaseServiceNumber` - Send pattern/template SMS (bodyId parameter)
- `GetDeliveries2` - Check message delivery status (returns status codes 0-500)
- `GetMessages` - Receive messages (location: 1=incoming, 2=outgoing, max 100 records)
- `GetCredit` - Get SMS credit balance

**Response Structure (all endpoints):**
```json
{
  "Value": "response data or recId",
  "RetStatus": 1,        // 1=success, 0=failure
  "StrRetStatus": "Ok"   // "Ok" or error description
}
```

**Error Codes (SendSMS):**
- 0: Invalid credentials (username/ApiKey)
- 1: Success
- 2: Insufficient credit
- 3: Daily send limit reached
- 4: Volume limit reached
- 5: Invalid sender number
- 6: System updating
- 7: Text contains filtered words
- 9: Public line cannot send via webservice
- 10: User inactive
- 11: Not sent
- 12: Incomplete documents
- 14: Text contains link
- 15: Missing cancellation code 11
- 16: No recipient found
- 17: Empty message text
- 18: Invalid mobile number
- 108-111: IP-related security errors

The implementation must follow the existing provider pattern seen in `FarazSMS.php` for consistency.

## Goals / Non-Goals

**Goals:**
- Implement Mellipayamak gateway class extending `AbstractGateway`
- Support all required `Gateway` interface methods using REST API
- Maintain consistent response format: `['status' => bool, 'resultCode' => int, 'resultData' => mixed]`
- Follow PSR-12 coding standards and PHP 8.2+ features (strict types, union types)
- Map Mellipayamak error codes to Persian error messages
- Use the existing cURL infrastructure from `AbstractGateway`

**Non-Goals:**
- SOAP API implementation (REST is simpler and sufficient)
- Async/coroutine support (out of scope, can be added later)
- Contact management features (Mellipayamak API supports this but returns not-implemented status)
- Breaking changes to existing providers
- Additional external dependencies beyond `ext-curl`

## Decisions

### 1. REST API vs SOAP
**Decision:** Use REST API
**Rationale:** The REST API is simpler, more modern, and doesn't require SOAP libraries. The reference implementation shows REST is fully functional for all required operations (send, pattern, credit, status, receive).

### 2. Extend AbstractGateway vs Direct Gateway Implementation
**Decision:** Extend `AbstractGateway`
**Rationale:** Following the pattern established by `FarazSMS` and other providers. This provides:
- Common cURL handling with proper timeout and SSL settings
- Phone number validation and normalization utilities
- Consistent error handling framework

### 3. Error Code Handling
**Decision:** Map Mellipayamak response codes to error messages
**Rationale:** Mellipayamak returns numeric codes (similar to FarazSMS). We'll maintain an internal error message array in Persian to provide meaningful error descriptions, consistent with the existing FarazSMS implementation.

### 4. Pattern SMS Implementation
**Decision:** Use `sendByBaseNumber` API for pattern SMS
**Rationale:** Mellipayamak's pattern SMS uses `bodyId` parameter via `BaseServiceNumber` endpoint. This maps directly to our `sendSMSByPattern` method where `bodyId` becomes the pattern identifier.

### 5. Constructor Parameters (ApiKey Not Password)
**Decision:** Accept `username`, `password`, `from` in constructor, but document that `password` receives ApiKey
**Rationale:** Mellipayamak REST API uses ApiKey for authentication (retrieved from panel settings under Developers menu), not the account password. We keep parameter name as `password` for interface compatibility with other providers, but clearly document it expects ApiKey. The `from` parameter is the sender number.

### 6. SendMultipleSMS for Different Messages
**Decision:** Use `SendMultipleSMS` endpoint for `sendMultiSMSToMultiNumber`
**Rationale:** Unlike other providers that require individual API calls per recipient, Mellipayamak provides a dedicated `SendMultipleSMS` endpoint that accepts arrays of recipients and messages (JSON format, max 100 pairs). This is more efficient than looping through individual sends. Content-Type must be `application/json` for this endpoint.

### 7. Response Parsing Strategy
**Decision:** Parse the consistent JSON structure {Value, RetStatus, StrRetStatus}
**Rationale:** All Mellipayamak REST endpoints return the same response structure. RetStatus=1 indicates success, RetStatus=0 indicates failure. Value contains the actual response data (recId for sends, status codes for deliveries, credit amount, etc.).

### 6. Response Parsing Strategy
**Decision:** Override `parseResponse()` to handle Mellipayamak JSON format
**Rationale:** Mellipayamak returns JSON responses that need to be decoded and checked for error codes before returning the standardized format.

## Risks / Trade-offs

**[Risk] SSL Certificate Verification**
- The reference implementation disables SSL verification (`CURLOPT_SSL_VERIFYPEER => false`)
- **Mitigation:** Follow `AbstractGateway` defaults which enable SSL verification for security. Only disable in development if necessary.

**[Risk] API Endpoint Changes**
- Mellipayamak may change API endpoints or response formats
- **Mitigation:** Use constructor injection for base URL, allowing users to override if needed. Monitor API documentation for changes.

**[Risk] Error Code Coverage**
- Mellipayamak may introduce new error codes not in our mapping
- **Mitigation:** Provide generic "Unknown error (code)" fallback message. Update mapping as new codes are discovered.

**[Risk] Phone Number Format Differences**
- Mellipayamak may expect different number formats than other providers
- **Mitigation:** Use `AbstractGateway::normalizePhoneNumbers()` to standardize to 09xxxxxxxx format before sending. This matches the pattern used by other providers.

**[Trade-off] Limited Error Context**
- Mellipayamak REST API doesn't provide detailed error messages in all cases
- **Acceptance:** Provide best-effort error mapping; users can check `resultCode` for debugging

## Migration Plan

**Deployment Steps:**
1. Create `src/Melipayamak.php` with full implementation
2. Update `test.php` to include Mellipayamak test cases (optional)
3. Run `composer dump-autoload` to register new class
4. Test with valid Mellipayamak credentials
5. Update README.md with provider documentation (optional, follow-up task)

**Rollback Strategy:**
- Simply remove or rename `src/Melipayamak.php` if issues arise
- No database or configuration changes required
- Existing providers unaffected

## Open Questions (Resolved from PDF Documentation)

1. ✅ **Error Code Mapping:** Complete error code list (0-18) extracted from PDF documentation. IP-related codes (108-111) also documented.

2. ✅ **Flash SMS Support:** API supports `isFlash` parameter (boolean, optional). Decision: Not exposed in initial implementation - can be added later if needed. Default to false.

3. ✅ **Multiple Recipients Limit:** 
   - SendSMS: max 100 recipients (comma-separated or JSON)
   - SendMultipleSMS: max 100 recipient-message pairs (JSON array)
   - GetMessages: max 100 records per call

4. ✅ **ApiKey vs Password:** Confirmed - REST API uses ApiKey from panel settings, not account password. Parameter kept as `password` for interface compatibility but documented as expecting ApiKey.
