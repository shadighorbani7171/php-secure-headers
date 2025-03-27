<?php

namespace EasyShield\SecureHeaders\Tests;

use EasyShield\SecureHeaders\SecureHeaders;
use PHPUnit\Framework\TestCase;

class CSPBuilderTest extends TestCase
{
    private SecureHeaders $secureHeaders;

    protected function setUp(): void
    {
        $this->secureHeaders = new SecureHeaders();
    }

    public function testDefaultDirectives(): void
    {
        $builder = $this->secureHeaders->csp();
        $directives = $builder->getDirectives();

        $this->assertArrayHasKey('default-src', $directives);
        $this->assertArrayHasKey('base-uri', $directives);
        $this->assertArrayHasKey('form-action', $directives);
        $this->assertEquals(["'self'"], $directives['default-src']);
    }

    public function testAllowScripts(): void
    {
        $builder = $this->secureHeaders->csp();
        $builder->allowScripts('https://example.com', 'https://cdn.example.com');
        $directives = $builder->getDirectives();

        $this->assertArrayHasKey('script-src', $directives);
        $this->assertContains('https://example.com', $directives['script-src']);
        $this->assertContains('https://cdn.example.com', $directives['script-src']);
        $this->assertMatchesRegularExpression("/'nonce-[A-Za-z0-9+\/=]+'/", $directives['script-src'][2]);
    }

    public function testAllowStyles(): void
    {
        $builder = $this->secureHeaders->csp();
        $builder->allowStyles('https://fonts.googleapis.com');
        $directives = $builder->getDirectives();

        $this->assertArrayHasKey('style-src', $directives);
        $this->assertContains('https://fonts.googleapis.com', $directives['style-src']);
        $this->assertMatchesRegularExpression("/'nonce-[A-Za-z0-9+\/=]+'/", $directives['style-src'][1]);
    }

    public function testStrictDynamic(): void
    {
        $builder = $this->secureHeaders->csp();
        $builder->allowScripts()->useStrictDynamic();
        $directives = $builder->getDirectives();

        $this->assertArrayHasKey('script-src', $directives);
        $this->assertContains("'strict-dynamic'", $directives['script-src']);
    }

    public function testWithoutNonce(): void
    {
        $builder = $this->secureHeaders->csp();
        $builder->withoutNonce()->allowScripts('https://example.com');
        $directives = $builder->getDirectives();

        $this->assertArrayHasKey('script-src', $directives);
        $this->assertCount(1, $directives['script-src']); // Only example.com
        $this->assertStringNotContainsString("'nonce-", implode(' ', $directives['script-src']));
    }

    public function testAllowImages(): void
    {
        $builder = $this->secureHeaders->csp();
        $builder->allowImages('https://images.example.com');
        $directives = $builder->getDirectives();

        $this->assertArrayHasKey('img-src', $directives);
        $this->assertContains('https://images.example.com', $directives['img-src']);
    }

    public function testAllowFonts(): void
    {
        $builder = $this->secureHeaders->csp();
        $builder->allowFonts('https://fonts.example.com');
        $directives = $builder->getDirectives();

        $this->assertArrayHasKey('font-src', $directives);
        $this->assertContains('https://fonts.example.com', $directives['font-src']);
    }

    public function testAllowConnections(): void
    {
        $builder = $this->secureHeaders->csp();
        $builder->allowConnections('https://api.example.com');
        $directives = $builder->getDirectives();

        $this->assertArrayHasKey('connect-src', $directives);
        $this->assertContains('https://api.example.com', $directives['connect-src']);
    }

    public function testBlockFrames(): void
    {
        $builder = $this->secureHeaders->csp();
        $builder->blockFrames();
        $directives = $builder->getDirectives();

        $this->assertArrayHasKey('frame-ancestors', $directives);
        $this->assertEquals(["'none'"], $directives['frame-ancestors']);
    }

    public function testAllowUnsafeInlineScripts(): void
    {
        $builder = $this->secureHeaders->csp();
        $builder->allowUnsafeInlineScripts();
        $directives = $builder->getDirectives();

        $this->assertArrayHasKey('script-src', $directives);
        $this->assertContains("'unsafe-inline'", $directives['script-src']);
    }

    public function testAllowUnsafeInlineStyles(): void
    {
        $builder = $this->secureHeaders->csp();
        $builder->allowUnsafeInlineStyles();
        $directives = $builder->getDirectives();

        $this->assertArrayHasKey('style-src', $directives);
        $this->assertContains("'unsafe-inline'", $directives['style-src']);
    }

    public function testAllowUnsafeEval(): void
    {
        $builder = $this->secureHeaders->csp();
        $builder->allowUnsafeEval();
        $directives = $builder->getDirectives();

        $this->assertArrayHasKey('script-src', $directives);
        $this->assertContains("'unsafe-eval'", $directives['script-src']);
    }

    public function testUpgradeInsecureRequests(): void
    {
        $builder = $this->secureHeaders->csp();
        $builder->upgradeInsecureRequests();
        $directives = $builder->getDirectives();

        $this->assertArrayHasKey('upgrade-insecure-requests', $directives);
    }

