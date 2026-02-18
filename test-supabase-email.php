<?php

/**
 * Supabase Email Integration Test Script
 * 
 * This script tests the Supabase email service without running the full Laravel app.
 * Run from the project root: php test-supabase-email.php
 */

require __DIR__ . '/backend/vendor/autoload.php';

use Illuminate\Support\Facades\Http;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/backend');
$dotenv->load();

echo "========================================\n";
echo "Supabase Email Integration Test\n";
echo "========================================\n\n";

// Get configuration
$supabaseUrl = $_ENV['SUPABASE_URL'] ?? null;
$supabaseKey = $_ENV['SUPABASE_ANON_KEY'] ?? null;
$fromEmail = $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@example.com';
$fromName = $_ENV['MAIL_FROM_NAME'] ?? 'Test';

// Validate configuration
if (!$supabaseUrl || !$supabaseKey) {
    echo "❌ Error: Supabase configuration missing\n";
    echo "Please set SUPABASE_URL and SUPABASE_ANON_KEY in backend/.env\n";
    exit(1);
}

echo "Configuration:\n";
echo "- Supabase URL: $supabaseUrl\n";
echo "- From Email: $fromEmail\n";
echo "- From Name: $fromName\n\n";

// Prompt for test email
echo "Enter test email address: ";
$testEmail = trim(fgets(STDIN));

if (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
    echo "❌ Invalid email address\n";
    exit(1);
}

// Generate test OTP
$testOtp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

echo "\nTest OTP: $testOtp\n";
echo "Sending test email...\n\n";

// Build email HTML
$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #4F46E5; color: white; padding: 20px; text-align: center; }
        .content { background-color: #f9fafb; padding: 30px; }
        .otp-code { background-color: #fff; border: 2px dashed #4F46E5; padding: 20px; text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 8px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Test Email</h1>
        </div>
        <div class="content">
            <p>This is a test email from your Supabase integration.</p>
            <p>Test OTP Code:</p>
            <div class="otp-code">$testOtp</div>
            <p>If you received this email, your Supabase email integration is working correctly!</p>
        </div>
    </div>
</body>
</html>
HTML;

$text = "Test Email\n\nThis is a test email from your Supabase integration.\n\nTest OTP Code: $testOtp\n\nIf you received this email, your Supabase email integration is working correctly!";

// Send via Supabase Edge Function
try {
    $edgeFunctionUrl = $supabaseUrl . '/functions/v1/send-email';
    
    $ch = curl_init($edgeFunctionUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $supabaseKey,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'to' => $testEmail,
        'from' => $fromEmail,
        'from_name' => $fromName,
        'subject' => '🔐 Supabase Email Test',
        'html' => $html,
        'text' => $text,
    ]));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        throw new Exception("cURL error: $error");
    }
    
    $responseData = json_decode($response, true);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        echo "✅ Email sent successfully!\n\n";
        echo "Response:\n";
        echo json_encode($responseData, JSON_PRETTY_PRINT) . "\n\n";
        echo "Check your inbox at: $testEmail\n";
    } else {
        echo "❌ Failed to send email\n\n";
        echo "HTTP Code: $httpCode\n";
        echo "Response:\n";
        echo json_encode($responseData, JSON_PRETTY_PRINT) . "\n\n";
        
        if (isset($responseData['error'])) {
            echo "Error: " . $responseData['error'] . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n========================================\n";
echo "Test Complete\n";
echo "========================================\n";
