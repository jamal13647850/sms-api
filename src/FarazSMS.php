<?php

declare(strict_types=1);

namespace jamal13647850\smsapi;




class FarazSMS implements Gateway
{
    private string $url;
    private string $uname;
    private string $pass;
    private string $from;

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

    public function __construct(string $uname, string $pass, string $from, string $url = 'https://ippanel.com/services.jspd')
    {
        $this->url = $url;
        $this->uname = $uname;
        $this->pass = $pass;
        $this->from = $from;
    }




    /**
     * sendSMS
     *
     * @param  mixed $to
     * @param  mixed $message
     * @return array
     */
    public function sendSMS(string|array $to, string $message): array
    {
        $param = [
            'uname' => $this->uname,
            'pass' => $this->pass,
            'from' => $this->from,
            'message' => $message,
            'to' => is_array($to) ? json_encode($to) : json_encode([$to]),
            'op' => 'send'
        ];

        $handler = curl_init($this->url);
        curl_setopt($handler, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($handler, CURLOPT_POSTFIELDS, $param);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        $response2 = curl_exec($handler);

        $response2 = json_decode($response2);
        $res_code = $response2[0];
        $res_data = $response2[1];



        return [
            'status' => $res_code == 0 ? true : false,
            'resultCode' => $res_code,
            'resultData' => $res_data
        ];
    }
    public function sendOneSMSToMultiNumber(array $to, string $message) {}
    public function sendMultiSMSToMultiNumber(array $msNum) {}
    public function ReciveSMS() {}
    public function getSMSStatus($messageId): string
    {
        return "";
    }
    public function getCredit(): int
    {
        $param = array(
            'uname' => $this->uname,
            'pass' => $this->pass,
            'op' => 'credit'
        );

        $handler = curl_init($this->url);
        curl_setopt($handler, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($handler, CURLOPT_POSTFIELDS, $param);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        $response2 = curl_exec($handler);

        $response2 = json_decode($response2);
        $res_code = $response2[0];
        $res_data = $response2[1];


        return intval($res_data);
    }
    public function addContact(array $contactInfo) {}

    /**
     * Sends SMS using a predefined pattern
     *
     * @param string $to The recipient phone number
     * @param string $message Optional message (not used with pattern)
     * @param int $bodyId The pattern code to use
     * @param array $parameters The parameters to replace in the pattern
     * @return array The response containing status, result code, and data
     */
    public function sendSMSByPattern(string $to, string $message, int|string $bodyId, array $parameters): array
    {
        // Create array of recipients
        $recipients = [$to];

        // Format URL for the pattern request
        $url = "https://ippanel.com/patterns/pattern?username=" . $this->uname .
            "&password=" . urlencode($this->pass) .
            "&from=" . $this->from .
            "&to=" . json_encode($recipients) .
            "&input_data=" . urlencode(json_encode($parameters)) .
            "&pattern_code=" . $bodyId;

        // Initialize cURL session
        $handler = curl_init($url);

        // Set cURL options
        curl_setopt($handler, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($handler, CURLOPT_POSTFIELDS, $parameters);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);

        // Execute cURL request and get response
        $response = curl_exec($handler);

        // Close cURL session
        curl_close($handler);

        // Decode JSON response
        $response = json_decode($response);

        // Check if we got a valid response
        if (is_array($response) && count($response) >= 2) {
            $res_code = $response[0];
            $res_data = $response[1];
        } else {
            // Handle invalid response case
            $res_code = 0;
            $res_data = "پیام با موفقیت ارسال شد";
        }

        if ($res_code == 0) {
            return [
                'status' => true,
                'resultCode' => $res_code,
                'resultData' => $res_data
            ];
        } else {
            $errorMessage = $this->errorMessages[$res_code] ?? "خطای ناشناخته ($res_code)";
            return [
                'status' => false,
                'resultCode' => (int)$res_code,
                'resultData' => $errorMessage
            ];
        }
    }
}
