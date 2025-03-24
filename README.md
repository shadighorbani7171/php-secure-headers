# PHP Secure Headers

یک کتابخانه PHP ساده و قدرتمند برای مدیریت هدرهای امنیتی در برنامه‌های وب.

A simple yet powerful PHP library for managing security headers in web applications.

## ✨ ویژگی‌ها / Features

- 🔒 پیکربندی آسان هدرهای امنیتی / Easy configuration of security headers
- 🛡️ پشتیبانی از CSP با قابلیت nonce / CSP support with nonce capability
- 🎯 تنظیمات پیش‌فرض امن / Secure default settings
- 🔄 قابلیت شخصی‌سازی کامل / Complete customization capabilities
- 📚 مستندات کامل و مثال‌های کاربردی / Complete documentation with practical examples
- 🎚️ پشتیبانی از سطوح مختلف امنیتی (basic و strict) / Support for different security levels (basic and strict)
- **HSTS (HTTP Strict Transport Security)**
  - Configurable max-age
  - Optional includeSubDomains
  - Optional preload flag
- **CSP (Content Security Policy)**
  - Two security levels: Basic and Strict
  - Automatic nonce generation for scripts
  - Configurable policies for various content types
  - Strict mode with additional security directives
- **X-Frame-Options**
  - DENY or SAMEORIGIN options
  - Automatic configuration based on security level
- **X-Content-Type-Options**
  - Prevents MIME type sniffing
- **X-XSS-Protection**
  - Enables browser's XSS filtering
  - Block mode enabled
- **Referrer Policy**
  - Configurable referrer policy
  - Default: strict-origin-when-cross-origin
- **Permissions Policy**
  - Configurable feature permissions
  - Different defaults for Basic and Strict modes
  - Support for custom policies
- **Critical-CH**
  - Configurable critical client hints
  - Default hints: Sec-CH-UA, Sec-CH-UA-Mobile, Sec-CH-UA-Platform
- **Client Hints Policy**
  - Configurable client hints delegation
  - Integration with Permissions Policy
  - Default configuration for browser information sharing

## 📥 نصب / Installation

```bash
composer require shadi/php-secure-headers
```

## 🚀 شروع سریع / Quick Start

```php
use SecureHeaders\SecureHeaders;

// ایجاد نمونه با سطح امنیتی strict (پیش‌فرض)
// Create instance with strict security level (default)
$headers = new SecureHeaders();

// یا با سطح امنیتی basic
// Or with basic security level
$headers = new SecureHeaders(SecureHeaders::LEVEL_BASIC);

// فعال‌سازی همه هدرهای امنیتی با یک دستور
// Enable all security headers with one command
$headers->enableAllSecurityHeaders();

// اعمال هدرها
// Apply headers
$headers->apply();
```

## 🔐 سطوح امنیتی / Security Levels

کتابخانه از دو سطح امنیتی پشتیبانی می‌کند:
The library supports two security levels:

### Basic (پایه)
مناسب برای سایت‌های معمولی و محیط توسعه:
Suitable for regular websites and development environments:
```php
$headers = new SecureHeaders(SecureHeaders::LEVEL_BASIC);
```
- CSP با قوانین ساده‌تر / CSP with simpler rules
- اجازه `unsafe-inline` برای استایل‌ها / Allows `unsafe-inline` for styles
- دسترسی محدود به دوربین و میکروفون / Limited access to camera and microphone
- X-Frame-Options: SAMEORIGIN

### Strict (سختگیرانه)
مناسب برای برنامه‌های حساس و محیط تولید:
Suitable for sensitive applications and production environments:
```php
$headers = new SecureHeaders(SecureHeaders::LEVEL_STRICT);
```
- CSP با قوانین سختگیرانه / CSP with stricter rules
- استفاده از nonce برای اسکریپت‌ها / Uses nonce for scripts
- غیرفعال کردن همه دسترسی‌های حساس / Disables all sensitive permissions
- X-Frame-Options: DENY
- ارتقای خودکار به HTTPS / Automatic upgrade to HTTPS

## 🔍 نمونه کامل / Complete Example

```php
<?php
use SecureHeaders\SecureHeaders;

// ایجاد نمونه با سطح امنیتی strict
// Create instance with strict security level
$headers = new SecureHeaders(SecureHeaders::LEVEL_STRICT);

// فعال‌سازی همه هدرها با یک دستور
// Enable all headers with one command
$headers->enableAllSecurityHeaders();

// یا فعال‌سازی جداگانه هر هدر
// Or enable each header separately
$headers->enableHSTS();
$headers->enableXFrameOptions();
$headers->enableXContentTypeOptions();
$headers->enableXXSSProtection();
$headers->enableReferrerPolicy();
$headers->enablePermissionsPolicy();

// تنظیم CSP با قوانین سفارشی
// Configure CSP with custom rules
$headers->enableCSP([
    'script-src' => ["'self'", "https://trusted-cdn.com"],
    'style-src' => ["'self'", "https://fonts.googleapis.com"],
]);

// فعال‌سازی Client Hints Policy برای اشتراک اطلاعات مرورگر
// Enable Client Hints Policy for browser information sharing
$headers->enableClientHintsPolicy([
    'ch-ua-platform' => '*',
    'ch-ua' => 'self'
]);

// فعال‌سازی Critical-CH برای درخواست اطلاعات مرورگر
// Enable Critical-CH to request browser information
$headers->enableCriticalCH(['Sec-CH-UA', 'Sec-CH-UA-Platform', 'Sec-CH-UA-Mobile']);

// اعمال همه هدرها
// Apply all headers
$headers->apply();

// دریافت nonce برای اسکریپت‌های inline
// Get nonce for inline scripts
$nonce = $headers->getNonce();
?>
<!DOCTYPE html>
<html>
<head>
    <title>نمونه امن / Secure Example</title>
</head>
<body>
    <script nonce="<?= htmlspecialchars($nonce) ?>">
        console.log('اسکریپت امن با nonce / Secure script with nonce');
    </script>
</body>
</html>
```

## 📝 راهنمای استفاده از Client Hints / Client Hints Usage Guide

### Client Hints Policy
برای کنترل دقیق اطلاعاتی که مرورگر با سایت‌های ثالث به اشتراک می‌گذارد، می‌توانید از Client Hints Policy استفاده کنید:

For precise control over what information the browser shares with third-party sites, you can use Client Hints Policy:

```php
// اجازه دسترسی به اطلاعات پلتفرم سیستم به همه سایت‌ها و 
// اطلاعات مرورگر فقط به دامنه خودمان
// Allow access to platform info for all sites and
// browser info only to our domain
$headers->enableClientHintsPolicy([
    'ch-ua-platform' => '*',
    'ch-ua' => 'self'
]);
```

### Critical-CH
برای درخواست اطلاعات خاص از مرورگر کاربر:

To request specific information from the user's browser:

```php
// درخواست اطلاعات مشخصی از مرورگر کاربر
// Request specific information from the user's browser
$headers->enableCriticalCH([
    'Sec-CH-UA',              // اطلاعات مرورگر / Browser info
    'Sec-CH-UA-Platform',     // سیستم عامل / Operating system
    'Sec-CH-UA-Mobile'        // وضعیت موبایل / Mobile status
]);
```

## 🤝 مشارکت / Contributing

از مشارکت شما استقبال می‌کنیم! لطفاً:
We welcome your contributions! Please:

1. Fork کنید / Fork the repository
2. یک branch جدید بسازید / Create a new branch
3. تغییرات خود را اعمال کنید / Make your changes
4. یک Pull Request ارسال کنید / Submit a Pull Request
