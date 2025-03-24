# PHP Secure Headers

[![Latest Version on Packagist](https://img.shields.io/packagist/v/shgh/php-secure-headers.svg?style=flat-square)](https://packagist.org/packages/shgh/php-secure-headers)
[![Tests](https://github.com/shadighorbani7171/php-secure-headers/actions/workflows/tests.yml/badge.svg)](https://github.com/shadighorbani7171/php-secure-headers/actions/workflows/tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/shgh/php-secure-headers.svg?style=flat-square)](https://packagist.org/packages/shgh/php-secure-headers)

یک کتابخانه قدرتمند PHP برای مدیریت هدرهای امنیتی در وب اپلیکیشن‌ها. این کتابخانه به شما کمک می‌کند بهترین روش‌های امنیتی را با پیکربندی آسان هدرهای مختلف امنیتی از جمله Content Security Policy (CSP)، HTTP Strict Transport Security (HSTS) و موارد دیگر پیاده‌سازی کنید.

<div dir="rtl">

## ویژگی‌ها

- 🛡️ پیکربندی آسان هدرهای امنیتی
- 🔒 پشتیبانی از Content Security Policy (CSP)
- 🔐 HTTP Strict Transport Security (HSTS)
- 🚫 محافظت X-Frame-Options
- 🔍 X-Content-Type-Options
- 🛑 X-XSS-Protection
- 📝 Referrer Policy
- 🎯 Permissions Policy
- 📱 Client Hints Policy
- ⚙️ دو سطح امنیتی: پایه و سختگیرانه
- 🔄 تولید خودکار nonce برای CSP
- ⚡ ادغام با فریمورک‌ها (Laravel و Symfony)

## نصب سریع

نصب با Composer:

```bash
composer require shgh/php-secure-headers
```

## استفاده سریع

### روش 1: تنها با چند خط کد

فقط با 5 خط کد، همه هدرهای امنیتی را فعال کنید:

```php
<?php
// هدرهای امنیتی را فعال کنید
$headers = new \SecureHeaders\SecureHeaders();
$headers->enableAllSecurityHeaders();

// هدرها را اعمال کنید
foreach ($headers->getHeaders() as $name => $value) {
    header("$name: $value");
}
```

### روش 2: برای Laravel

در Laravel، فقط میدلور را اضافه کنید:

```php
<?php
// app/Http/Middleware/SecureHeadersMiddleware.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use SecureHeaders\SecureHeaders;
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

### روش 3: برای Symfony

```php
<?php
// src/EventSubscriber/SecureHeadersSubscriber.php
namespace App\EventSubscriber;

use SecureHeaders\SecureHeaders;
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

فقط هدرهای خاصی را فعال کنید:

```php
$headers = new \SecureHeaders\SecureHeaders();

// فقط هدرهای خاص را فعال کنید
$headers->enableHSTS()
        ->enableXFrameOptions()
        ->enableXContentTypeOptions();
```

### CSP سفارشی

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

### HSTS سفارشی

```php
$headers->enableHSTS(
    maxAge: 31536000, // 1 سال
    includeSubDomains: true,
    preload: true
);
```

### Permissions Policy سفارشی

```php
$headers->enablePermissionsPolicy([
    'camera' => ["'self'"],
    'microphone' => ["'none'"],
    'geolocation' => ["'self'", "https://maps.example.com"]
]);
```

## نمونه‌های بیشتر

برای نمونه‌های بیشتر، لطفاً به [راهنمای کامل](GUIDE.md) مراجعه کنید.

## استفاده در فریمورک‌های مختلف

### Laravel

برای راهنمای کامل Laravel، به [examples/Laravel/README.md](examples/Laravel/README.md) مراجعه کنید.

### Symfony

برای راهنمای کامل Symfony، به [examples/Symfony/README.md](examples/Symfony/README.md) مراجعه کنید.

## تست

```bash
composer test
```

## کیفیت کد

```bash
# اجرای همه بررسی‌ها (سبک، نحو، تحلیل استاتیک، تست‌ها)
composer check-all

# اصلاح سبک کد
composer fix-style

# تولید گزارش پوشش تست
composer test-coverage
```

## نکات امنیتی

اگر مشکل امنیتی پیدا کردید، به جای استفاده از سیستم گزارش مشکل، لطفاً با ایمیل shadighorbani7171@gmail.com تماس بگیرید.

## مجوز

مجوز MIT (MIT). برای اطلاعات بیشتر، لطفاً به فایل License مراجعه کنید.

</div>

## English Documentation

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
composer require shgh/php-secure-headers
```

## Basic Usage

```php
use SecureHeaders\SecureHeaders;

// Create instance with strict security level (default)
$headers = new SecureHeaders();

// Or with basic security level
$headers = new SecureHeaders(SecureHeaders::LEVEL_BASIC);

// Enable all security headers
$headers->enableAllSecurityHeaders();

// Get headers
foreach ($headers->getHeaders() as $name => $value) {
    header("$name: $value");
}
```

## Framework Integration

### Laravel

1. Copy the middleware from `examples/Laravel/SecureHeadersMiddleware.php` to your Laravel project's `app/Http/Middleware` directory.
2. Register the middleware in your `bootstrap/app.php` file:

```php
->withMiddleware(function (Middleware $middleware) {
    // Add the SecureHeaders middleware globally
    $middleware->append(\App\Http\Middleware\SecureHeadersMiddleware::class);
})
```

For detailed instructions and alternative approaches, see `examples/Laravel/README.md`.

> **Note**: When using the Laravel integration, please include the following attribution in your project's README:
> 
> Laravel integration based on [PHP Secure Headers](https://github.com/shadighorbani7171/php-secure-headers) by [Shadi Ghorbani](https://github.com/shadighorbani7171).

### Symfony 7/8

1. Copy the subscriber from `examples/Symfony/SecureHeadersSubscriber.php` to your Symfony project's `src/EventSubscriber` directory.
2. The subscriber will be automatically registered thanks to Symfony's autoconfiguration.

For detailed instructions and custom configurations, see `examples/Symfony/README.md`.

> **Note**: When using the Symfony integration, please include the following attribution in your project's README:
> 
> Symfony integration based on [PHP Secure Headers](https://github.com/shadighorbani7171/php-secure-headers) by [Shadi Ghorbani](https://github.com/shadighorbani7171).

## Advanced Usage

### Content Security Policy (CSP)

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

### HTTP Strict Transport Security (HSTS)

```php
$headers->enableHSTS(
    maxAge: 31536000, // 1 year
    includeSubDomains: true,
    preload: true
);
```

### Permissions Policy

```php
$headers->enablePermissionsPolicy([
    'camera' => ["'self'"],
    'microphone' => ["'none'"],
    'geolocation' => ["'self'", "https://maps.example.com"]
]);
```

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

See CONTRIBUTING.md for more information on how to contribute.

## Security

If you discover any security related issues, please email shadighorbani7171@gmail.com instead of using the issue tracker.

## Credits

* Shadi Ghorbani
* All Contributors

## License

The MIT License (MIT) with additional attribution requirements for frameworks and major projects. Please see License File for more information.
