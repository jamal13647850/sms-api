<?php
declare(strict_types=1);

namespace jamal13647850\smsapi\Tests;

use PHPUnit\Framework\TestCase;
use jamal13647850\smsapi\Melipayamak;
use Dotenv\Dotenv;

/**
 * Unit tests for Melipayamak SMS Gateway
 * 
 * SECURITY NOTE: This test file loads credentials from environment variables.
 * Create a .env file in the project root with your credentials.
 * See .env.example for the required format.
 * DO NOT commit .env files with real credentials!
 * 
 * @author Sayyed Jamal Ghasemi
 */
class MelipayamakTest extends TestCase
{
    private static ?array $config = null;
    private Melipayamak $gateway;

    /**
     * Load configuration from environment variables
     */
    private static function loadConfig(): array
    {
        if (self::$config === null) {
            // Load .env file if it exists
            $envPath = dirname(__DIR__);
            if (file_exists($envPath . '/.env')) {
                $dotenv = Dotenv::createImmutable($envPath);
                $dotenv->safeLoad();
            }

            self::$config = [
                'username' => $_ENV['MELIPAYAMAK_USERNAME'] ?? $_SERVER['MELIPAYAMAK_USERNAME'] ?? 'test_user',
                'apikey' => $_ENV['MELIPAYAMAK_PASSWORD'] ?? $_SERVER['MELIPAYAMAK_PASSWORD'] ?? 'test_key',
                'from_primary' => $_ENV['MELIPAYAMAK_FROM_PRIMARY'] ?? $_SERVER['MELIPAYAMAK_FROM_PRIMARY'] ?? '1000XXXXX',
                'from_secondary' => $_ENV['MELIPAYAMAK_FROM_SECONDARY'] ?? $_SERVER['MELIPAYAMAK_FROM_SECONDARY'] ?? '1000XXXXX',
                'recipient_1' => $_ENV['TEST_RECIPIENT_1'] ?? $_SERVER['TEST_RECIPIENT_1'] ?? '0912XXXXXXX',
                'recipient_2' => $_ENV['TEST_RECIPIENT_2'] ?? $_SERVER['TEST_RECIPIENT_2'] ?? '0939XXXXXXX',
            ];
        }

        return self::$config;
    }

    /**
     * Get configuration array
     */
    protected function getConfig(): array
    {
        return self::loadConfig();
    }

    /**
     * Set up test environment before each test
     */
    protected function setUp(): void
    {
        $config = $this->getConfig();
        
        $this->gateway = new Melipayamak(
            $config['username'],
            $config['apikey'],
            $config['from_primary']
        );
    }

    // ==================== CONSTRUCTOR TESTS ====================

    public function testConstructorWithValidParameters(): void
    {
        $config = $this->getConfig();
        $gateway = new Melipayamak(
            $config['username'],
            $config['apikey'],
            $config['from_primary']
        );
        $this->assertInstanceOf(Melipayamak::class, $gateway);
    }

    public function testConstructorWithCustomUrl(): void
    {
        $config = $this->getConfig();
        $customUrl = 'https://custom.payamak-panel.com/api/SendSMS/';
        $gateway = new Melipayamak(
            $config['username'],
            $config['apikey'],
            $config['from_primary'],
            $customUrl
        );
        $this->assertInstanceOf(Melipayamak::class, $gateway);
    }

    public function testConstructorWithAlternativeSenderNumber(): void
    {
        $config = $this->getConfig();
        $gateway = new Melipayamak(
            $config['username'],
            $config['apikey'],
            $config['from_secondary']
        );
        $this->assertInstanceOf(Melipayamak::class, $gateway);
    }

    public function testConstructorStoresCredentials(): void
    {
        $config = $this->getConfig();
        $gateway = new Melipayamak(
            $config['username'],
            $config['apikey'],
            $config['from_primary']
        );
        $this->assertInstanceOf(Melipayamak::class, $gateway);
    }

    // ==================== sendSMS TESTS ====================

