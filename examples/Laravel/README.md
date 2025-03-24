# Laravel Integration for PHP Secure Headers

This directory contains example code for integrating PHP Secure Headers with Laravel.

## Laravel 10 and Earlier

For Laravel 10 and earlier versions, copy the `SecureHeadersMiddleware.php` file to your Laravel project's `app/Http/Middleware` directory and register it in your `app/Http/Kernel.php`:

```php
protected $middleware = [
    // ...
    \App\Http\Middleware\SecureHeadersMiddleware::class,
];
```

## Laravel 11

For Laravel 11, copy the `SecureHeadersMiddleware.php` file to your Laravel project's `app/Http/Middleware` directory and register it in your `bootstrap/app.php` file:

```php
->withMiddleware(function (Middleware $middleware) {
    // Register the SecureHeaders middleware globally
    $middleware->append(\App\Http\Middleware\SecureHeadersMiddleware::class);
    
    // OR add it to the web group only
    // $middleware->appendToGroup('web', \App\Http\Middleware\SecureHeadersMiddleware::class);
})
```

Alternatively, you can use an invokable class to manage your middleware. Create a file in `app/Http/AppMiddleware.php`:

```php
<?php

namespace App\Http;

use Illuminate\Foundation\Configuration\Middleware;

class AppMiddleware
{
    public function __invoke(Middleware $middleware)
    {
        // Register the SecureHeaders middleware globally
        $middleware->append(\App\Http\Middleware\SecureHeadersMiddleware::class);
        
        // OR add it to the web group only
        // $middleware->appendToGroup('web', \App\Http\Middleware\SecureHeadersMiddleware::class);
    }
}
```

Then in your `bootstrap/app.php`:

```php
->withMiddleware(new \App\Http\AppMiddleware())
```

## Attribution Required

When using this Laravel integration, please include the following attribution in your project's README:

```markdown
Laravel integration based on [PHP Secure Headers](https://github.com/shadighorbani7171/php-secure-headers) by [Shadi Ghorbani](https://github.com/shadighorbani7171).
``` 