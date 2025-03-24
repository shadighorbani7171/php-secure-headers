# Symfony Integration for PHP Secure Headers

This directory contains example code for integrating PHP Secure Headers with Symfony 7 and 8.

## Installation

Copy the `SecureHeadersSubscriber.php` file to your Symfony project's `src/EventSubscriber` directory. The subscriber will be automatically registered thanks to Symfony's autoconfiguration.

## Event Subscriber

The event subscriber implements `EventSubscriberInterface` and listens to the `KernelEvents::RESPONSE` event. When a response is sent, the subscriber adds all configured security headers to the response.

```php
public function onKernelResponse(ResponseEvent $event): void
{
    $response = $event->getResponse();
    
    foreach ($this->headers->getHeaders() as $name => $value) {
        $response->headers->set($name, $value);
    }
}
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

## Attribution Required

When using this Symfony integration, please include the following attribution in your project's README:

```markdown
Symfony integration based on [PHP Secure Headers](https://github.com/shadighorbani7171/php-secure-headers) by [Shadi Ghorbani](https://github.com/shadighorbani7171).
``` 