<?php

require_once __DIR__ . '/../vendor/autoload.php';

use EasyShield\SecureHeaders\SecureHeaders;

// Create a new SecureHeaders instance with STRICT security level
$secureHeaders = new SecureHeaders(SecureHeaders::LEVEL_STRICT);

// Example 1: Basic CSP configuration using fluent API
echo "Example 1: Basic CSP configuration\n";
echo "--------------------------------\n";

// Configure CSP using the fluent builder
$secureHeaders->csp()
    ->allowScripts('https://cdn.jsdelivr.net', 'https://ajax.googleapis.com')
    ->allowStyles('https://fonts.googleapis.com')
    ->allowImages('https://img.example.com')
    ->blockFrames()
    ->useStrictDynamic()
    ->upgradeInsecureRequests();

// Enable CSP with the configured directives
$secureHeaders->enableCSP();

// Get and display all headers
$headers = $secureHeaders->getHeaders();
echo "Content-Security-Policy: " . $headers['Content-Security-Policy'] . "\n\n";

// Example 2: Different security levels
echo "Example 2: Different security levels\n";
echo "--------------------------------\n";

// Basic security level
$basicHeaders = new SecureHeaders(SecureHeaders::LEVEL_BASIC);
$basicHeaders->csp()
    ->allowScripts('https://cdn.example.com')
    ->allowStyles('https://fonts.googleapis.com')
    ->allowUnsafeInlineScripts(); // Only in BASIC level

$basicHeaders->enableCSP();
echo "BASIC Security Level CSP: " . $basicHeaders->getHeaders()['Content-Security-Policy'] . "\n\n";

// Strict security level
$strictHeaders = new SecureHeaders(SecureHeaders::LEVEL_STRICT);
$strictHeaders->csp()
    ->allowScripts('https://cdn.example.com')
    ->allowStyles('https://fonts.googleapis.com')
    ->useStrictDynamic(); // Recommended for STRICT level

$strictHeaders->enableCSP();
echo "STRICT Security Level CSP: " . $strictHeaders->getHeaders()['Content-Security-Policy'] . "\n\n";

// Example 3: Complete policy with all directives
echo "Example 3: Complete policy with all directives\n";
echo "----------------------------------------\n";

$completeHeaders = new SecureHeaders();
$completeHeaders->csp()
    // Script sources
    ->allowScripts('https://cdn.example.com', 'https://api.example.com')
    ->useStrictDynamic()
    
    // Style sources
    ->allowStyles('https://fonts.googleapis.com', 'https://cdn.example.com/css')
    
    // Media sources
    ->allowImages('https://images.example.com', 'data:')
    ->allowFonts('https://fonts.gstatic.com', 'https://fonts.example.com')
    
    // Connection sources
    ->allowConnections('https://api.example.com', 'wss://ws.example.com')
    
    // Frame control
    ->blockFrames()
    
    // Upgrade insecure requests
    ->upgradeInsecureRequests();

$completeHeaders->enableCSP();
echo "Complete CSP: " . $completeHeaders->getHeaders()['Content-Security-Policy'] . "\n";

// Example 4: Using CSP with hashes
echo "\nExample 4: Using CSP with hashes\n";
echo "--------------------------------\n";

$hashHeaders = new SecureHeaders();
$hashHeaders->csp()
    ->allowScripts('https://cdn.example.com')
    // Allow an inline script with specific hash
    ->addScriptHash('sha256', 'jWu23zYWG+xg+f/H3K9/v/Y9FRqB9K05/6Rvz1Q3dJA=')
    // Allow an inline style with specific hash
    ->addStyleHash('sha256', 'X3OaKuRV0SBxRtsbc9UZj9LLO/faCBvoycMD+vGbMA8=');

$hashHeaders->enableCSP();
echo "CSP with hashes: " . $hashHeaders->getHeaders()['Content-Security-Policy'] . "\n";

// Example 5: Using CSP without nonce
echo "\nExample 5: Using CSP without nonce\n";
echo "--------------------------------\n";

$nonceHeaders = new SecureHeaders();
$nonceHeaders->csp()
    ->withoutNonce()
    ->allowScripts('https://cdn.example.com')
    ->allowStyles('https://fonts.googleapis.com');

$nonceHeaders->enableCSP();
echo "CSP without nonce: " . $nonceHeaders->getHeaders()['Content-Security-Policy'] . "\n"; 