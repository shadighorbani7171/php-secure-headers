# راهنمای استفاده از PHP Secure Headers

این راهنما به شما کمک می‌کند تا در چند دقیقه هدرهای امنیتی را در پروژه PHP خود فعال کنید.

## فهرست مطالب

- [نصب سریع](#نصب-سریع)
- [استفاده پایه](#استفاده-پایه)
- [ادغام با Laravel](#ادغام-با-laravel)
- [ادغام با Symfony](#ادغام-با-symfony)
- [پیکربندی پیشرفته](#پیکربندی-پیشرفته)
- [مقایسه با سایر پکیج‌ها](#مقایسه-با-سایر-پکیج‌ها)

## نصب سریع

```bash
composer require shgh/php-secure-headers
```

همین! فقط یک دستور.

## استفاده پایه

استفاده اولیه فقط **2 خط کد** نیاز دارد:

```php
$headers = new \SecureHeaders\SecureHeaders();
$headers->enableAllSecurityHeaders();

// اعمال هدرها
foreach ($headers->getHeaders() as $name => $value) {
    header("$name: $value");
}
```

با این کد ساده، تمام هدرهای امنیتی زیر فعال می‌شوند:
- Content-Security-Policy
- Strict-Transport-Security
- X-Frame-Options
- X-Content-Type-Options
- X-XSS-Protection
- Referrer-Policy
- Permissions-Policy

## ادغام با Laravel

### نصب در Laravel (فقط 2 دقیقه!)

**گام 1**: میدلور ایجاد کنید (یا از نمونه ما کپی کنید)

```php
<?php

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
    
    // برای استفاده با Blade و Vite
    public function getNonce(): string
    {
        return $this->headers->getNonce();
    }
}
```

**گام 2**: میدلور را در `bootstrap/app.php` ثبت کنید

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\App\Http\Middleware\SecureHeadersMiddleware::class);
})
```

**گام 3**: برای استفاده از nonce در بلید و Vite

```php
// در برنامه، Middleware را singleton کنید
$this->app->singleton(\App\Http\Middleware\SecureHeadersMiddleware::class);

// در AppServiceProvider.php
public function boot(): void
{
    if (class_exists('\Illuminate\Foundation\Vite')) {
        \Illuminate\Foundation\Vite::useCspNonce(
            app(\App\Http\Middleware\SecureHeadersMiddleware::class)->getNonce()
        );
    }
}
```

در فایل‌های Blade، از nonce استفاده کنید:

```blade
<script nonce="{{ app(\App\Http\Middleware\SecureHeadersMiddleware::class)->getNonce() }}">
    // کد جاوااسکریپت شما
</script>
```

## ادغام با Symfony

### نصب در Symfony (فقط 2 دقیقه!)

**گام 1**: EventSubscriber ایجاد کنید (یا از نمونه ما کپی کنید)

```php
<?php

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
    
    public function getNonce(): string
    {
        return $this->headers->getNonce();
    }
}
```

**گام 2**: در Symfony، به لطف autoconfiguration، هیچ تنظیم اضافی نیاز نیست!

**گام 3**: برای استفاده از nonce در Twig:

```php
// تنظیم Twig Extension
namespace App\Twig;

use App\EventSubscriber\SecureHeadersSubscriber;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SecureHeadersExtension extends AbstractExtension
{
    private SecureHeadersSubscriber $secureHeaders;
    
    public function __construct(SecureHeadersSubscriber $secureHeaders)
    {
        $this->secureHeaders = $secureHeaders;
    }
    
    public function getFunctions(): array
    {
        return [
            new TwigFunction('csp_nonce', [$this, 'getNonce']),
        ];
    }
    
    public function getNonce(): string
    {
        return $this->secureHeaders->getNonce();
    }
}
```

در قالب‌های Twig:

```twig
<script nonce="{{ csp_nonce() }}">
    // کد جاوااسکریپت شما
</script>
```

## پیکربندی پیشرفته

### سفارشی‌سازی CSP

```php
$headers->enableCSP([
    'default-src' => ["'self'"],
    'script-src' => ["'self'", "https://trusted.com", "'nonce-" . $headers->getNonce() . "'"],
    'style-src' => ["'self'", "https://fonts.googleapis.com"],
    'img-src' => ["'self'", "data:", "https:"],
    'font-src' => ["'self'", "https://fonts.gstatic.com"],
    'connect-src' => ["'self'", "https://api.example.com"],
    'frame-ancestors' => ["'none'"],
    'form-action' => ["'self'"],
    'base-uri' => ["'self'"],
    'upgrade-insecure-requests' => true
]);
```

### تنظیم HSTS

```php
$headers->enableHSTS(
    maxAge: 31536000, // 1 سال
    includeSubDomains: true,
    preload: true
);
```

### تنظیم Permissions Policy

```php
$headers->enablePermissionsPolicy([
    'camera' => ["'self'"],
    'microphone' => ["'none'"],
    'geolocation' => ["'self'", "https://maps.example.com"],
    'payment' => ["'self'"]
]);
```

## مقایسه با سایر پکیج‌ها

| ویژگی | PHP Secure Headers (ما) | bepsvpt/secure-headers | paragonie/csp-builder |
|-------|--------------------|-----------------------|----------------------|
| سهولت استفاده | ✅ API روان و ساده | 🟡 نیاز به فایل کانفیگ | 🟡 فقط CSP |
| پشتیبانی Laravel | ✅ کامل | ✅ کامل | ❌ ندارد |
| پشتیبانی Symfony | ✅ کامل | ❌ ندارد | ❌ ندارد |
| نیاز به تنظیمات | ✅ کمترین - فقط کد | 🟡 فایل کانفیگ | 🟡 آرایه پیکربندی |
| CSP با nonce | ✅ خودکار | ✅ نیاز به تنظیم | ✅ نیاز به تنظیم |
| پوشش تست | ✅ 100% | 🟡 نامشخص | 🟡 نامشخص |
| حجم پکیج | ✅ سبک | 🟡 متوسط | ✅ سبک |
| سرعت اجرا | ✅ بهینه | 🟡 متوسط | ✅ بهینه |
| انعطاف‌پذیری API | ✅ بالا | 🟡 متوسط | 🟡 فقط برای CSP |


## نمونه کدهای آماده برای فریمورک‌های JavaScript

### Vue.js

```php
$headers->enableCSP([
    'default-src' => ["'self'"],
    'script-src' => ["'self'", "'nonce-" . $headers->getNonce() . "'"],
    'style-src' => ["'self'", "'unsafe-inline'"], // برای Vue styles
    'img-src' => ["'self'", "data:"],
    'connect-src' => ["'self'", "https://api.example.com"]
]);
```

### React

```php
$headers->enableCSP([
    'default-src' => ["'self'"],
    'script-src' => ["'self'", "'nonce-" . $headers->getNonce() . "'"],
    'style-src' => ["'self'", "'unsafe-inline'"], // برای styled-components
    'img-src' => ["'self'", "data:"],
    'connect-src' => ["'self'", "https://api.example.com"]
]);
```

### Alpine.js

```php
$headers->enableCSP([
    'default-src' => ["'self'"],
    'script-src' => ["'self'", "'nonce-" . $headers->getNonce() . "'"],
    'style-src' => ["'self'", "'unsafe-inline'"], // برای Alpine style bindings
    'img-src' => ["'self'", "data:"],
    'connect-src' => ["'self'", "https://api.example.com"]
]);
```

---

## نمونه محیط تولید برای Laravel

```php
<?php

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
        
        if (app()->environment('production')) {
            // تنظیمات سختگیرانه برای محیط تولید
            $this->headers->enableAllSecurityHeaders();
            $this->headers->enableHSTS(maxAge: 31536000, includeSubDomains: true, preload: true);
            
            // CSP سختگیرانه با nonce
            $this->headers->enableCSP([
                'default-src' => ["'self'"],
                'script-src' => ["'self'", "'nonce-" . $this->headers->getNonce() . "'"],
                'style-src' => ["'self'", "https://fonts.googleapis.com", "'nonce-" . $this->headers->getNonce() . "'"],
                'img-src' => ["'self'", "data:"],
                'font-src' => ["'self'", "https://fonts.gstatic.com"],
                'connect-src' => ["'self'"],
                'frame-ancestors' => ["'none'"],
                'form-action' => ["'self'"],
                'base-uri' => ["'self'"],
                'upgrade-insecure-requests' => true
            ]);
        } else {
            // تنظیمات راحت‌تر برای محیط توسعه
            $this->headers->enableXFrameOptions('SAMEORIGIN');
            $this->headers->enableXContentTypeOptions();
            
            // CSP با اجازه برای ابزارهای توسعه
            $this->headers->enableCSP([
                'default-src' => ["'self'"],
                'script-src' => ["'self'", "'unsafe-eval'", "'unsafe-inline'"],
                'style-src' => ["'self'", "'unsafe-inline'", "https://fonts.googleapis.com"],
                'img-src' => ["'self'", "data:", "*"],
                'font-src' => ["'self'", "https://fonts.gstatic.com", "data:"],
                'connect-src' => ["'self'", "ws:", "wss:"] // برای hot reload
            ]);
        }
        
        // اگر از Vite استفاده می‌شود
        if (class_exists('\Illuminate\Foundation\Vite')) {
            \Illuminate\Foundation\Vite::useCspNonce($this->headers->getNonce());
        }
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        foreach ($this->headers->getHeaders() as $name => $value) {
            $response->headers->set($name, $value);
        }
        
        return $response;
    }
    
    public function getNonce(): string
    {
        return $this->headers->getNonce();
    }
}
```

موفق باشید! 🚀 