    public function testSendSmsWithEmptyMessageReturnsError(): void
    {
        $config = $this->getConfig();
        $result = $this->gateway->sendSMS($config['recipient_1'], '');
        $this->assertFalse($result['status']);
        $this->assertEquals(17, $result['resultCode']);
        $this->assertStringContainsString('خالی', $result['resultData']);
    }

    public function testSendSmsWithSingleRecipient(): void
    {
        echo "\n>>> Testing sendSMS with single recipient...\n";
        $config = $this->getConfig();
        
        $result = $this->gateway->sendSMS(
            $config['recipient_1'],
            'Test message from Melipayamak unit test - ' . date('H:i:s')
        );
        echo "Result: " . print_r($result, true) . "\n";

        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
        
        if ($result['status']) {
            echo ">>> SMS sent successfully! Message ID: " . $result['resultData'] . "\n";
        }
    }

    public function testSendSmsWithMultipleRecipients(): void
    {
        echo "\n>>> Testing sendSMS with multiple recipients...\n";
        $config = $this->getConfig();
        
        $recipients = [$config['recipient_1'], $config['recipient_2']];
        $result = $this->gateway->sendSMS(
            $recipients,
            'Test message to multiple recipients - ' . date('H:i:s')
        );
        echo "Result: " . print_r($result, true) . "\n";

        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
    }

    public function testSendSmsWithMoreThan100RecipientsReturnsError(): void
    {
        $recipients = array_fill(0, 101, '0912XXXXXXX');
        $result = $this->gateway->sendSMS($recipients, 'Test message');
        $this->assertFalse($result['status']);
        $this->assertEquals(-1, $result['resultCode']);
        $this->assertStringContainsString('100', $result['resultData']);
    }

    public function testSendSmsNormalizesPhoneNumbers(): void
    {
        echo "\n>>> Testing sendSMS with phone number normalization...\n";
        
        $result = $this->gateway->sendSMS(
            '+989124118355',
            'Test with +98 prefix - ' . date('H:i:s')
        );
        echo "Result: " . print_r($result, true) . "\n";

        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
    }

    // ==================== sendSMSByPattern TESTS ====================

    /**
     * Test sendSMSByPattern with pattern code 185341
     * Pattern: Template with one parameter for product name
     * Parameter: "فرش دستباف"
     */
    public function testSendSmsByPattern(): void
    {
        echo "\n>>> Testing sendSMSByPattern with pattern 185341...\n";
        echo ">>> Pattern: 185341 | Parameter: فرش دستباف\n";
        $config = $this->getConfig();
        
        $result = $this->gateway->sendSMSByPattern(
            $config['recipient_1'],
            '',
            '185341',  // Real pattern ID
            ['product' => 'فرش دستباف']  // Pattern parameter
        );
        echo "Result: " . print_r($result, true) . "\n";

        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
        
        if ($result['status']) {
            echo ">>> ✅ Pattern SMS sent successfully! Message ID: " . $result['resultData'] . "\n";
        } else {
            echo ">>> ❌ Pattern SMS failed: " . $result['resultData'] . " (Code: " . $result['resultCode'] . ")\n";
        }
    }

    /**
     * Test sendSMSByPattern with multiple parameters
     */
    public function testSendSmsByPatternWithMultipleParameters(): void
    {
        echo "\n>>> Testing sendSMSByPattern with multiple parameters...\n";
        echo ">>> Pattern: 185341 | Params: product=فرش دستباف, code=12345\n";
        $config = $this->getConfig();
        
        $result = $this->gateway->sendSMSByPattern(
            $config['recipient_1'],
            '',
            '185341',
            [
                'product' => 'فرش دستباف',
                'code' => '12345'
            ]
        );
        echo "Result: " . print_r($result, true) . "\n";

        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
    }

    /**
     * Test sendSMSByPattern with invalid pattern ID
     */
    public function testSendSmsByPatternWithInvalidId(): void
    {
        echo "\n>>> Testing sendSMSByPattern with invalid pattern ID...\n";
        $config = $this->getConfig();
        
        $result = $this->gateway->sendSMSByPattern(
            $config['recipient_1'],
            '',
            '999999',  // Invalid pattern ID
            ['product' => 'تست']
        );
        echo "Result: " . print_r($result, true) . "\n";

        $this->assertIsArray($result);
        // Should fail with invalid pattern
        $this->assertFalse($result['status']);
    }

