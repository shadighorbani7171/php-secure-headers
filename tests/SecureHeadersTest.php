<?php

namespace EasyShield\SecureHeaders\Tests;

use EasyShield\SecureHeaders\SecureHeaders;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

/**
 * @covers \EasyShield\SecureHeaders\SecureHeaders
 */
class SecureHeadersTest extends TestCase
{
    private SecureHeaders $headers;

    protected function setUp(): void
    {
        $this->headers = new SecureHeaders();
    }

    public function testSecureHeadersClassStructure(): void
    {
        $reflection = new ReflectionClass(SecureHeaders::class);

        // Test class exists and is instantiable
        $this->assertTrue(class_exists(SecureHeaders::class));
        $this->assertFalse($reflection->isAbstract());
        $this->assertFalse($reflection->isInterface());

        // Test properties exist with correct visibility and types
        $this->assertTrue($reflection->hasProperty('headers'));
        $this->assertTrue($reflection->hasProperty('nonce'));
        $this->assertTrue($reflection->hasProperty('securityLevel'));

        $headersProperty = $reflection->getProperty('headers');
        $nonceProperty = $reflection->getProperty('nonce');
        $securityLevelProperty = $reflection->getProperty('securityLevel');

        $this->assertTrue($headersProperty->isProtected());
        $this->assertTrue($nonceProperty->isProtected());
        $this->assertTrue($securityLevelProperty->isProtected());

        // Test property types
        $this->assertSame('array', $headersProperty->getType()->getName());
        $this->assertTrue($nonceProperty->getType()->allowsNull());
        $this->assertSame('string', $securityLevelProperty->getType()->getName());

        // Test constants exist with correct values
        $this->assertTrue($reflection->hasConstant('LEVEL_BASIC'));
        $this->assertTrue($reflection->hasConstant('LEVEL_STRICT'));
        $this->assertSame('basic', $reflection->getConstant('LEVEL_BASIC'));
        $this->assertSame('strict', $reflection->getConstant('LEVEL_STRICT'));

        // Test initial state of properties
        $headers = new SecureHeaders();
        $this->assertEmpty($this->getPrivateProperty($headers, 'headers'));
        $this->assertNull($this->getPrivateProperty($headers, 'nonce'));
        $this->assertSame('strict', $this->getPrivateProperty($headers, 'securityLevel'));
    }

    public function testClassCanBeInstantiated(): void
    {
        $headers = new SecureHeaders();
        $this->assertInstanceOf(SecureHeaders::class, $headers);
        $this->assertSame('strict', $this->getPrivateProperty($headers, 'securityLevel'));
        $this->assertEmpty($this->getPrivateProperty($headers, 'headers'));
        $this->assertNull($this->getPrivateProperty($headers, 'nonce'));
    }

    public function testConstructorWithDefaultValue(): void
    {
        $headers = new SecureHeaders();
        $this->assertSame('strict', $this->getPrivateProperty($headers, 'securityLevel'));
        $this->assertEmpty($this->getPrivateProperty($headers, 'headers'));
        $this->assertNull($this->getPrivateProperty($headers, 'nonce'));
    }

    public function testConstructorWithBasicLevel(): void
    {
        $headers = new SecureHeaders(SecureHeaders::LEVEL_BASIC);
        $this->assertSame('basic', $this->getPrivateProperty($headers, 'securityLevel'));
        $this->assertEmpty($this->getPrivateProperty($headers, 'headers'));
        $this->assertNull($this->getPrivateProperty($headers, 'nonce'));
    }

    public function testConstructorWithStrictLevel(): void
    {
        $headers = new SecureHeaders(SecureHeaders::LEVEL_STRICT);
        $this->assertSame('strict', $this->getPrivateProperty($headers, 'securityLevel'));
        $this->assertEmpty($this->getPrivateProperty($headers, 'headers'));
        $this->assertNull($this->getPrivateProperty($headers, 'nonce'));
    }

