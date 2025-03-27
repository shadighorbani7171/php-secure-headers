# PHP Secure Headers

[![Latest Version on Packagist](https://img.shields.io/packagist/v/easyshield/php-secure-headers.svg?style=flat-square)](https://packagist.org/packages/easyshield/php-secure-headers)
[![Tests](https://github.com/shadighorbani7171/php-secure-headers/actions/workflows/tests.yml/badge.svg)](https://github.com/shadighorbani7171/php-secure-headers/actions/workflows/tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/easyshield/php-secure-headers.svg?style=flat-square)](https://packagist.org/packages/easyshield/php-secure-headers)

[English](#english) | [فارسی](#persian)

<div id="english">

A powerful PHP library for managing security headers in web applications. This library helps you implement best security practices by easily configuring various security headers including Content Security Policy (CSP), HTTP Strict Transport Security (HSTS), and more.

## Features

- 🛡️ Easy configuration of security headers
- 🔒 Support for Content Security Policy (CSP)
- 🔐 HTTP Strict Transport Security (HSTS)
- 🚫 X-Frame-Options protection
- 🔍 X-Content-Type-Options
- 🛑 X-XSS-Protection
- 📝 Referrer Policy
- 🎯 Permissions Policy
- 📱 Client Hints Policy
- ⚙️ Two security levels: Basic and Strict
- 🔄 Automatic nonce generation for CSP
- ⚡ Framework integrations (Laravel & Symfony)

## Installation

You can install the package via composer:

```bash
composer require easyshield/php-secure-headers
```

## Quick Usage

### Method 1: Plain PHP

Just 5 lines of code to enable all security headers:

```php
<?php
// Create the headers instance
$headers = new \EasyShield\SecureHeaders\SecureHeaders();
$headers->enableAllSecurityHeaders();

// Apply headers
foreach ($headers->getHeaders() as $name => $value) {
    header("$name: $value");
}
```

### Method 2: Laravel Integration

In Laravel, just add the middleware:

```php
<?php
// app/Http/Middleware/SecureHeadersMiddleware.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use EasyShield\SecureHeaders\SecureHeaders;
use Symfony\Component\HttpFoundation\Response;

class SecureHeadersMiddleware
{
    private SecureHeaders $headers;
    
    public function __construct()
    {
        $this->headers = new SecureHeaders();
        $this->headers->enableAllSecurityHeaders();
    }
    
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        foreach ($this->headers->getHeaders() as $name => $value) {
            $response->headers->set($name, $value);
        }
        
        return $response;
    }
}
```

Then register it in `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\App\Http\Middleware\SecureHeadersMiddleware::class);
})
```

### Method 3: Symfony Integration

```php
<?php
// src/EventSubscriber/SecureHeadersSubscriber.php
namespace App\EventSubscriber;

use EasyShield\SecureHeaders\SecureHeaders;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SecureHeadersSubscriber implements EventSubscriberInterface
{
    private SecureHeaders $headers;
    
    public function __construct()
    {
        $this->headers = new SecureHeaders();
        $this->headers->enableAllSecurityHeaders();
    }
    
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }
    
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }
        
        $response = $event->getResponse();
        
        foreach ($this->headers->getHeaders() as $name => $value) {
            $response->headers->set($name, $value);
        }
    }
}
```

## Custom Configuration

Enable only specific headers:

```php
$headers = new \EasyShield\SecureHeaders\SecureHeaders();

// Enable only specific headers
$headers->enableHSTS()
        ->enableXFrameOptions()
        ->enableXContentTypeOptions();
```

### Custom CSP

#### Method 1: Using Array Configuration

```php
$headers->enableCSP([
    'default-src' => ["'self'"],
    'script-src' => ["'self'", "https://trusted.com"],
    'style-src' => ["'self'", "'unsafe-inline'"],
    'img-src' => ["'self'", "data:", "https:"],
    'font-src' => ["'self'", "https://fonts.gstatic.com"],
    'connect-src' => ["'self'", "https://api.example.com"]
]);
```

#### Method 2: Using CSP Builder (Fluent API)

```php
// Get CSP builder instance and configure it
$headers->csp()
    ->allowScripts('https://trusted.com')
    ->allowStyles('https://fonts.googleapis.com')
    ->allowImages('https://images.example.com', 'data:')
    ->allowFonts('https://fonts.gstatic.com')
    ->allowConnections('https://api.example.com')
    ->blockFrames()
    ->useStrictDynamic()
    ->upgradeInsecureRequests();

// Apply the CSP configuration
$headers->enableCSP();
```

#### Advanced CSP Features

**Auto-detecting external resources from HTML:**

```php
// Analyze HTML and automatically add sources to CSP
$html = '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>';
$headers->csp()->detectExternalResourcesFromHtml($html);
$headers->enableCSP();
```

**Auto-injecting nonces into HTML:**

```php
// Inject nonces into script and style tags
$html = '<script>console.log("Hello");</script>';
$modifiedHtml = $headers->csp()->injectNoncesToHtml($html);
$headers->enableCSP();

// Output: <script nonce="random-nonce-value">console.log("Hello");</script>
```

**Using hashes for inline scripts instead of nonces:**

```php
$headers->csp()
    ->addScriptHash('sha256', 'HashOfYourInlineScript')
    ->addStyleHash('sha256', 'HashOfYourInlineStyle');
$headers->enableCSP();
```

### Custom HSTS

```php
$headers->enableHSTS(
    maxAge: 31536000, // 1 year
    includeSubDomains: true,
    preload: true
);
```

### Custom Permissions Policy

```php
$headers->enablePermissionsPolicy([
    'camera' => ["'self'"],
    'microphone' => ["'none'"],
    'geolocation' => ["'self'", "https://maps.example.com"]
]);
```

## Framework Integration

### Laravel

For detailed Laravel instructions, see [examples/Laravel/README.md](examples/Laravel/README.md).

> **Note**: When using the Laravel integration, please include the following attribution in your project's README:
> 
> Laravel integration based on [PHP Secure Headers](https://github.com/shadighorbani7171/php-secure-headers) by [Shadi Ghorbani](https://github.com/shadighorbani7171).

### Symfony 7/8

For detailed Symfony instructions, see [examples/Symfony/README.md](examples/Symfony/README.md).

> **Note**: When using the Symfony integration, please include the following attribution in your project's README:
> 
> Symfony integration based on [PHP Secure Headers](https://github.com/shadighorbani7171/php-secure-headers) by [Shadi Ghorbani](https://github.com/shadighorbani7171).

## Advanced Usage

### Client Hints Policy

```php
$headers->enableClientHintsPolicy([
    'ch-ua-platform' => '*',
    'ch-ua-mobile' => 'true',
    'ch-ua' => 'self'
]);
```

### Critical Client Hints

```php
$headers->enableCriticalCH([
    'Sec-CH-UA-Platform',
    'Sec-CH-UA-Mobile',
    'Sec-CH-UA'
]);
```

## Security Levels

The library supports two security levels:

### Basic Level

* Allows 'unsafe-inline' for styles
* Less restrictive CSP
* Basic permissions policy

### Strict Level (Default)

* No 'unsafe-inline'
* Strict CSP with nonce
* Comprehensive permissions policy
* Enforces upgrade-insecure-requests

## More Examples

For more examples, please refer to the [comprehensive guide](GUIDE.md).

## Testing

```bash
composer test
```

## Code Quality

```bash
# Run all checks (style, syntax, static analysis, tests)
composer check-all

# Fix code style
composer fix-style

# Generate test coverage report
composer test-coverage
```

## Feedback and Contributions

Your feedback is highly appreciated! If you have any suggestions, ideas, or comments, please:

* Open an issue on GitHub
* Share how you're using the library
* Suggest improvements or new features

See [CONTRIBUTING.md](CONTRIBUTING.md) for more information on how to contribute.

## Security

If you discover any security related issues, please email shadighorbani7171@gmail.com instead of using the issue tracker.

## Credits

* [Shadi Ghorbani](https://github.com/shadighorbani7171)
* [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

</div>

---

<div id="persian" dir="rtl">

# هدرهای امنیتی PHP

یک کتابخانه قدرتمند PHP برای مدیریت هدرهای امنیتی در برنامه‌های وب. این کتابخانه با پیکربندی آسان انواع هدرهای امنیتی از جمله Content Security Policy (CSP)، HTTP Strict Transport Security (HSTS) و موارد دیگر، به شما کمک می‌کند بهترین روش‌های امنیتی را پیاده‌سازی کنید.

## ویژگی‌ها

- 🛡️ پیکربندی آسان هدرهای امنیتی
- 🔒 پشتیبانی از سیاست امنیتی محتوا (CSP)
- 🔐 امنیت انتقال سختگیرانه HTTP (HSTS)
- 🚫 محافظت X-Frame-Options
- 🔍 X-Content-Type-Options
- 🛑 X-XSS-Protection
- 📝 سیاست ارجاع (Referrer Policy)
- 🎯 سیاست مجوزها (Permissions Policy)
- 📱 سیاست اطلاعات مشتری (Client Hints Policy)
- ⚙️ دو سطح امنیتی: پایه و سختگیرانه
- 🔄 تولید خودکار nonce برای CSP
- ⚡ ادغام با فریم‌ورک‌ها (Laravel و Symfony)

## نصب

می‌توانید این پکیج را از طریق Composer نصب کنید:

```bash
composer require easyshield/php-secure-headers
```

## استفاده سریع

### روش 1: PHP ساده

تنها با 5 خط کد، تمام هدرهای امنیتی را فعال کنید:

```php
<?php
// ایجاد نمونه هدر
$headers = new \EasyShield\SecureHeaders\SecureHeaders();
$headers->enableAllSecurityHeaders();

// اعمال هدرها
foreach ($headers->getHeaders() as $name => $value) {
    header("$name: $value");
}
```

### روش 2: ادغام با Laravel

در Laravel، کافی است میدلور را اضافه کنید:

```php
<?php
// app/Http/Middleware/SecureHeadersMiddleware.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use EasyShield\SecureHeaders\SecureHeaders;
use Symfony\Component\HttpFoundation\Response;

class SecureHeadersMiddleware
{
    private SecureHeaders $headers;
    
    public function __construct()
    {
        $this->headers = new SecureHeaders();
        $this->headers->enableAllSecurityHeaders();
    }
    
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        foreach ($this->headers->getHeaders() as $name => $value) {
            $response->headers->set($name, $value);
        }
        
        return $response;
    }
}
```

سپس آن را در `bootstrap/app.php` ثبت کنید:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\App\Http\Middleware\SecureHeadersMiddleware::class);
})
```

### روش 3: ادغام با Symfony

```php
<?php
// src/EventSubscriber/SecureHeadersSubscriber.php
namespace App\EventSubscriber;

use EasyShield\SecureHeaders\SecureHeaders;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SecureHeadersSubscriber implements EventSubscriberInterface
{
    private SecureHeaders $headers;
    
    public function __construct()
    {
        $this->headers = new SecureHeaders();
        $this->headers->enableAllSecurityHeaders();
    }
    
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }
    
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }
        
        $response = $event->getResponse();
        
        foreach ($this->headers->getHeaders() as $name => $value) {
            $response->headers->set($name, $value);
        }
    }
}
```

## پیکربندی سفارشی

فعال‌سازی فقط هدرهای خاص:

```php
$headers = new \EasyShield\SecureHeaders\SecureHeaders();

// فعال‌سازی فقط هدرهای خاص
$headers->enableHSTS()
        ->enableXFrameOptions()
        ->enableXContentTypeOptions();
```

### CSP سفارشی

#### روش 1: استفاده از پیکربندی آرایه‌ای

```php
$headers->enableCSP([
    'default-src' => ["'self'"],
    'script-src' => ["'self'", "https://trusted.com"],
    'style-src' => ["'self'", "'unsafe-inline'"],
    'img-src' => ["'self'", "data:", "https:"],
    'font-src' => ["'self'", "https://fonts.gstatic.com"],
    'connect-src' => ["'self'", "https://api.example.com"]
]);
```

#### روش 2: استفاده از CSP Builder (API روان)

```php
// دریافت نمونه CSP builder و پیکربندی آن
$headers->csp()
    ->allowScripts('https://trusted.com')
    ->allowStyles('https://fonts.googleapis.com')
    ->allowImages('https://images.example.com', 'data:')
    ->allowFonts('https://fonts.gstatic.com')
    ->allowConnections('https://api.example.com')
    ->blockFrames()
    ->useStrictDynamic()
    ->upgradeInsecureRequests();

// اعمال پیکربندی CSP
$headers->enableCSP();
```

#### ویژگی‌های پیشرفته CSP

**تشخیص خودکار منابع خارجی از HTML:**

```php
// تحلیل HTML و افزودن خودکار منابع به CSP
$html = '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>';
$headers->csp()->detectExternalResourcesFromHtml($html);
$headers->enableCSP();
```

**تزریق خودکار nonce به HTML:**

```php
// تزریق nonce به تگ‌های script و style
$html = '<script>console.log("Hello");</script>';
$modifiedHtml = $headers->csp()->injectNoncesToHtml($html);
$headers->enableCSP();

// خروجی: <script nonce="مقدار-تصادفی-nonce">console.log("Hello");</script>
```

**استفاده از hash برای اسکریپت‌های درون‌خطی به جای nonce:**

```php
$headers->csp()
    ->addScriptHash('sha256', 'HashOfYourInlineScript')
    ->addStyleHash('sha256', 'HashOfYourInlineStyle');
$headers->enableCSP();
```

### HSTS سفارشی

```php
$headers->enableHSTS(
    maxAge: 31536000, // 1 year
    includeSubDomains: true,
    preload: true
);
```

### Custom Permissions Policy

```php
$headers->enablePermissionsPolicy([
    'camera' => ["'self'"],
    'microphone' => ["'none'"],
    'geolocation' => ["'self'", "https://maps.example.com"]
]);
```

## مستندات بیشتر

برای مثال‌های بیشتر و راهنمای کامل، لطفاً به [راهنمای جامع](GUIDE.md) مراجعه کنید.

## مشارکت

نظرات شما بسیار ارزشمند است! اگر پیشنهاد، ایده یا نظری دارید، لطفاً:

* در GitHub یک issue باز کنید
* نحوه استفاده خود از کتابخانه را به اشتراک بگذارید
* بهبودها یا ویژگی‌های جدید را پیشنهاد دهید

برای اطلاعات بیشتر در مورد نحوه مشارکت، به [CONTRIBUTING.md](CONTRIBUTING.md) مراجعه کنید.

## امنیت

اگر مشکلات مرتبط با امنیت پیدا کردید، لطفاً به جای استفاده از issue tracker، به آدرس shadighorbani7171@gmail.com ایمیل بزنید.

## مجوز

مجوز MIT (MIT). لطفاً برای اطلاعات بیشتر به [فایل مجوز](LICENSE.md) مراجعه کنید.

</div>
