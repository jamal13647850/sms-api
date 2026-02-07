# 🛡️ راهنمای امنیت - مدیریت اطلاعات حساس

## ⚠️ مهم: هرگز اطلاعات حساس را در کد قرار ندهید!

### ❌ روش نادرست (غیرامن)
```php
// هرگز این کار را نکنید!
$gateway = new Melipayamak(
    '09109568855',                    // نام کاربری در کد
    'c4150f06-312c-4152-b76b-...',    // API Key در کد
    '50004000882270'                  // شماره ارسال‌کننده در کد
);
```

**مشکلات:**
- اطلاعات در Git history ثبت می‌شود
- حتی با حذف، در history باقی می‌ماند
- هر کسی که به repository دسترسی دارد، credentials را می‌بیند
- در صورت public بودن repo، اطلاعات عمومی می‌شود

---

## ✅ روش صحیح: استفاده از Environment Variables

### مرحله ۱: نصب پکیج phpdotenv
```bash
composer require vlucas/phpdotenv
```

### مرحله ۲: ایجاد فایل `.env`
فایل `.env` در root پروژه ایجاد کنید (این فایل در `.gitignore` قرار دارد):

```bash
# Melipayamak Configuration
MELIPAYAMAK_USERNAME=09109568855
MELIPAYAMAK_PASSWORD=c4150f06-312c-4152-b76b-34ac9c525437
MELIPAYAMAK_FROM_PRIMARY=50004000882270
MELIPAYAMAK_FROM_SECONDARY=50001060660924

# Test recipients
TEST_RECIPIENT_1=09124118355
TEST_RECIPIENT_2=09394221468
```

### مرحله ۳: استفاده در کد
```php
use Dotenv\Dotenv;

// Load .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Use environment variables
$gateway = new Melipayamak(
    $_ENV['MELIPAYAMAK_USERNAME'],
    $_ENV['MELIPAYAMAK_PASSWORD'],
    $_ENV['MELIPAYAMAK_FROM_PRIMARY']
);
```

---

## 🔒 چک‌لیست امنیتی

### قبل از commit کردن:
- [ ] فایل `.env` در `.gitignore` قرار دارد
- [ ] هیچ credential در کد نیست
- [ ] فایل `.env.example` با placeholder ها وجود دارد
- [ ] تست‌ها با متغیرهای محیطی کار می‌کنند

### فایل‌هایی که نباید commit شوند:
```
.env                    # فایل اصلی متغیرهای محیطی
.env.local             # تنظیمات محلی
.env.*.local           # تنظیمات محلی محیط‌های مختلف
*.key                  # فایل‌های کلید
*.pem                  # فایل‌های گواهی
config/secrets.php     # فایل‌های اسرار
```

---

## 🔄 روش‌های جایگزین

### روش ۲: استفاده از متغیرهای محیطی سیستم
```bash
# در terminal
export MELIPAYAMAK_USERNAME=09109568855
export MELIPAYAMAK_PASSWORD=c4150f06-312c-4152-b76b-...

# در PHP
$username = getenv('MELIPAYAMAK_USERNAME');
```

### روش ۳: استفاده از فایل کانفیگ خارجی
```php
// config.php - در .gitignore
return [
    'username' => '09109568855',
    'apikey' => 'c4150f06-312c-4152-b76b-...',
];

// در کد اصلی
$config = require 'config.php';
```

### روش ۴: استفاده از Key Management Services
- AWS Secrets Manager
- Azure Key Vault
- HashiCorp Vault
- Google Secret Manager

---

## 📋 نمونه فایل `.env.example`

```bash
# Melipayamak Configuration
# https://melipayamak.ir
# IMPORTANT: MELIPAYAMAK_PASSWORD is the ApiKey from panel settings
MELIPAYAMAK_USERNAME=your_username
MELIPAYAMAK_PASSWORD=your_apikey
MELIPAYAMAK_FROM_PRIMARY=5000XXXXXXXX
MELIPAYAMAK_FROM_SECONDARY=5000XXXXXXXX

# Test Configuration
TEST_RECIPIENT_1=0912XXXXXXX
TEST_RECIPIENT_2=0939XXXXXXX
```

---

## 🚨 در صورت لو رفتن اطلاعات

### ۱. فوراً تغییر دهید:
- وارد پنل Melipayamak شوید
- API Key جدید بسازید
- رمز عبور را تغییر دهید

### ۲. تاریخچه Git را پاک کنید:
```bash
# حذف از تاریخچه (مشکل‌ساز)
git filter-branch --force --index-filter \
"git rm --cached --ignore-unmatch .env" \
--prune-empty --tag-name-filter cat -- --all
```

### ۳. Repository را private کنید (اگر public بود)

---

## ✅ بهترین روش‌ها

### ۱. اصول اصلی:
- هرگز credentials را commit نکنید
- از `.env` استفاده کنید
- فایل `.env.example` ارائه دهید
- مستندات واضح بنویسید

### ۲. برای توسعه تیمی:
```bash
# هر توسعه‌دهنده:
cp .env.example .env
# مقادیر خود را در .env وارد می‌کند
```

### ۳. برای production:
- از secret management service استفاده کنید
- دسترسی محدود به production credentials
- rotation دوره‌ای API keys

---

## 📚 منابع

- [OWASP Secrets Management](https://cheatsheetseries.owasp.org/cheatsheets/Secrets_Management_CheatSheet.html)
- [GitHub - Removing sensitive data](https://docs.github.com/en/authentication/keeping-your-account-and-data-secure/removing-sensitive-data-from-a-repository)
- [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv)

---

## ✨ خلاصه

| روش | امنیت | سادگی | توصیه |
|-----|-------|-------|-------|
| **هاردکد در کد** | ❌ خطرناک | ✅ ساده | ❌ هرگز |
| **`.env` + phpdotenv** | ✅ امن | ✅ ساده | ✅ توصیه شده |
| **متغیرهای محیطی سیستم** | ✅ امن | ⚠️ متوسط | ✅ برای production |
| **Key Management Service** | ✅ بسیار امن | ⚠️ پیچیده | ✅ برای enterprise |

**بهترین روش برای شروع: استفاده از `.env` + phpdotenv**
