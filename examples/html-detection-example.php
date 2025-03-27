<?php

require_once __DIR__ . '/../vendor/autoload.php';

use EasyShield\SecureHeaders\SecureHeaders;

// Sample HTML with various external resources
$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>CSP Builder Example</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/local-style.css">
    <style>
        body { background-color: #f5f5f5; }
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // This is an inline script that needs a nonce
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Page loaded');
        });
    </script>
</head>
<body>
    <div class="container">
        <h1>CSP Builder Example</h1>
        <p>This page demonstrates how CSPBuilder can automatically detect external resources.</p>
        <img src="https://placekitten.com/300/200" alt="Kitten">
        <img src="/images/local-image.jpg" alt="Local image">
        
        <iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ" frameborder="0" allowfullscreen></iframe>
        
        <div style="font-family: 'Roboto', sans-serif;">
            This text uses an external font.
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
HTML;

echo "Original HTML Size: " . strlen($html) . " bytes\n\n";

// Create a new SecureHeaders instance
$secureHeaders = new SecureHeaders();

// Demonstrate automatic resource detection
echo "Demonstrating automatic resource detection:\n";
echo "----------------------------------------\n";

$secureHeaders->csp()
    ->detectExternalResourcesFromHtml($html);

// Enable CSP with the auto-detected directives
$secureHeaders->enableCSP();

// Display the generated CSP header
$headers = $secureHeaders->getHeaders();
echo "Generated CSP Header:\n";
echo $headers['Content-Security-Policy'] . "\n\n";

// Demonstrate nonce injection
echo "Demonstrating nonce injection:\n";
echo "----------------------------\n";

// Reset headers
$secureHeaders = new SecureHeaders();

// Get a reference to the CSP builder
$cspBuilder = $secureHeaders->csp();

// Inject nonces into the HTML
$modifiedHtml = $cspBuilder->injectNoncesToHtml($html);

// Show the difference in HTML
echo "Nonces were injected into <script> and <style> tags.\n";
echo "Modified HTML Size: " . strlen($modifiedHtml) . " bytes\n\n";

// Show a sample of the modified HTML (just the head section for brevity)
$headSection = substr($modifiedHtml, 0, strpos($modifiedHtml, "</head>") + 7);
echo "Sample of modified HTML (head section):\n";
echo "------------------------------------\n";
echo $headSection . "\n\n";

// Enable CSP with nonces
$secureHeaders->enableCSP();

// Display the CSP header with nonces
echo "CSP Header with nonces:\n";
echo $secureHeaders->getHeaders()['Content-Security-Policy'] . "\n"; 