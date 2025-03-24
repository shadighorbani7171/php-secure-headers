# PHP Secure Headers

[![Latest Version on Packagist](https://img.shields.io/packagist/v/shadi/php-secure-headers.svg?style=flat-square)](https://packagist.org/packages/shadi/php-secure-headers)
[![Tests](https://github.com/shadighorbani7171/php-secure-headers/actions/workflows/tests.yml/badge.svg)](https://github.com/shadighorbani7171/php-secure-headers/actions/workflows/tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/shadi/php-secure-headers.svg?style=flat-square)](https://packagist.org/packages/shadi/php-secure-headers)

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
composer require shadi/php-secure-headers
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
> ```markdown
> Laravel integration based on [PHP Secure Headers](https://github.com/shadighorbani7171/php-secure-headers) by [Shadi Ghorbani](https://github.com/shadighorbani7171).
> ```

### Symfony 7/8

1. Copy the subscriber from `examples/Symfony/SecureHeadersSubscriber.php` to your Symfony project's `src/EventSubscriber` directory.
2. The subscriber will be automatically registered thanks to Symfony's autoconfiguration.

For detailed instructions and custom configurations, see `examples/Symfony/README.md`.

> **Note**: When using the Symfony integration, please include the following attribution in your project's README:
> ```markdown
> Symfony integration based on [PHP Secure Headers](https://github.com/shadighorbani7171/php-secure-headers) by [Shadi Ghorbani](https://github.com/shadighorbani7171).
> ```

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
- Allows 'unsafe-inline' for styles
- Less restrictive CSP
- Basic permissions policy

### Strict Level (Default)
- No 'unsafe-inline'
- Strict CSP with nonce
- Comprehensive permissions policy
- Enforces upgrade-insecure-requests

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

- [Open an issue](https://github.com/shadighorbani7171/php-secure-headers/issues/new) on GitHub
- Share how you're using the library
- Suggest improvements or new features

See [CONTRIBUTING.md](CONTRIBUTING.md) for more information on how to contribute.

## Security

If you discover any security related issues, please email shadighorbani7171@gmail.com instead of using the issue tracker.

## Credits

- [Shadi Ghorbani](https://github.com/shadighorbani7171)
- [All Contributors](../../contributors)

## License

The MIT License (MIT) with additional attribution requirements for frameworks and major projects. Please see [License File](LICENSE.md) for more information.
