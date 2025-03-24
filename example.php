<?php
require_once __DIR__ . '/vendor/autoload.php';

use SecureHeaders\SecureHeaders;

// Initialize SecureHeaders with strict security level
$headers = new SecureHeaders(SecureHeaders::LEVEL_STRICT);

// Enable all security headers
$headers->enableAllSecurityHeaders();

// Customize some headers for our example
$headers->enableCSP([
    'script-src' => ["'self'", "https://cdn.jsdelivr.net", "https://code.jquery.com"],
    'style-src' => ["'self'", "https://cdn.jsdelivr.net", "https://fonts.googleapis.com"],
    'img-src' => ["'self'", "data:", "https:", "https://picsum.photos"],
    'font-src' => ["'self'", "https://fonts.gstatic.com"],
    'connect-src' => ["'self'", "https://api.example.com"]
]);

// Customize Permissions Policy
$headers->enablePermissionsPolicy([
    'camera' => ["'self'"],
    'microphone' => ["'self'"],
    'geolocation' => ["'self'"],
    'payment' => ["'self'"]
]);

// Customize Critical-CH
$headers->enableCriticalCH([
    'Sec-CH-UA',
    'Sec-CH-UA-Platform',
    'Sec-CH-UA-Mobile'
]);

// Customize Client Hints Policy
$headers->enableClientHintsPolicy([
    'ch-ua-platform' => '*',
    'ch-ua' => 'self'
]);

// Apply headers
$headers->apply();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Headers Example</title>
    
    <!-- External CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    
    <!-- Internal CSS -->
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            padding: 20px;
        }
        .header-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .test-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Security Headers Test Page</h1>
        
        <!-- Header Information Section -->
        <div class="header-info">
            <h3>Current Security Headers</h3>
            <pre id="headers"></pre>
        </div>

        <!-- Test Section 1: Basic HTML -->
        <div class="test-section">
            <h3>Test Section 1: Basic HTML</h3>
            <p>This section tests basic HTML rendering and styling.</p>
            <div class="alert alert-info">
                This is a Bootstrap alert component.
            </div>
        </div>

        <!-- Test Section 2: External Resources -->
        <div class="test-section">
            <h3>Test Section 2: External Resources</h3>
            <p>Testing external images and scripts:</p>
            <img src="https://picsum.photos/200/100" alt="Random image" class="img-fluid mb-3">
            <button id="testButton" class="btn btn-primary">Test jQuery</button>
        </div>

        <!-- Test Section 3: Forms and AJAX -->
        <div class="test-section">
            <h3>Test Section 3: Forms and AJAX</h3>
            <form id="testForm">
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" required>
                </div>
                <button type="submit" class="btn btn-success">Submit</button>
            </form>
            <div id="formResult" class="mt-3"></div>
        </div>

        <!-- Test Section 4: Client Hints -->
        <div class="test-section">
            <h3>Test Section 4: Client Hints</h3>
            <div id="clientHints"></div>
        </div>
    </div>

    <!-- External JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Internal JavaScript -->
    <script>
        // Display current headers
        fetch(window.location.href)
            .then(response => {
                const headers = {};
                for (const [key, value] of response.headers) {
                    headers[key] = value;
                }
                document.getElementById('headers').textContent = JSON.stringify(headers, null, 2);
            });

        // Test jQuery
        $('#testButton').click(function() {
            alert('jQuery is working!');
        });

        // Test form submission
        $('#testForm').submit(function(e) {
            e.preventDefault();
            const name = $('#name').val();
            $('#formResult').html(`<div class="alert alert-success">Form submitted with name: ${name}</div>`);
        });

        // Display Client Hints
        const clientHints = {
            'User-Agent': navigator.userAgent,
            'Platform': navigator.platform,
            'Language': navigator.language,
            'Device Memory': navigator.deviceMemory,
            'Hardware Concurrency': navigator.hardwareConcurrency
        };
        document.getElementById('clientHints').innerHTML = `
            <pre>${JSON.stringify(clientHints, null, 2)}</pre>
        `;
    </script>
</body>
</html> 