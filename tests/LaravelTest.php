<?php

namespace SecureHeaders\Tests;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PHPUnit\Framework\TestCase;
use SecureHeaders\SecureHeaders;

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
}
