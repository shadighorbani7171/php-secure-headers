<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use SecureHeaders\SecureHeaders;

/**
 * Secure Headers Middleware for Laravel
 * 
 * This middleware is based on PHP Secure Headers by Shadi Ghorbani
 * @see https://github.com/shadighorbani7171/php-secure-headers
 */
class SecureHeadersMiddleware
{
    private SecureHeaders $headers;

    public function __construct()
    {
        $this->headers = new SecureHeaders();
        $this->headers->enableAllSecurityHeaders();
    }

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        foreach ($this->headers->getHeaders() as $name => $value) {
            $response->header($name, $value);
        }
        
        return $response;
    }
} 