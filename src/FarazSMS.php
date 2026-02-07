<?php
declare(strict_types=1);

namespace jamal13647850\smsapi;

/**
 * FarazSMS Gateway Implementation
 * 
 * Provides integration with FarazSMS (ippanel.com) SMS service.
 * 
 * @author Sayyed Jamal Ghasemi
 * @see https://ippanel.com
 */
class FarazSMS extends AbstractGateway
{
    private const DEFAULT_URL = 'https://ippanel.com/services.jspd';
    private const PATTERN_URL = 'https://ippanel.com/patterns/pattern';

    private string $url;
    private string $username;
    private string $password;
    private string $from;

    /**
     * Error messages mapping for FarazSMS result codes
     */
    private array $errorMessages = [
        1 => 'متن پیام خالی می باشد.',
        2 => 'کاربر محدود گردیده است.',
        3 => 'خط به شما تعلق ندارد.',
        4 => 'گیرندگان خالی است.',
        5 => 'اعتبار کافی نیست.',
        7 => 'خط مورد نظر برای ارسال انبوه مناسب نمی‌باشد.',
        9 => 'خط مورد نظر در این ساعت امکان ارسال ندارد.',
        98 => 'حداکثر تعداد گیرنده رعایت نشده است.',
        99 => 'اپراتور خط ارسالی قطع می‌باشد.',
        21 => 'پسوند فایل صوتی نامعتبر است.',
        22 => 'سایز فایل صوتی نامعتبر است.',
        23 => 'تعداد تلاش در پیام صوتی نامعتبر است.',
        100 => 'شماره مخاطب دفترچه تلفن نامعتبر می‌باشد.',
        101 => 'شماره مخاطب در دفترچه تلفن وجود دارد.',
        102 => 'شماره مخاطب با موفقیت در دفترچه تلفن ذخیره گردید.',
        111 => 'حداکثر تعداد گیرنده برای ارسال پیام صوتی رعایت نشده است.',
        131 => 'تعداد تلاش در پیام صوتی باید یکبار باشد.',
        132 => 'آدرس فایل صوتی وارد نگردیده است.',
        301 => 'از حرف ویژه در نام کاربری استفاده گردیده است.',
        302 => 'قیمت گذاری انجام نشده است.',
        303 => 'نام کاربری وارد نگردیده است.',
        304 => 'نام کاربری قبلا انتخاب گردیده است.',
        305 => 'نام کاربری وارد نگردیده است.',
        306 => 'کد ملی وارد نشده است.',
        307 => 'کد ملی به خطا وارد شده است.',
        308 => 'شماره شناسنامه نا معتبر است.',
        309 => 'شماره شناسنامه وارد نگردیده است.',
        310 => 'ایمیل کاربر وارد نگردیده است.',
        311 => 'شماره تلفن وارد نگردیده است.',
        312 => 'تلفن به درستی وارد نگردیده است.',
        313 => 'آدرس شما وارد نگردیده است.',
        314 => 'شماره موبایل را وارد نکرده اید.',
        315 => 'شماره موبایل به نادرستی وارد گردیده است.',
        316 => 'سطح دسترسی به نادرستی وارد گردیده است.',
        317 => 'کلمه عبور وارد نشده است.',
        455 => 'ارسال در آینده برای کد بالک ارسالی لغو شد.',
        456 => 'کد بالک ارسالی نامعتبر است.',
        458 => 'کد تیکت نامعتبر است.',
        964 => 'شما دسترسی نمایندگی ندارید.',
        962 => 'نام کاربری یا کلمه عبور نادرست می باشد.',
        963 => 'دسترسی نامعتبر می باشد.',
        971 => 'پترن ارسالی نامعتبر است.',
        970 => 'پارامترهای ارسالی برای پترن نامعتبر است.',
        972 => 'دریافت کننده برای ارسال پترن نامعتبر می باشد.',
        992 => 'ارسال پیام از ساعت 8 تا 23 می باشد.',
        993 => 'دفترچه تلفن باید یک آرایه باشد.',
        994 => 'لطفا تصویری از کارت بانکی خود را از منو مدارک ارسال کنید.',
        995 => 'جهت ارسال با خطوط اشتراکی سامانه، لطفا شماره کارت بانکی خود را به دلیل تکمیل فرایند احراز هویت از بخش ارسال مدارک ثبت نمایید.',
        996 => 'پترن فعال نیست.',
        997 => 'شما اجازه ارسال از این پترن را ندارید.',
        998 => 'کارت ملی یا کارت بانکی شما تایید نشده است.',
        1001 => 'فرمت نام کاربری درست نمی باشد، حداقل ۵ کاراکتر (فقط حروف و اعداد).',
        1002 => 'گذرواژه خیلی ساده می باشد. (حداقل ۸ کاراکتر بوده و نام کاربری، ایمیل و شماره موبایل در آن وجود نداشته باشد.)',
        1003 => 'مشکل در ثبت، با پشتیبانی تماس بگیرید.',
        1004 => 'مشکل در ثبت، با پشتیبانی تماس بگیرید.',
        1005 => 'مشکل در ثبت، با پشتیبانی تماس بگیرید.',
        1006 => 'تاریخ ارسال پیام برای گذشته می باشد، لطفا تاریخ ارسال پیام را به درستی وارد نمایید.',
    ];

