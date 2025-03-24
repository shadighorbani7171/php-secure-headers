<?php

require_once __DIR__ . '/../vendor/autoload.php';

use SecureHeaders\SecureHeaders;

// تنظیم سطح امنیتی بر اساس محیط
$isProduction = false; // در محیط واقعی این مقدار باید از تنظیمات پروژه خوانده شود

if ($isProduction) {
    // سطح امنیتی Strict برای محیط تولید
    $headers = new SecureHeaders(SecureHeaders::LEVEL_STRICT);
} else {
    // سطح امنیتی Basic برای محیط توسعه
    $headers = new SecureHeaders(SecureHeaders::LEVEL_BASIC);
}

// فعال‌سازی همه هدرهای امنیتی با یک دستور
$headers->enableAllSecurityHeaders();

// دریافت nonce برای اسکریپت‌های inline
$nonce = $headers->getNonce();

// اعمال هدرها
$headers->apply();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نمایش سطوح امنیتی</title>
    <style>
        body {
            font-family: system-ui, -apple-system, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .security-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 4px;
            margin: 1rem 0;
        }
        .security-level {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        .level-basic {
            background: #e3f2fd;
            color: #1565c0;
        }
        .level-strict {
            background: #e8f5e9;
            color: #2e7d32;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>نمایش سطوح امنیتی</h1>
            <div class="security-level <?= $isProduction ? 'level-strict' : 'level-basic' ?>">
                سطح امنیتی: <?= $isProduction ? 'Strict' : 'Basic' ?>
            </div>
        </div>

        <div class="security-info">
            <h2>هدرهای امنیتی فعال:</h2>
            <ul>
                <li>Content Security Policy (CSP)</li>
                <li>HTTP Strict Transport Security (HSTS)</li>
                <li>X-Frame-Options</li>
                <li>X-Content-Type-Options</li>
                <li>X-XSS-Protection</li>
                <li>Referrer Policy</li>
                <li>Permissions Policy</li>
            </ul>
        </div>

        <script nonce="<?= htmlspecialchars($nonce) ?>">
            // نمایش اطلاعات امنیتی در کنسول
            console.log('سطح امنیتی:', '<?= $isProduction ? "Strict" : "Basic" ?>');
            console.log('CSP Nonce:', '<?= $nonce ?>');
        </script>
    </div>
</body>
</html> 