    public function testAddScriptHash(): void
    {
        $builder = $this->secureHeaders->csp();
        $builder->addScriptHash('sha256', 'hash123');
        $directives = $builder->getDirectives();

        $this->assertArrayHasKey('script-src', $directives);
        $this->assertContains("'sha256-hash123'", $directives['script-src']);
    }

    public function testAddStyleHash(): void
    {
        $builder = $this->secureHeaders->csp();
        $builder->addStyleHash('sha256', 'hash123');
        $directives = $builder->getDirectives();

        $this->assertArrayHasKey('style-src', $directives);
        $this->assertContains("'sha256-hash123'", $directives['style-src']);
    }

    public function testSetDefaultSrc(): void
    {
        $builder = $this->secureHeaders->csp();
        $builder->setDefaultSrc('https://default.example.com');
        $directives = $builder->getDirectives();

        $this->assertArrayHasKey('default-src', $directives);
        $this->assertEquals(['https://default.example.com'], $directives['default-src']);
    }

    public function testIntegrationWithSecureHeaders(): void
    {
        // Build CSP policy
        $cspPolicies = $this->secureHeaders->csp()
            ->allowScripts('https://example.com')
            ->allowStyles('https://fonts.googleapis.com')
            ->useStrictDynamic()
            ->getDirectives();
            
        // Enable CSP with the configured directives
        $this->secureHeaders->enableCSP($cspPolicies);
        $headers = $this->secureHeaders->getHeaders();

        $this->assertArrayHasKey('Content-Security-Policy', $headers);
        $this->assertStringContainsString('script-src', $headers['Content-Security-Policy']);
        $this->assertStringContainsString('style-src', $headers['Content-Security-Policy']);
        $this->assertStringContainsString('https://example.com', $headers['Content-Security-Policy']);
        $this->assertStringContainsString('https://fonts.googleapis.com', $headers['Content-Security-Policy']);
        $this->assertStringContainsString("'strict-dynamic'", $headers['Content-Security-Policy']);
    }

    public function testComplexPolicy(): void
    {
        $builder = $this->secureHeaders->csp()
            ->allowScripts('https://cdn.example.com')
            ->allowStyles('https://fonts.googleapis.com')
            ->allowImages('https://images.example.com')
            ->allowFonts('https://fonts.example.com')
            ->allowConnections('https://api.example.com')
            ->blockFrames()
            ->upgradeInsecureRequests();

        $directives = $builder->getDirectives();

        $this->assertArrayHasKey('script-src', $directives);
        $this->assertArrayHasKey('style-src', $directives);
        $this->assertArrayHasKey('img-src', $directives);
        $this->assertArrayHasKey('font-src', $directives);
        $this->assertArrayHasKey('connect-src', $directives);
        $this->assertArrayHasKey('frame-ancestors', $directives);
        $this->assertArrayHasKey('upgrade-insecure-requests', $directives);
    }

    public function testDetectExternalResourcesFromHtml(): void
    {
        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <script src="https://example.com/script.js"></script>
    <link rel="stylesheet" href="https://example.com/style.css">
    <style>@import url('https://fonts.googleapis.com/css?family=Roboto');</style>
</head>
<body>
    <img src="https://example.com/image.jpg">
    <iframe src="https://example.com/frame.html"></iframe>
</body>
</html>
HTML;

        $builder = $this->secureHeaders->csp();
        $builder->detectExternalResourcesFromHtml($html);
        $directives = $builder->getDirectives();

        $this->assertArrayHasKey('script-src', $directives);
        $this->assertContains('https://example.com', $directives['script-src']);
        
        $this->assertArrayHasKey('style-src', $directives);
        $this->assertContains('https://example.com', $directives['style-src']);
        $this->assertContains('https://fonts.googleapis.com', $directives['style-src']);
        
        $this->assertArrayHasKey('img-src', $directives);
        $this->assertContains('https://example.com', $directives['img-src']);
        
        $this->assertArrayHasKey('frame-src', $directives);
        $this->assertContains('https://example.com', $directives['frame-src']);
    }

    public function testInjectNoncesToHtml(): void
    {
        $html = <<<HTML
<script>console.log('test');</script>
<style>body { color: red; }</style>
HTML;

        $builder = $this->secureHeaders->csp();
        $modifiedHtml = $builder->injectNoncesToHtml($html);

        // Check that nonces were injected into script and style tags
        $this->assertMatchesRegularExpression('/<script nonce="[A-Za-z0-9+\/=]+"/', $modifiedHtml);
        $this->assertMatchesRegularExpression('/<style nonce="[A-Za-z0-9+\/=]+"/', $modifiedHtml);
    }

    public function testInjectNoncesToHtmlWithDisabledNonce(): void
    {
        $html = '<script>console.log("test");</script>';

        $builder = $this->secureHeaders->csp();
        $builder->withoutNonce();
        $modifiedHtml = $builder->injectNoncesToHtml($html);

        // HTML should remain unchanged
        $this->assertEquals($html, $modifiedHtml);
    }
} 