    public function testDefaultSecurityLevelIsStrict(): void
    {
        $this->assertSame('strict', $this->getPrivateProperty($this->headers, 'securityLevel'));
    }

    public function testSetSecurityLevelWithValidValues(): void
    {
        // Test setting to basic level
        $this->headers->setSecurityLevel(SecureHeaders::LEVEL_BASIC);
        $this->assertSame('basic', $this->getPrivateProperty($this->headers, 'securityLevel'));

        // Test setting back to strict level
        $this->headers->setSecurityLevel(SecureHeaders::LEVEL_STRICT);
        $this->assertSame('strict', $this->getPrivateProperty($this->headers, 'securityLevel'));

        // Test that headers are cleared when security level changes
        $headers = $this->getPrivateProperty($this->headers, 'headers');
        $this->assertEmpty($headers);
    }

    public function testSetSecurityLevelWithInvalidValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid security level: invalid_level');
        $this->headers->setSecurityLevel('invalid_level');
    }

    public function testEnableHSTS(): void
    {
        $this->headers->enableHSTS();

        $headers = $this->getPrivateProperty($this->headers, 'headers');
        $this->assertArrayHasKey('Strict-Transport-Security', $headers);
        $this->assertSame('max-age=31536000; includeSubDomains', $headers['Strict-Transport-Security']);
    }

    public function testEnableHSTSWithCustomValues(): void
    {
        $this->headers->enableHSTS(maxAge: 3600, includeSubDomains: false, preload: true);

        $headers = $this->getPrivateProperty($this->headers, 'headers');
        $this->assertArrayHasKey('Strict-Transport-Security', $headers);
        $this->assertSame('max-age=3600; preload', $headers['Strict-Transport-Security']);
    }

    public function testEnableCSPGeneratesNonce(): void
    {
        $this->headers->enableCSP();

        $nonce = $this->headers->getNonce();
        $this->assertNotNull($nonce);
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9+\/]+={0,2}$/', $nonce);
    }

    public function testStrictLevelCSPHasStrictDirectives(): void
    {
        $this->headers->enableCSP();

        $headers = $this->getPrivateProperty($this->headers, 'headers');
        $this->assertArrayHasKey('Content-Security-Policy', $headers);

        $csp = $headers['Content-Security-Policy'];
        $this->assertStringContainsString("'strict-dynamic'", $csp);
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);
    }

    public function testBasicLevelCSPAllowsUnsafeInline(): void
    {
        $headers = new SecureHeaders(SecureHeaders::LEVEL_BASIC);
        $headers->enableCSP();

        $headersArray = $this->getPrivateProperty($headers, 'headers');
        $csp = $headersArray['Content-Security-Policy'];

        $this->assertStringContainsString("style-src 'self' 'unsafe-inline'", $csp);
        $this->assertStringNotContainsString("'strict-dynamic'", $csp);
    }

    public function testEnableAllSecurityHeaders(): void
    {
        $this->headers->enableAllSecurityHeaders();

        $headers = $this->getPrivateProperty($this->headers, 'headers');

        $expectedHeaders = [
            'Strict-Transport-Security',
            'X-Frame-Options',
            'X-Content-Type-Options',
            'X-XSS-Protection',
            'Referrer-Policy',
            'Permissions-Policy',
            'Content-Security-Policy'
        ];

        foreach ($expectedHeaders as $header) {
            $this->assertArrayHasKey($header, $headers);
        }
    }

    public function testInvalidSecurityLevel(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid security level: invalid');
        new SecureHeaders('invalid');
    }

    public function testInvalidXFrameOptions(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid X-Frame-Options value: INVALID');
        $this->headers->enableXFrameOptions('INVALID');
    }

    public function testInvalidReferrerPolicy(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Referrer-Policy value: invalid');
        $this->headers->enableReferrerPolicy('invalid');
    }

    public function testCustomPermissionsPolicy(): void
    {
        $policies = [
            'camera' => ["'self'"],
            'microphone' => [],
            'payment' => ["https://payment.example.com"]
        ];

        $this->headers->enablePermissionsPolicy($policies);

        $headers = $this->getPrivateProperty($this->headers, 'headers');
        $this->assertArrayHasKey('Permissions-Policy', $headers);

        $policy = $headers['Permissions-Policy'];
        $this->assertStringContainsString("camera=('self')", $policy);
        $this->assertStringContainsString("microphone=()", $policy);
        $this->assertStringContainsString("payment=(https://payment.example.com)", $policy);
    }

    public function testApplySendsAllConfiguredHeaders(): void
    {
        // Configure multiple headers
        $this->headers->enableHSTS(maxAge: 3600);
        $this->headers->enableXFrameOptions('DENY');
        $this->headers->enableXContentTypeOptions();
        $this->headers->enableXXSSProtection();
        $this->headers->enableReferrerPolicy();
        $this->headers->enableCSP();
        $this->headers->enablePermissionsPolicy();

        // Capture and send headers
        ob_start();
        $this->headers->apply();
        ob_end_clean();

        // Get all sent headers
        $headers = xdebug_get_headers();

        // Verify each header was sent
        $this->assertContains('Strict-Transport-Security: max-age=3600; includeSubDomains', $headers);
        $this->assertContains('X-Frame-Options: DENY', $headers);
        $this->assertContains('X-Content-Type-Options: nosniff', $headers);
        $this->assertContains('X-XSS-Protection: 1; mode=block', $headers);
        $this->assertContains('Referrer-Policy: strict-origin-when-cross-origin', $headers);

        // Verify CSP header exists and contains essential directives
        $cspHeader = $this->findHeader($headers, 'Content-Security-Policy');
        $this->assertNotNull($cspHeader, 'CSP header not found');
        $this->assertStringContainsString("default-src 'self'", $cspHeader);
        $this->assertStringContainsString("frame-ancestors 'none'", $cspHeader);

        // Verify Permissions Policy header exists
        $permissionsHeader = $this->findHeader($headers, 'Permissions-Policy');
        $this->assertNotNull($permissionsHeader, 'Permissions-Policy header not found');
        $this->assertStringContainsString('camera=()', $permissionsHeader);
    }

    /**
     * Helper method to find a specific header in the headers array
     */
    private function findHeader(array $headers, string $name): ?string
    {
        foreach ($headers as $header) {
            if (strpos($header, $name . ':') === 0) {
                return $header;
            }
        }
        return null;
    }

    public function testCSPWithCustomPolicies(): void
    {
        $customPolicies = [
            'script-src' => ["'self'", "https://trusted.com"],
            'style-src' => ["'self'", "https://fonts.googleapis.com"],
            'img-src' => ["'self'", "data:", "https:"],
            'connect-src' => ["'self'", "https://api.example.com"]
        ];

        $this->headers->enableCSP($customPolicies);

        $headers = $this->getPrivateProperty($this->headers, 'headers');
        $csp = $headers['Content-Security-Policy'];

        foreach ($customPolicies as $directive => $sources) {
            foreach ($sources as $source) {
                $this->assertStringContainsString($source, $csp);
            }
        }
    }

    public function testBuildCSPString(): void
    {
        $policies = [
            'default-src' => ["'self'"],
            'script-src' => ["'self'", "'nonce-123'", "'strict-dynamic'"],
            'style-src' => ["'self'", "'unsafe-inline'"],
            'empty-directive' => [],
        ];

        $result = $this->invokePrivateMethod($this->headers, 'buildCSPString', [$policies]);

        $this->assertStringContainsString("default-src 'self'", $result);
        $this->assertStringContainsString("script-src 'self' 'nonce-123' 'strict-dynamic'", $result);
        $this->assertStringContainsString("style-src 'self' 'unsafe-inline'", $result);
        $this->assertStringNotContainsString('empty-directive', $result);
        $this->assertStringContainsString(';', $result);
    }

    public function testBuildCSPStringWithEmptyValues(): void
    {
        $policies = [
            'default-src' => ["'self'"],
            'script-src' => [],
            'style-src' => ["'self'"],
            'empty-directive' => []
        ];

        $result = $this->invokePrivateMethod($this->headers, 'buildCSPString', [$policies]);

        $this->assertStringContainsString("default-src 'self'", $result);
        $this->assertStringContainsString("style-src 'self'", $result);
        $this->assertStringNotContainsString('script-src', $result);
        $this->assertStringNotContainsString('empty-directive', $result);
    }

    public function testEnableCSPWithoutScriptSrc(): void
    {
        $policies = [
            'default-src' => ["'self'"],
            'style-src' => ["'self'"]
        ];

        $this->headers->enableCSP($policies);

        $headers = $this->getPrivateProperty($this->headers, 'headers');
        $cspHeader = $headers['Content-Security-Policy'];

        $this->assertStringContainsString("script-src 'self'", $cspHeader);
        $this->assertStringContainsString("'nonce-", $cspHeader);
        if ($this->getPrivateProperty($this->headers, 'securityLevel') === 'strict') {
            $this->assertStringContainsString("'strict-dynamic'", $cspHeader);
        }
    }

    public function testGenerateNonceProducesValidBase64(): void
    {
        $this->invokePrivateMethod($this->headers, 'generateNonce', []);
        $nonce = $this->getPrivateProperty($this->headers, 'nonce');

        $this->assertNotNull($nonce);
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9+\/]+={0,2}$/', $nonce);
        $this->assertEquals(24, strlen($nonce)); // base64_encode(random_bytes(16)) always produces 24 characters
    }

    public function testDefaultCSPPoliciesInStrictMode(): void
    {
        $policies = $this->invokePrivateMethod($this->headers, 'getDefaultCSPPolicies', []);

        $this->assertArrayHasKey('default-src', $policies);
        $this->assertArrayHasKey('script-src', $policies);
        $this->assertArrayHasKey('style-src', $policies);
        $this->assertArrayHasKey('img-src', $policies);
        $this->assertArrayHasKey('font-src', $policies);
        $this->assertArrayHasKey('connect-src', $policies);
        $this->assertArrayHasKey('form-action', $policies);
        $this->assertArrayHasKey('frame-ancestors', $policies);
        $this->assertArrayHasKey('base-uri', $policies);
        $this->assertArrayHasKey('upgrade-insecure-requests', $policies);

        $this->assertContains("'self'", $policies['default-src']);
        $this->assertContains("'none'", $policies['frame-ancestors']);
        $this->assertEmpty($policies['upgrade-insecure-requests']);
    }

    public function testDefaultCSPPoliciesInBasicMode(): void
    {
        $headers = new SecureHeaders(SecureHeaders::LEVEL_BASIC);
        $policies = $this->invokePrivateMethod($headers, 'getDefaultCSPPolicies', []);

        $this->assertArrayHasKey('default-src', $policies);
        $this->assertArrayHasKey('script-src', $policies);
        $this->assertArrayHasKey('style-src', $policies);
        $this->assertArrayHasKey('img-src', $policies);
        $this->assertArrayHasKey('font-src', $policies);
        $this->assertArrayHasKey('connect-src', $policies);

        $this->assertContains("'self'", $policies['default-src']);
        $this->assertContains("'unsafe-inline'", $policies['style-src']);
        $this->assertContains("https:", $policies['img-src']);
        $this->assertContains("https:", $policies['font-src']);
    }

    public function testDefaultPermissionsPoliciesInStrictMode(): void
    {
        $policies = $this->invokePrivateMethod($this->headers, 'getDefaultPermissionsPolicies', []);

        $this->assertArrayHasKey('accelerometer', $policies);
        $this->assertArrayHasKey('camera', $policies);
        $this->assertArrayHasKey('geolocation', $policies);
        $this->assertArrayHasKey('gyroscope', $policies);
        $this->assertArrayHasKey('magnetometer', $policies);
        $this->assertArrayHasKey('microphone', $policies);
        $this->assertArrayHasKey('payment', $policies);
        $this->assertArrayHasKey('usb', $policies);

        foreach ($policies as $feature => $allowlist) {
            $this->assertEmpty($allowlist, "Feature '$feature' should have empty allowlist in strict mode");
        }
    }

    public function testDefaultPermissionsPoliciesInBasicMode(): void
    {
        $headers = new SecureHeaders(SecureHeaders::LEVEL_BASIC);
        $policies = $this->invokePrivateMethod($headers, 'getDefaultPermissionsPolicies', []);

        $this->assertArrayHasKey('camera', $policies);
        $this->assertArrayHasKey('microphone', $policies);
        $this->assertArrayHasKey('geolocation', $policies);

        foreach ($policies as $feature => $allowlist) {
            $this->assertContains("'self'", $allowlist, "Feature '$feature' should allow 'self' in basic mode");
        }
    }

    public function testEnableCSPWithoutScriptSrcAndCustomPolicies(): void
    {
        $headers = new SecureHeaders();
        $customPolicies = [
            'img-src' => ["'self'", "https://example.com"],
            'style-src' => ["'self'", "'unsafe-inline'"]
        ];

        $headers->enableCSP($customPolicies);
        $nonce = $headers->getNonce();

        $this->assertNotNull($nonce);
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9+\/]+={0,2}$/', $nonce);

        $headersProperty = new ReflectionProperty(SecureHeaders::class, 'headers');
        $headersProperty->setAccessible(true);
        $headerValues = $headersProperty->getValue($headers);

        $this->assertArrayHasKey('Content-Security-Policy', $headerValues);
        $cspHeader = $headerValues['Content-Security-Policy'];

        // Check that script-src was added with nonce and strict-dynamic
        $this->assertStringContainsString("script-src 'self' 'nonce-{$nonce}' 'strict-dynamic'", $cspHeader);

        // Check that custom policies were merged correctly
        $this->assertStringContainsString("img-src 'self' data: https://example.com", $cspHeader);
        $this->assertStringContainsString("style-src 'self' 'unsafe-inline'", $cspHeader);
    }

    public function testEnableCSPWithExistingNonce(): void
    {
        // First call to set nonce
        $this->headers->enableCSP();
        $firstNonce = $this->headers->getNonce();

        // Second call should reuse the same nonce
        $this->headers->enableCSP();
        $secondNonce = $this->headers->getNonce();

        $this->assertSame($firstNonce, $secondNonce);
    }

    public function testEnableCriticalCH(): void
    {
        $this->headers->enableCriticalCH(['Sec-CH-UA', 'Sec-CH-UA-Platform']);
        $reflection = new \ReflectionClass($this->headers);
        $property = $reflection->getProperty('headers');
        $property->setAccessible(true);
        $headers = $property->getValue($this->headers);

        $this->assertArrayHasKey('Critical-CH', $headers);
        $this->assertSame('Sec-CH-UA, Sec-CH-UA-Platform', $headers['Critical-CH']);
    }

    public function testEnableCriticalCHWithDefaultHints(): void
    {
        $this->headers->enableCriticalCH();
        $reflection = new \ReflectionClass($this->headers);
        $property = $reflection->getProperty('headers');
        $property->setAccessible(true);
        $headers = $property->getValue($this->headers);

        $this->assertArrayHasKey('Critical-CH', $headers);
        $this->assertSame('Sec-CH-UA, Sec-CH-UA-Mobile, Sec-CH-UA-Platform', $headers['Critical-CH']);
    }

    public function testEnableClientHintsPolicy(): void
    {
        $hints = [
            'ch-ua-platform' => '*',
            'ch-ua' => 'self'
        ];

        $this->headers->enableClientHintsPolicy($hints);
        $reflection = new \ReflectionClass($this->headers);
        $property = $reflection->getProperty('headers');
        $property->setAccessible(true);
        $headers = $property->getValue($this->headers);

        $this->assertArrayHasKey('Permissions-Policy', $headers);
        $this->assertStringContainsString('ch-ua-platform=*', $headers['Permissions-Policy']);
        $this->assertStringContainsString('ch-ua=self', $headers['Permissions-Policy']);
    }

    public function testEnableClientHintsPolicyWithExistingPolicy(): void
    {
        // First set some existing permissions
        $this->headers->enablePermissionsPolicy([
            'camera' => ["'self'"],
            'microphone' => ["'self'"]
        ]);

        // Then add client hints
        $hints = [
            'ch-ua-platform' => '*',
            'ch-ua' => 'self'
        ];

        $this->headers->enableClientHintsPolicy($hints);
        $reflection = new \ReflectionClass($this->headers);
        $property = $reflection->getProperty('headers');
        $property->setAccessible(true);
        $headers = $property->getValue($this->headers);

        $this->assertArrayHasKey('Permissions-Policy', $headers);
        $this->assertStringContainsString("camera=('self')", $headers['Permissions-Policy']);
        $this->assertStringContainsString("microphone=('self')", $headers['Permissions-Policy']);
        $this->assertStringContainsString('ch-ua-platform=*', $headers['Permissions-Policy']);
        $this->assertStringContainsString('ch-ua=self', $headers['Permissions-Policy']);
    }

    public function testEnableClientHintsPolicyWithEmptyHints(): void
    {
        $this->headers->enableClientHintsPolicy([]);
        $reflection = new \ReflectionClass($this->headers);
        $property = $reflection->getProperty('headers');
        $property->setAccessible(true);
        $headers = $property->getValue($this->headers);

        $this->assertArrayNotHasKey('Permissions-Policy', $headers);
    }

    /**
     * Helper method to access private properties
     */
    private function getPrivateProperty(object $object, string $property): mixed
    {
        $reflection = new \ReflectionClass($object);
        $prop = $reflection->getProperty($property);
        $prop->setAccessible(true);
        return $prop->getValue($object);
    }

    /**
     * Helper method to invoke private methods
     */
    private function invokePrivateMethod(object $object, string $methodName, array $parameters = []): mixed
    {
        $reflection = new \ReflectionClass($object);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }

    public function testSecurityHeadersIntegration(): void
    {
        // Enable all security headers
        $this->headers->enableAllSecurityHeaders();

        // Get headers through reflection
        $reflection = new \ReflectionClass($this->headers);
        $property = $reflection->getProperty('headers');
        $property->setAccessible(true);
        $headers = $property->getValue($this->headers);

        // Test HSTS header
        $this->assertArrayHasKey('Strict-Transport-Security', $headers);
        $this->assertStringContainsString('max-age=31536000', $headers['Strict-Transport-Security']);
        $this->assertStringContainsString('includeSubDomains', $headers['Strict-Transport-Security']);

        // Test X-Frame-Options header
        $this->assertArrayHasKey('X-Frame-Options', $headers);
        $this->assertSame('DENY', $headers['X-Frame-Options']);

        // Test X-Content-Type-Options header
        $this->assertArrayHasKey('X-Content-Type-Options', $headers);
        $this->assertSame('nosniff', $headers['X-Content-Type-Options']);

        // Test X-XSS-Protection header
        $this->assertArrayHasKey('X-XSS-Protection', $headers);
        $this->assertSame('1; mode=block', $headers['X-XSS-Protection']);

        // Test Referrer-Policy header
        $this->assertArrayHasKey('Referrer-Policy', $headers);
        $this->assertSame('strict-origin-when-cross-origin', $headers['Referrer-Policy']);

        // Test Content-Security-Policy header
        $this->assertArrayHasKey('Content-Security-Policy', $headers);
        $csp = $headers['Content-Security-Policy'];
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("script-src 'self'", $csp);
        $this->assertStringContainsString("'nonce-", $csp);
        $this->assertStringContainsString("'strict-dynamic'", $csp);

        // Test Permissions-Policy header
        $this->assertArrayHasKey('Permissions-Policy', $headers);
        $permissions = $headers['Permissions-Policy'];
        $this->assertStringContainsString('camera=()', $permissions);
        $this->assertStringContainsString('microphone=()', $permissions);
        $this->assertStringContainsString('geolocation=()', $permissions);
    }

    public function testSecurityHeadersInBasicMode(): void
    {
        $headers = new SecureHeaders(SecureHeaders::LEVEL_BASIC);
        $headers->enableAllSecurityHeaders();

        // Get headers through reflection
        $reflection = new \ReflectionClass($headers);
        $property = $reflection->getProperty('headers');
        $property->setAccessible(true);
        $headerValues = $property->getValue($headers);

        // Test CSP header in basic mode
        $this->assertArrayHasKey('Content-Security-Policy', $headerValues);
        $csp = $headerValues['Content-Security-Policy'];
        $this->assertStringContainsString("'unsafe-inline'", $csp);
        $this->assertStringNotContainsString("'strict-dynamic'", $csp);

        // Test Permissions-Policy header in basic mode
        $this->assertArrayHasKey('Permissions-Policy', $headerValues);
        $permissions = $headerValues['Permissions-Policy'];
        $this->assertStringContainsString("camera=('self')", $permissions);
        $this->assertStringContainsString("microphone=('self')", $permissions);
    }

    public function testNonceGenerationAndReuse(): void
    {
        // First call to generate nonce
        $this->headers->enableCSP();
        $firstNonce = $this->headers->getNonce();

        // Second call should reuse the same nonce
        $this->headers->enableCSP();
        $secondNonce = $this->headers->getNonce();

        // Third call with custom policies should still reuse the same nonce
        $this->headers->enableCSP([
            'img-src' => ["'self'", "https://example.com"]
        ]);
        $thirdNonce = $this->headers->getNonce();

        $this->assertNotNull($firstNonce);
        $this->assertSame($firstNonce, $secondNonce);
        $this->assertSame($firstNonce, $thirdNonce);
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9+\/]+={0,2}$/', $firstNonce);
    }

    public function testCSPWithMultipleDirectives(): void
    {
        $customPolicies = [
            'script-src' => ["'self'", "https://trusted.com"],
            'style-src' => ["'self'", "https://fonts.googleapis.com"],
            'img-src' => ["'self'", "data:", "https:"],
            'connect-src' => ["'self'", "https://api.example.com"],
            'font-src' => ["'self'", "https://fonts.gstatic.com"],
            'media-src' => ["'self'", "https://media.example.com"],
            'object-src' => ["'none'"],
            'base-uri' => ["'self'"],
            'form-action' => ["'self'"],
            'frame-ancestors' => ["'none'"]
        ];

        $this->headers->enableCSP($customPolicies);

        $reflection = new \ReflectionClass($this->headers);
        $property = $reflection->getProperty('headers');
        $property->setAccessible(true);
        $headers = $property->getValue($this->headers);

        $this->assertArrayHasKey('Content-Security-Policy', $headers);
        $csp = $headers['Content-Security-Policy'];

        foreach ($customPolicies as $directive => $sources) {
            foreach ($sources as $source) {
                $this->assertStringContainsString($source, $csp);
            }
        }
    }

    public function testPermissionsPolicyWithAllFeatures(): void
    {
        $policies = [
            'accelerometer' => ["'self'"],
            'camera' => ["'self'"],
            'display-capture' => ["'self'"],
            'document-domain' => ["'self'"],
            'encrypted-media' => ["'self'"],
            'execution-while-not-rendered' => ["'self'"],
            'execution-while-out-of-viewport' => ["'self'"],
            'fullscreen' => ["'self'"],
            'geolocation' => ["'self'"],
            'gyroscope' => ["'self'"],
            'keyboard-map' => ["'self'"],
            'magnetometer' => ["'self'"],
            'microphone' => ["'self'"],
            'midi' => ["'self'"],
            'navigation-override' => ["'self'"],
            'payment' => ["'self'"],
            'picture-in-picture' => ["'self'"],
            'publickey-credentials-get' => ["'self'"],
            'screen-wake-lock' => ["'self'"],
            'sync-xhr' => ["'self'"],
            'usb' => ["'self'"],
            'web-share' => ["'self'"],
            'xr-spatial-tracking' => ["'self'"]
        ];

        $this->headers->enablePermissionsPolicy($policies);

        $reflection = new \ReflectionClass($this->headers);
        $property = $reflection->getProperty('headers');
        $property->setAccessible(true);
        $headers = $property->getValue($this->headers);

        $this->assertArrayHasKey('Permissions-Policy', $headers);
        $policy = $headers['Permissions-Policy'];

        foreach ($policies as $feature => $allowlist) {
            $this->assertStringContainsString("{$feature}=('self')", $policy);
        }
    }

    public function testClientHintsPolicyWithAllHints(): void
    {
        $hints = [
            'ch-ua' => 'self',
            'ch-ua-mobile' => 'self',
            'ch-ua-platform' => 'self',
            'ch-ua-platform-version' => 'self',
            'ch-ua-full-version' => 'self',
            'ch-ua-full-version-list' => 'self',
            'ch-ua-bitness' => 'self',
            'ch-ua-model' => 'self',
            'ch-ua-viewport-width' => 'self',
            'ch-ua-device-memory' => 'self',
            'ch-ua-dpr' => 'self',
            'ch-ua-color-gamut' => 'self',
            'ch-ua-prefers-color-scheme' => 'self',
            'ch-ua-prefers-reduced-motion' => 'self',
            'ch-ua-prefers-reduced-transparency' => 'self',
            'ch-ua-forced-colors' => 'self'
        ];

        $this->headers->enableClientHintsPolicy($hints);

        $reflection = new \ReflectionClass($this->headers);
        $property = $reflection->getProperty('headers');
        $property->setAccessible(true);
        $headers = $property->getValue($this->headers);

        $this->assertArrayHasKey('Permissions-Policy', $headers);
        $policy = $headers['Permissions-Policy'];

        foreach ($hints as $hint => $value) {
            $this->assertStringContainsString("{$hint}={$value}", $policy);
        }
    }

    public function testCriticalCHWithAllHints(): void
    {
        $hints = [
            'Sec-CH-UA',
            'Sec-CH-UA-Mobile',
            'Sec-CH-UA-Platform',
            'Sec-CH-UA-Platform-Version',
            'Sec-CH-UA-Full-Version',
            'Sec-CH-UA-Full-Version-List',
            'Sec-CH-UA-Bitness',
            'Sec-CH-UA-Model',
            'Sec-CH-UA-Viewport-Width',
            'Sec-CH-UA-Device-Memory',
            'Sec-CH-UA-DPR',
            'Sec-CH-UA-Color-Gamut',
            'Sec-CH-UA-Prefers-Color-Scheme',
            'Sec-CH-UA-Prefers-Reduced-Motion',
            'Sec-CH-UA-Prefers-Reduced-Transparency',
            'Sec-CH-UA-Forced-Colors'
        ];

        $this->headers->enableCriticalCH($hints);

        $reflection = new \ReflectionClass($this->headers);
        $property = $reflection->getProperty('headers');
        $property->setAccessible(true);
        $headers = $property->getValue($this->headers);

        $this->assertArrayHasKey('Critical-CH', $headers);

        // Sort both arrays before comparison
        sort($hints);
        $actualHints = explode(', ', $headers['Critical-CH']);
        sort($actualHints);

        $this->assertSame(implode(', ', $hints), implode(', ', $actualHints));
    }
}
