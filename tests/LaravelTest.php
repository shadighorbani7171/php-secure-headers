<?php

namespace EasyShield\SecureHeaders\Tests;

use EasyShield\SecureHeaders\SecureHeaders;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PHPUnit\Framework\TestCase;

class LaravelTest extends TestCase
{
    private $headers;
    private $request;
    private $response;

    protected function setUp(): void
    {
        parent::setUp();
        $this->headers = new SecureHeaders();
        $this->request = new Request();
        $this->response = new Response();
    }

    public function testLaravelMiddlewareIntegration(): void
    {
        // Simulate Laravel middleware
        $this->headers->enableAllSecurityHeaders();

        // Get headers through reflection
        $reflection = new \ReflectionClass($this->headers);
        $property = $reflection->getProperty('headers');
        $property->setAccessible(true);
        $headers = $property->getValue($this->headers);

        // Test if headers are properly set
        $this->assertArrayHasKey('Strict-Transport-Security', $headers);
        $this->assertArrayHasKey('X-Frame-Options', $headers);
        $this->assertArrayHasKey('X-Content-Type-Options', $headers);
        $this->assertArrayHasKey('X-XSS-Protection', $headers);
        $this->assertArrayHasKey('Content-Security-Policy', $headers);
        $this->assertArrayHasKey('Permissions-Policy', $headers);
    }

    public function testLaravelResponseHeaders(): void
    {
        // Enable headers
        $this->headers->enableAllSecurityHeaders();

        // Apply headers to Laravel response
        foreach ($this->headers->getHeaders() as $name => $value) {
            $this->response->headers->set($name, $value);
        }

        // Test if headers are properly set in response
        $this->assertTrue($this->response->headers->has('Strict-Transport-Security'));
        $this->assertTrue($this->response->headers->has('X-Frame-Options'));
        $this->assertTrue($this->response->headers->has('X-Content-Type-Options'));
        $this->assertTrue($this->response->headers->has('X-XSS-Protection'));
        $this->assertTrue($this->response->headers->has('Content-Security-Policy'));
        $this->assertTrue($this->response->headers->has('Permissions-Policy'));
    }

    public function testLaravelCSPWithNonce(): void
    {
        // Enable CSP
        $this->headers->enableCSP();

        // Get nonce
        $nonce = $this->headers->getNonce();

        // Test if nonce is properly generated
        $this->assertNotNull($nonce);
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9+\/]+={0,2}$/', $nonce);