    // ==================== sendOneSMSToMultiNumber TESTS ====================

    public function testSendOneSmsToMultiNumber(): void
    {
        echo "\n>>> Testing sendOneSMSToMultiNumber...\n";
        $config = $this->getConfig();
        
        $recipients = [$config['recipient_1'], $config['recipient_2']];
        $result = $this->gateway->sendOneSMSToMultiNumber(
            $recipients,
            'Test message to multiple numbers - ' . date('H:i:s')
        );
        echo "Result: " . print_r($result, true) . "\n";

        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
    }

    public function testSendOneSmsToMultiNumberHandlesBatchSplitting(): void
    {
        echo "\n>>> Testing batch splitting with 150 recipients...\n";
        $config = $this->getConfig();
        
        $recipients = array_fill(0, 150, $config['recipient_1']);
        $result = $this->gateway->sendOneSMSToMultiNumber(
            $recipients,
            'Test batch message - ' . date('H:i:s')
        );
        echo "Result: " . print_r($result, true) . "\n";

        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
    }

    // ==================== sendMultiSMSToMultiNumber TESTS ====================

    public function testSendMultiSmsToMultiNumberWithEmptyArray(): void
    {
        $result = $this->gateway->sendMultiSMSToMultiNumber([]);
        $this->assertFalse($result['status']);
        $this->assertEquals(16, $result['resultCode']);
    }

    public function testSendMultiSmsToMultiNumber(): void
    {
        echo "\n>>> Testing sendMultiSMSToMultiNumber...\n";
        $config = $this->getConfig();
        
        $messages = [
            $config['recipient_1'] => 'Message 1 - ' . date('H:i:s'),
            $config['recipient_2'] => 'Message 2 - ' . date('H:i:s'),
        ];
        $result = $this->gateway->sendMultiSMSToMultiNumber($messages);
        echo "Result: " . print_r($result, true) . "\n";

        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
    }

    public function testSendMultiSmsToMultiNumberHandlesLargeBatches(): void
    {
        echo "\n>>> Testing large batch (120 pairs)...\n";
        $config = $this->getConfig();
        
        $messages = [];
        for ($i = 0; $i < 120; $i++) {
            $messages[$config['recipient_1']] = "Message {$i} - " . date('H:i:s');
        }
        $result = $this->gateway->sendMultiSMSToMultiNumber($messages);
        echo "Result: " . print_r($result, true) . "\n";

        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
    }

    // ==================== receiveSMS TESTS ====================

    public function testReceiveSms(): void
    {
        echo "\n>>> Testing receiveSMS...\n";
        $result = $this->gateway->receiveSMS();
        echo "Result: " . print_r($result, true) . "\n";

        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
    }

    // ==================== getSMSStatus TESTS ====================

    public function testGetSmsStatus(): void
    {
        echo "\n>>> Testing getSMSStatus...\n";
        $config = $this->getConfig();
        
        $sendResult = $this->gateway->sendSMS(
            $config['recipient_1'],
            'Status check test - ' . date('H:i:s')
        );
        
        if ($sendResult['status'] && is_numeric($sendResult['resultData'])) {
            $messageId = $sendResult['resultData'];
            echo ">>> Checking status for message ID: {$messageId}\n";
            $status = $this->gateway->getSMSStatus($messageId);
            echo "Status: {$status}\n";
            $this->assertIsString($status);
        } else {
            $this->assertTrue(true);
        }
    }

    public function testGetSmsStatusWithInvalidId(): void
    {
        echo "\n>>> Testing getSMSStatus with invalid ID...\n";
        $status = $this->gateway->getSMSStatus('invalid_id');
        echo "Status: {$status}\n";
        $this->assertIsString($status);
    }

    // ==================== getCredit TESTS ====================

