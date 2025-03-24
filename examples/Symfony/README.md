# Symfony Integration for PHP Secure Headers

This directory contains example code for integrating PHP Secure Headers with Symfony 7 and 8.

## Installation

1. First, install the package via composer:

```bash
composer require shadi/php-secure-headers
```

2. Copy the `SecureHeadersSubscriber.php` file to your Symfony project's `src/EventSubscriber` directory or create it with the following content:

```php
<?php

namespace App\EventSubscriber;

use SecureHeaders\SecureHeaders;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Secure Headers Event Subscriber for Symfony
 * 
 * This subscriber is based on PHP Secure Headers by Shadi Ghorbani
 * @see https://github.com/shadighorbani7171/php-secure-headers
 */
class SecureHeadersSubscriber implements EventSubscriberInterface
{
    private SecureHeaders $headers;

    public function __construct()
    {
        $this->headers = new SecureHeaders();
        
        // Enable all security headers at once
        $this->headers->enableAllSecurityHeaders();
        
        // Or customize specific headers as needed:
        // $this->headers->enableHSTS(maxAge: 31536000, includeSubDomains: true, preload: true);
        // $this->headers->enableXFrameOptions('DENY');
        // $this->headers->enableXContentTypeOptions();
        // $this->headers->enableXXSSProtection();
        // $this->headers->enableReferrerPolicy();
        
        // Customize CSP if needed
        // $this->headers->enableCSP([
        //     'default-src' => ["'self'"],
        //     'script-src' => ["'self'", "https://trusted.com"],
        //     'style-src' => ["'self'", "'unsafe-inline'"],
        //     'img-src' => ["'self'", "data:", "https:"],
        //     'font-src' => ["'self'", "https://fonts.gstatic.com"],
        //     'connect-src' => ["'self'", "https://api.example.com"]
        // ]);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        // Only process main request, not sub-requests
        if (!$event->isMainRequest()) {
            return;
        }
        
        $response = $event->getResponse();
        
        // Apply all configured headers to the response
        foreach ($this->headers->getHeaders() as $name => $value) {
            $response->headers->set($name, $value);
        }
    }
}
```

3. Thanks to Symfony's autoconfiguration, the subscriber will be automatically registered. No additional configuration required!

## Using CSP Nonce in Twig Templates

If you're using Content Security Policy with nonces, you can access the nonce in your Twig templates:

1. First, create a Twig extension to expose the nonce:

```php
<?php

namespace App\Twig;

use App\EventSubscriber\SecureHeadersSubscriber;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SecureHeadersExtension extends AbstractExtension
{
    private SecureHeadersSubscriber $secureHeadersSubscriber;

    public function __construct(SecureHeadersSubscriber $secureHeadersSubscriber)
    {
        $this->secureHeadersSubscriber = $secureHeadersSubscriber;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('csp_nonce', [$this, 'getCspNonce']),
        ];
    }

    public function getCspNonce(): string
    {
        return $this->secureHeadersSubscriber->getNonce();
    }
}
```

2. Modify your `SecureHeadersSubscriber.php` to expose the nonce:

```php
// Add this method to the SecureHeadersSubscriber class
public function getNonce(): string
{
    return $this->headers->getNonce();
}
```

3. Use the nonce in your Twig templates:

```twig
<script nonce="{{ csp_nonce() }}">
    // Your JavaScript code
</script>

<style nonce="{{ csp_nonce() }}">
    /* Your CSS code */
</style>
```

## Custom Configuration

You can customize the security headers in the constructor of the subscriber:

```php
public function __construct()
{
    $this->headers = new SecureHeaders();
    
    // Enable only specific headers
    $this->headers->enableHSTS();
    $this->headers->enableXFrameOptions();
    
    // Or enable all headers at once
    // $this->headers->enableAllSecurityHeaders();
    
    // Customize CSP
    $this->headers->enableCSP([
        'default-src' => ["'self'"],
        'script-src' => ["'self'", "https://trusted.com"],
        'style-src' => ["'self'", "'unsafe-inline'"],
        'img-src' => ["'self'", "data:", "https:"],
    ]);
}
```

## Advanced Use Case: Environment-specific Configuration

You can inject the environment into your subscriber to configure different headers for different environments:

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
    private string $environment;

    public function __construct(string $environment)
    {
        $this->environment = $environment;
        $this->headers = new SecureHeaders();
        
        // Configure based on environment
        if ($this->environment === 'prod') {
            // Strict production settings
            $this->headers->enableAllSecurityHeaders();
            $this->headers->enableHSTS(maxAge: 31536000, includeSubDomains: true, preload: true);
        } else {
            // More relaxed development settings
            $this->headers->enableXFrameOptions('SAMEORIGIN');
            $this->headers->enableXContentTypeOptions();
            $this->headers->enableCSP([
                'default-src' => ["'self'"],
                'script-src' => ["'self'", "'unsafe-eval'", "'unsafe-inline'"], // Allow eval for dev tools
                'style-src' => ["'self'", "'unsafe-inline'"],
                'img-src' => ["'self'", "data:"],
            ]);
        }
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

Register this service with environment injection in `services.yaml`:

```yaml
services:
    App\EventSubscriber\SecureHeadersSubscriber:
        arguments:
            $environment: '%kernel.environment%'
```

## Attribution Required

When using this Symfony integration, please include the following attribution in your project's README:

```markdown
Symfony integration based on [PHP Secure Headers](https://github.com/shadighorbani7171/php-secure-headers) by [Shadi Ghorbani](https://github.com/shadighorbani7171).
``` 