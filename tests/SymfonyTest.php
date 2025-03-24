<?php

namespace SecureHeaders\Tests;

use PHPUnit\Framework\TestCase;
use SecureHeaders\SecureHeaders;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SymfonyTest extends TestCase
{
    private $headers;
    private $request;
    private $response;
    private $kernel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->headers = new SecureHeaders();
        $this->request = new Request();
        $this->response = new Response();
        $this->kernel = $this->createMock(HttpKernelInterface::class);
    }

    public function testSymfonyEventSubscriberIntegration(): void
    {
        // Simulate Symfony event subscriber
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

    public function testSymfonyResponseHeaders(): void
    {
        // Enable headers
        $this->headers->enableAllSecurityHeaders();

        // Apply headers to Symfony response
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

    public function testSymfonyCSPWithNonce(): void
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

    public function testSymfonyClientHintsPolicy(): void
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

    public function testSymfonyCriticalCH(): void
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

    public function testSymfonyEventSubscriber(): void
    {
        // Create event
        $event = new ResponseEvent(
            $this->kernel,
            $this->request,
            HttpKernelInterface::MAIN_REQUEST,
            $this->response
        );

        // Enable headers
        $this->headers->enableAllSecurityHeaders();

        // Apply headers to response
        foreach ($this->headers->getHeaders() as $name => $value) {
            $event->getResponse()->headers->set($name, $value);
        }

        // Test if headers are properly set in event response
        $this->assertTrue($event->getResponse()->headers->has('Strict-Transport-Security'));
        $this->assertTrue($event->getResponse()->headers->has('X-Frame-Options'));
        $this->assertTrue($event->getResponse()->headers->has('X-Content-Type-Options'));
        $this->assertTrue($event->getResponse()->headers->has('X-XSS-Protection'));
        $this->assertTrue($event->getResponse()->headers->has('Content-Security-Policy'));
        $this->assertTrue($event->getResponse()->headers->has('Permissions-Policy'));
    }

    public function testSymfony7And8Compatibility(): void
    {
        // Create event
        $event = new ResponseEvent(
            $this->kernel,
            $this->request,
            HttpKernelInterface::MAIN_REQUEST,
            $this->response
        );

        // Configure CSP with modern values for Symfony 7 and 8
        $this->headers->enableCSP([
            'default-src' => ["'self'"],
            'script-src' => ["'self'", "'wasm-unsafe-eval'", "'strict-dynamic'"],
            'style-src' => ["'self'"],
            'connect-src' => ["'self'", "https://api.example.com"],
            'img-src' => ["'self'", "data:"],
            'worker-src' => ["'self'"],
            'frame-src' => ["'self'"],
            'font-src' => ["'self'"],
        ]);

        // Apply headers to response
        foreach ($this->headers->getHeaders() as $name => $value) {
            $event->getResponse()->headers->set($name, $value);
        }

        // Test if modern CSP directives are properly set
        $cspHeader = $event->getResponse()->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("'wasm-unsafe-eval'", $cspHeader);
        $this->assertStringContainsString("'strict-dynamic'", $cspHeader);
        $this->assertStringContainsString("worker-src 'self'", $cspHeader);
    }
}