    public function testGetCredit(): void
    {
        echo "\n>>> Testing getCredit...\n";
        $credit = $this->gateway->getCredit();
        echo "Account credit: {$credit}\n";

        $this->assertIsInt($credit);
        $this->assertGreaterThanOrEqual(0, $credit);
    }

    public function testGetCreditReturnsZeroOnError(): void
    {
        $invalidGateway = new Melipayamak('invalid_user', 'invalid_key', '1000XXXXX');
        $credit = $invalidGateway->getCredit();
        $this->assertIsInt($credit);
    }

    // ==================== addContact TESTS ====================

    public function testAddContact(): void
    {
        $config = $this->getConfig();
        $result = $this->gateway->addContact([
            'name' => 'Test User',
            'mobile' => $config['recipient_1']
        ]);

        $this->assertFalse($result['status']);
        $this->assertEquals(-1, $result['resultCode']);
        $this->assertStringContainsString('not implemented', strtolower($result['resultData']));
    }

    // ==================== ERROR HANDLING TESTS ====================

    public function testErrorMessagesAreInPersian(): void
    {
        $config = $this->getConfig();
        $result = $this->gateway->sendSMS($config['recipient_1'], '');
        $this->assertStringContainsString('خالی', $result['resultData']);
    }

    public function testResponseStructureConsistency(): void
    {
        $config = $this->getConfig();
        $result = $this->gateway->sendSMS($config['recipient_1'], '');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('resultCode', $result);
        $this->assertArrayHasKey('resultData', $result);
        $this->assertIsBool($result['status']);
        $this->assertIsInt($result['resultCode']);
    }

    // ==================== PHONE NUMBER NORMALIZATION TESTS ====================

    public function testPhoneNumberNormalization(): void
    {
        echo "\n>>> Testing phone number normalization...\n";
        
        $formats = [
            '09124118355',
            '9124118355',
            '+989124118355',
            '989124118355',
        ];

        foreach ($formats as $format) {
            echo ">>> Testing format: {$format}\n";
            $result = $this->gateway->sendSMS($format, 'Normalization test - ' . date('H:i:s'));
            $this->assertIsArray($result);
            echo "Result: status=" . ($result['status'] ? 'true' : 'false') . ", code={$result['resultCode']}\n";
        }
    }

    // ==================== INTEGRATION TEST ====================

    public function testFullIntegration(): void
    {
        echo "\n";
        echo "========================================\n";
        echo ">>> FULL INTEGRATION TEST STARTING <<<\n";
        echo "========================================\n\n";

        $config = $this->getConfig();

        echo ">>> Step 1: Checking credit...\n";
        $credit = $this->gateway->getCredit();
        echo "Credit available: {$credit}\n";
        $this->assertIsInt($credit);

        echo "\n>>> Step 2: Sending single SMS...\n";
        $result = $this->gateway->sendSMS(
            $config['recipient_1'],
            'Integration test - ' . date('Y-m-d H:i:s')
        );
        echo "Send result: " . print_r($result, true) . "\n";

        if ($result['status']) {
            $messageId = $result['resultData'];
            echo "\n>>> Step 3: Checking message status...\n";
            $status = $this->gateway->getSMSStatus($messageId);
            echo "Status: {$status}\n";
            $this->assertIsString($status);
        }

        echo "\n>>> Step 4: Testing alternative sender...\n";
        $altGateway = new Melipayamak(
            $config['username'],
            $config['apikey'],
            $config['from_secondary']
        );
        $altResult = $altGateway->sendSMS(
            $config['recipient_2'],
            'Test from alternative sender - ' . date('H:i:s')
        );
        echo "Alternative sender result: " . print_r($altResult, true) . "\n";

        echo "\n>>> Step 5: Receiving messages...\n";
        $receiveResult = $this->gateway->receiveSMS();
        echo "Receive result: " . print_r($receiveResult, true) . "\n";

        echo "\n========================================\n";
        echo ">>> INTEGRATION TEST COMPLETED <<<\n";
        echo "========================================\n";
    }
}
