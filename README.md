# PHP Secure Headers

A simple yet powerful PHP library for managing security headers in web applications.

## ✨ Features

- 🔒 Easy configuration of security headers
- 🛡️ CSP support with nonce capability
- 🎯 Secure default settings
- 🔄 Complete customization capabilities
- 📚 Comprehensive documentation with practical examples
- 🎚️ Support for different security levels (basic and strict)
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

## 📥 Installation

```bash
composer require shadi/php-secure-headers
```

## 🚀 Quick Start

```php
use SecureHeaders\SecureHeaders;

// Create instance with strict security level (default)
$headers = new SecureHeaders();

// Or with basic security level
$headers = new SecureHeaders(SecureHeaders::LEVEL_BASIC);

// Enable all security headers with one command
$headers->enableAllSecurityHeaders();

// Apply headers
$headers->apply();
```

## 🔐 Security Levels

The library supports two security levels:

### Basic
Suitable for regular websites and development environments:
```php
$headers = new SecureHeaders(SecureHeaders::LEVEL_BASIC);
```
- CSP with simpler rules
- Allows `unsafe-inline` for styles
- Limited access to camera and microphone
- X-Frame-Options: SAMEORIGIN

### Strict
Suitable for sensitive applications and production environments:
```php
$headers = new SecureHeaders(SecureHeaders::LEVEL_STRICT);
```
- CSP with stricter rules
- Uses nonce for scripts
- Disables all sensitive permissions
- X-Frame-Options: DENY
- Automatic upgrade to HTTPS

## 🔍 Complete Example

```php
<?php
use SecureHeaders\SecureHeaders;

// Create instance with strict security level
$headers = new SecureHeaders(SecureHeaders::LEVEL_STRICT);

// Enable all headers with one command
$headers->enableAllSecurityHeaders();

// Or enable each header separately
$headers->enableHSTS();
$headers->enableXFrameOptions();
$headers->enableXContentTypeOptions();
$headers->enableXXSSProtection();
$headers->enableReferrerPolicy();
$headers->enablePermissionsPolicy();

// Configure CSP with custom rules
$headers->enableCSP([
    'script-src' => ["'self'", "https://trusted-cdn.com"],
    'style-src' => ["'self'", "https://fonts.googleapis.com"],
]);

// Enable Client Hints Policy for browser information sharing
$headers->enableClientHintsPolicy([
    'ch-ua-platform' => '*',
    'ch-ua' => 'self'
]);

// Enable Critical-CH to request browser information
$headers->enableCriticalCH(['Sec-CH-UA', 'Sec-CH-UA-Platform', 'Sec-CH-UA-Mobile']);

// Apply all headers
$headers->apply();

// Get nonce for inline scripts
$nonce = $headers->getNonce();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Secure Example</title>
</head>
<body>
    <script nonce="<?= htmlspecialchars($nonce) ?>">
        console.log('Secure script with nonce');
    </script>
</body>
</html>
```

## 📝 Client Hints Usage Guide

### Client Hints Policy
For precise control over what information the browser shares with third-party sites, you can use Client Hints Policy:

```php
// Allow access to platform info for all sites and
// browser info only to our domain
$headers->enableClientHintsPolicy([
    'ch-ua-platform' => '*',
    'ch-ua' => 'self'
]);
```

Client Hints Delegation allows browsers to share certain information like operating system, device model, or browser type with servers or third parties in a controlled manner. This enhances security and privacy by ensuring only authorized resources can access this information.

The `enableClientHintsPolicy` method integrates with the Permissions Policy header to control which hints are delegated and to which origins. This ensures that sensitive client information is only shared with trusted sources.

### Critical-CH
To request specific information from the user's browser:

```php
// Request specific information from the user's browser
$headers->enableCriticalCH([
    'Sec-CH-UA',              // Browser info
    'Sec-CH-UA-Platform',     // Operating system
    'Sec-CH-UA-Mobile'        // Mobile status
]);
```

The Critical-CH header indicates to the browser which Client Hints are critical for the proper functioning of your site. This ensures that important browser information is sent on the first request, improving user experience and site performance.

By default, the library configures the following critical hints:
- `Sec-CH-UA`: Browser brand and version information
- `Sec-CH-UA-Mobile`: Whether the device is mobile
- `Sec-CH-UA-Platform`: Operating system platform

## 🛡️ Security Best Practices

When using this library, consider the following best practices:

1. **Use the strict security level in production**
   - The strict level provides the most secure settings for production environments

2. **Generate and use nonce for inline scripts**
   - Always use the generated nonce for any inline scripts to ensure CSP compliance

3. **Limit Client Hints delegation**
   - Only delegate client information to trusted domains

4. **Regularly update your security policies**
   - Security standards evolve; keep your headers up to date

5. **Test your headers**
   - Use tools like [Security Headers](https://securityheaders.com/) to validate your configuration

## 🤝 Contributing

We welcome your contributions! Please:

1. Fork the repository
2. Create a new branch
3. Make your changes
4. Submit a Pull Request