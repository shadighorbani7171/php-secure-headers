# PHP Secure Headers - Comprehensive User Guide

This guide will help you implement security headers in your PHP project in just a few minutes.

## Table of Contents

- [Quick Installation](#quick-installation)
- [Basic Usage](#basic-usage)
- [Laravel Integration](#laravel-integration)
- [Symfony Integration](#symfony-integration)
- [Advanced Configuration](#advanced-configuration)
- [Comparison with Other Packages](#comparison-with-other-packages)

## Quick Installation

```bash
composer require shgh/php-secure-headers
```

That's it! Just one command.

## Basic Usage

Basic usage requires only **2 lines of code**:

```php
$headers = new \SecureHeaders\SecureHeaders();
$headers->enableAllSecurityHeaders();

// Apply headers
foreach ($headers->getHeaders() as $name => $value) {
    header("$name: $value");
}
```

With this simple code, all the following security headers are enabled:
- Content-Security-Policy
- Strict-Transport-Security
- X-Frame-Options
- X-Content-Type-Options
- X-XSS-Protection
- Referrer-Policy
- Permissions-Policy

## Laravel Integration

### Installation in Laravel (Just 2 minutes!)

**Step 1**: Create a middleware (or copy our example)

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
    
    // For use with Blade and Vite
    public function getNonce(): string
    {
        return $this->headers->getNonce();
    }
}
```

**Step 2**: Register the middleware in `bootstrap/app.php`

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\App\Http\Middleware\SecureHeadersMiddleware::class);
})
```

**Step 3**: For using nonce with Blade and Vite

```php
// In your application, make Middleware a singleton
$this->app->singleton(\App\Http\Middleware\SecureHeadersMiddleware::class);

// In AppServiceProvider.php
public function boot(): void
{
    if (class_exists('\Illuminate\Foundation\Vite')) {
        \Illuminate\Foundation\Vite::useCspNonce(
            app(\App\Http\Middleware\SecureHeadersMiddleware::class)->getNonce()
        );
    }
}
```

In Blade files, use the nonce:

```blade
<script nonce="{{ app(\App\Http\Middleware\SecureHeadersMiddleware::class)->getNonce() }}">
    // Your JavaScript code
</script>
```

## Symfony Integration

### Installation in Symfony (Just 2 minutes!)

**Step 1**: Create an EventSubscriber (or copy our example)

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

**Step 2**: In Symfony, thanks to autoconfiguration, no additional setup is needed!

**Step 3**: For using nonce in Twig:

```php
// Set up a Twig Extension
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

In Twig templates:

```twig
<script nonce="{{ csp_nonce() }}">
    // Your JavaScript code
</script>
```

## Advanced Configuration

### Customizing CSP

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

### Configuring HSTS

```php
$headers->enableHSTS(
    maxAge: 31536000, // 1 year
    includeSubDomains: true,
    preload: true
);
```

### Configuring Permissions Policy

```php
$headers->enablePermissionsPolicy([
    'camera' => ["'self'"],
    'microphone' => ["'none'"],
    'geolocation' => ["'self'", "https://maps.example.com"],
    'payment' => ["'self'"]
]);
```

## Comparison with Other Packages

| Feature | PHP Secure Headers (Ours) | bepsvpt/secure-headers | paragonie/csp-builder |
|-------|--------------------|-----------------------|----------------------|
| Ease of Use | ✅ Fluent, simple API | 🟡 Requires config file | 🟡 CSP only |
| Laravel Support | ✅ Full | ✅ Full | ❌ None |
| Symfony Support | ✅ Full | ❌ None | ❌ None |
| Configuration Needs | ✅ Minimal - code only | 🟡 Config file | 🟡 Config array |
| CSP with nonce | ✅ Automatic | ✅ Requires setup | ✅ Requires setup |
| Test Coverage | ✅ 100% | 🟡 Unknown | 🟡 Unknown |
| Package Size | ✅ Light | 🟡 Medium | ✅ Light |
| Execution Speed | ✅ Optimized | 🟡 Medium | ✅ Optimized |
| API Flexibility | ✅ High | 🟡 Medium | 🟡 CSP only |


## Ready-to-Use Code Samples for JavaScript Frameworks

### Vue.js

```php
$headers->enableCSP([
    'default-src' => ["'self'"],
    'script-src' => ["'self'", "'nonce-" . $headers->getNonce() . "'"],
    'style-src' => ["'self'", "'unsafe-inline'"], // For Vue styles
    'img-src' => ["'self'", "data:"],
    'connect-src' => ["'self'", "https://api.example.com"]
]);
```

### React

```php
$headers->enableCSP([
    'default-src' => ["'self'"],
    'script-src' => ["'self'", "'nonce-" . $headers->getNonce() . "'"],
    'style-src' => ["'self'", "'unsafe-inline'"], // For styled-components
    'img-src' => ["'self'", "data:"],
    'connect-src' => ["'self'", "https://api.example.com"]
]);
```

### Alpine.js

```php
$headers->enableCSP([
    'default-src' => ["'self'"],
    'script-src' => ["'self'", "'nonce-" . $headers->getNonce() . "'"],
    'style-src' => ["'self'", "'unsafe-inline'"], // For Alpine style bindings
    'img-src' => ["'self'", "data:"],
    'connect-src' => ["'self'", "https://api.example.com"]
]);
```

---

## Production Environment Example for Laravel

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
            // Strict settings for production environment
            $this->headers->enableAllSecurityHeaders();
            $this->headers->enableHSTS(maxAge: 31536000, includeSubDomains: true, preload: true);
            
            // Strict CSP with nonce
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
            // More relaxed settings for development
            $this->headers->enableXFrameOptions('SAMEORIGIN');
            $this->headers->enableXContentTypeOptions();
            
            // CSP allowing dev tools
            $this->headers->enableCSP([
                'default-src' => ["'self'"],
                'script-src' => ["'self'", "'unsafe-eval'", "'unsafe-inline'"],
                'style-src' => ["'self'", "'unsafe-inline'", "https://fonts.googleapis.com"],
                'img-src' => ["'self'", "data:", "*"],
                'font-src' => ["'self'", "https://fonts.gstatic.com", "data:"],
                'connect-src' => ["'self'", "ws:", "wss:"] // For hot reload
            ]);
        }
        
        // If using Vite
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

Good luck! 🚀 