# PHP Secure Headers Examples

This directory contains examples of how to integrate the PHP Secure Headers library with popular PHP frameworks.

## Laravel Integration

To use PHP Secure Headers with Laravel:

1. Copy `Laravel/SecureHeadersMiddleware.php` to your Laravel project's `app/Http/Middleware` directory.
2. Register the middleware in your `app/Http/Kernel.php`:

```php
protected $middleware = [
    // ...
    \App\Http\Middleware\SecureHeadersMiddleware::class,
];
```

## Symfony Integration

To use PHP Secure Headers with Symfony:

1. Copy `Symfony/SecureHeadersSubscriber.php` to your Symfony project's `src/EventSubscriber` directory.
2. The subscriber will be automatically registered thanks to Symfony's autoconfiguration.

## Customization

Both examples enable all security headers by default. You can customize the headers by modifying the middleware/subscriber:

```php
// Enable specific headers
$this->headers->enableHSTS();
$this->headers->enableCSP([
    'default-src' => ["'self'"],
    'script-src' => ["'self'", "https://trusted.com"],
]);
$this->headers->enableXFrameOptions('DENY');
// ... etc.
```

## Security Levels

The library supports two security levels: 'basic' and 'strict'. You can set the level in the constructor:

```php
// For basic security level
$this->headers = new SecureHeaders(SecureHeaders::LEVEL_BASIC);

// For strict security level (default)
$this->headers = new SecureHeaders(SecureHeaders::LEVEL_STRICT);
``` 