        // Test if nonce is included in CSP
        $headers = $this->headers->getHeaders();
        $this->assertStringContainsString("'nonce-{$nonce}'", $headers['Content-Security-Policy']);
    }

    public function testLaravelClientHintsPolicy(): void
    {
        // Enable Client Hints Policy
        $this->headers->enableClientHintsPolicy([
            'ch-ua-platform' => '*',
            'ch-ua' => 'self'
        ]);

        // Test if headers are properly set
        $headers = $this->headers->getHeaders();
        $this->assertArrayHasKey('Permissions-Policy', $headers);
        $this->assertStringContainsString('ch-ua-platform=*', $headers['Permissions-Policy']);
        $this->assertStringContainsString('ch-ua=self', $headers['Permissions-Policy']);
    }

    public function testLaravelCriticalCH(): void
    {
        // Enable Critical-CH
        $this->headers->enableCriticalCH([
            'Sec-CH-UA',
            'Sec-CH-UA-Platform',
            'Sec-CH-UA-Mobile'
        ]);

        // Test if headers are properly set
        $headers = $this->headers->getHeaders();
        $this->assertArrayHasKey('Critical-CH', $headers);
        $this->assertStringContainsString('Sec-CH-UA', $headers['Critical-CH']);
        $this->assertStringContainsString('Sec-CH-UA-Platform', $headers['Critical-CH']);
        $this->assertStringContainsString('Sec-CH-UA-Mobile', $headers['Critical-CH']);
    }

    public function testLaravel11MiddlewareIntegration(): void
    {
        // Enable headers
        $this->headers->enableAllSecurityHeaders();

        // Apply headers to response
        foreach ($this->headers->getHeaders() as $name => $value) {
            $this->response->headers->set($name, $value);
        }

        // Test if headers are properly set in response
        $this->assertTrue($this->response->headers->has('Strict-Transport-Security'));
        $this->assertTrue($this->response->headers->has('X-Frame-Options'));
        $this->assertTrue($this->response->headers->has('X-Content-Type-Options'));
        $this->assertTrue($this->response->headers->has('X-XSS-Protection'));
        $this->assertTrue($this->response->headers->has('Content-Security-Policy'));
        $this->assertTrue($this->response->headers->has('Permissions-Policy'));
    }

    public function testLaravel11MiddlewareWithNonce(): void
    {
        // Enable headers with CSP nonce
        $this->headers->enableAllSecurityHeaders();
        $nonce = $this->headers->getNonce();

        // Apply headers to response
        foreach ($this->headers->getHeaders() as $name => $value) {
            $this->response->headers->set($name, $value);
        }

        // Test if CSP header contains nonce
        $cspHeader = $this->response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("'nonce-{$nonce}'", $cspHeader);
    }

    public function testLaravel11MiddlewareGroupIntegration(): void
    {
        // Enable headers
        $this->headers->enableAllSecurityHeaders();

        // Apply headers to response
        foreach ($this->headers->getHeaders() as $name => $value) {
            $this->response->headers->set($name, $value);
        }

        // Test if headers are properly set in response
        $this->assertTrue($this->response->headers->has('Strict-Transport-Security'));
        $this->assertTrue($this->response->headers->has('X-Frame-Options'));
        $this->assertTrue($this->response->headers->has('X-Content-Type-Options'));
        $this->assertTrue($this->response->headers->has('X-XSS-Protection'));
        $this->assertTrue($this->response->headers->has('Content-Security-Policy'));
        $this->assertTrue($this->response->headers->has('Permissions-Policy'));
    }
    
    public function testLaravelWithCSPBuilder(): void
    {
        // Configure CSP using the fluent builder
        $this->headers->csp()
            ->allowScripts('https://cdn.example.com')
            ->allowStyles('https://fonts.googleapis.com')
            ->allowImages('https://images.example.com')
            ->blockFrames()
            ->useStrictDynamic();
            
        // Get CSP directives and enable CSP
        $cspPolicies = $this->headers->csp()->getDirectives();
        $this->headers->enableCSP($cspPolicies);
        
        // Apply headers to Laravel response
        foreach ($this->headers->getHeaders() as $name => $value) {
            $this->response->headers->set($name, $value);
        }
        
        // Test if CSP header is properly set with expected values
        $cspHeader = $this->response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString('script-src', $cspHeader);
        $this->assertStringContainsString('https://cdn.example.com', $cspHeader);
        $this->assertStringContainsString('style-src', $cspHeader);
        $this->assertStringContainsString('https://fonts.googleapis.com', $cspHeader);
        $this->assertStringContainsString('img-src', $cspHeader);
        $this->assertStringContainsString('https://images.example.com', $cspHeader);
        $this->assertStringContainsString('frame-ancestors', $cspHeader);
        $this->assertStringContainsString("'none'", $cspHeader);
        $this->assertStringContainsString("'strict-dynamic'", $cspHeader);
    }
    
    public function testLaravelWithCSPBuilderHTMLDetection(): void
    {
        // Sample HTML with external resources
        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.example.com/styles.css">
</head>
<body>
    <img src="https://example.com/image.jpg">
</body>
</html>
HTML;

        // Use CSP Builder to detect external resources
        $this->headers->csp()->detectExternalResourcesFromHtml($html);
        
        // Enable CSP with detected resources
        $cspPolicies = $this->headers->csp()->getDirectives();
        $this->headers->enableCSP($cspPolicies);
        
        // Apply headers to Laravel response
        foreach ($this->headers->getHeaders() as $name => $value) {
            $this->response->headers->set($name, $value);
        }
        
        // Test if CSP header contains the detected resources
        $cspHeader = $this->response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString('https://code.jquery.com', $cspHeader);
        $this->assertStringContainsString('https://cdn.example.com', $cspHeader);
        $this->assertStringContainsString('https://example.com', $cspHeader);
    }
}