    /**
     * Constructor
     *
     * @param string $username API username
     * @param string $password API password
     * @param string $from Sender number
     * @param string|null $url Optional API URL override
     */
    public function __construct(
        string $username,
        string $password,
        string $from,
        ?string $url = null
    ) {
        $this->url = $url ?? self::DEFAULT_URL;
        $this->username = $username;
        $this->password = $password;
        $this->from = $from;
    }

    /**
     * Send SMS to single or multiple recipients
     *
     * @param string|array $to Single phone number or array of numbers
     * @param string $message Message content
     * @return array Response with status, resultCode, and resultData
     */
    public function sendSMS(string|array $to, string $message): array
    {
        if (empty($message)) {
            return [
                'status' => false,
                'resultCode' => 1,
                'resultData' => $this->errorMessages[1]
            ];
        }

        $params = [
            'uname' => $this->username,
            'pass' => $this->password,
            'from' => $this->from,
            'message' => $message,
            'to' => $this->formatRecipients($to),
            'op' => 'send'
        ];

        return $this->executePostRequest($params);
    }

    /**
     * Send SMS using predefined pattern/template
     *
     * @param string $to Recipient phone number
     * @param string $message Optional message (not used with patterns)
     * @param int|string $bodyId Pattern/template ID
     * @param array $parameters Variables to replace in pattern
     * @return array Response with status, resultCode, and resultData
     */
    public function sendSMSByPattern(
        string $to,
        string $message,
        int|string $bodyId,
        array $parameters
    ): array {
        $queryParams = [
            'username' => $this->username,
            'password' => $this->password,
            'from' => $this->from,
            'to' => json_encode([$this->normalizePhoneNumbers($to)]),
            'input_data' => json_encode($parameters),
            'pattern_code' => $bodyId,
        ];

        $url = self::PATTERN_URL . '?' . http_build_query($queryParams);

        $response = $this->executeRequest($url, [
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $parameters,
        ]);

        return $response;
    }

    /**
     * Send same SMS to multiple numbers
     *
     * @param array $to Array of phone numbers
     * @param string $message Message content
     * @return array Response with status, resultCode, and resultData
     */
    public function sendOneSMSToMultiNumber(array $to, string $message): array
    {
        return $this->sendSMS($to, $message);
    }

    /**
     * Send different SMS to different numbers
     *
     * @param array $msNum Array of [number => message] pairs
     * @return array Response with status, resultCode, and resultData
     */
    public function sendMultiSMSToMultiNumber(array $msNum): array
    {
        // FarazSMS doesn't support bulk different messages in single API call
        // We send them individually and aggregate results
        $results = [];
        $allSuccess = true;

        foreach ($msNum as $number => $message) {
            $result = $this->sendSMS($number, $message);
            $results[$number] = $result;
            if (!$result['status']) {
                $allSuccess = false;
            }
        }

        return [
            'status' => $allSuccess,
            'resultCode' => $allSuccess ? 0 : -1,
            'resultData' => $results
        ];
    }

    /**
     * Receive incoming SMS messages
     *
     * @return array Received messages
     */
    public function receiveSMS(): array
    {
        // FarazSMS doesn't provide receive SMS via REST API
        return [
            'status' => false,
            'resultCode' => -1,
            'resultData' => 'Receive SMS not supported by FarazSMS REST API'
        ];
    }

    /**
     * Get message delivery status
     *
     * @param mixed $messageId Message ID
     * @return string Status string
     */
    public function getSMSStatus($messageId): string
    {
        // FarazSMS doesn't provide message status via REST API
        return 'unknown';
    }

    /**
     * Get account credit/balance
     *
     * @return int Remaining credit amount
     */
    public function getCredit(): int
    {
        $params = [
            'uname' => $this->username,
            'pass' => $this->password,
            'op' => 'credit'
        ];

        $response = $this->executePostRequest($params);

        if ($response['status'] && is_numeric($response['resultData'])) {
            return (int) $response['resultData'];
        }

        return 0;
    }

    /**
     * Add contact to address book
     *
     * @param array $contactInfo Contact information
     * @return array Response with status, resultCode, and resultData
     */
    public function addContact(array $contactInfo): array
    {
        // FarazSMS contact management not implemented
        return [
            'status' => false,
            'resultCode' => -1,
            'resultData' => 'Add contact not implemented'
        ];
    }

    /**
     * Execute POST request with form parameters
     *
     * @param array $params Form parameters
     * @return array Parsed response
     */
    private function executePostRequest(array $params): array
    {
        $response = $this->executeRequest($this->url, [
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $params,
        ]);

        return $response;
    }

    /**
     * Parse API response
     *
     * @param string $response Raw response string
     * @return array Parsed response with status, resultCode, and resultData
     */
    protected function parseResponse(string $response): array
    {
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'status' => false,
                'resultCode' => -3,
                'resultData' => 'Invalid JSON response: ' . json_last_error_msg()
            ];
        }

        if (!is_array($data) || count($data) < 2) {
            return [
                'status' => false,
                'resultCode' => -4,
                'resultData' => 'Invalid response format'
            ];
        }

        [$code, $result] = $data;
        $code = (int) $code;

        if ($code === 0) {
            return [
                'status' => true,
                'resultCode' => 0,
                'resultData' => $result
            ];
        }

        return [
            'status' => false,
            'resultCode' => $code,
            'resultData' => $this->errorMessages[$code] ?? "Unknown error ({$code})"
        ];
    }
}
