<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use SecureHeaders\SecureHeaders;

class SecureHeadersMiddleware
{
    private SecureHeaders $headers;

    public function __construct()
    {
        $this->headers = new SecureHeaders();
    }

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Enable all security headers
        $this->headers->enableAllSecurityHeaders();

        // Apply headers to response
        foreach ($this->headers->getHeaders() as $name => $value) {
            $response->headers->set($name, $value);
        }

        return $response;
    }
} 