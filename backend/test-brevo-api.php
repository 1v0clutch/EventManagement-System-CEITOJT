<?php

// Direct Brevo API test
$apiKey = 'xsmtpsib-c1aaabe7dcc91dd923d010477e264127496b390c23cd5c84ce0a352c18238284-EykxDMLxifMexhEk';
$toEmail = 'main.markvincent.asibor@cvsu.edu.ph';

$data = [
    'sender' => [
        'name' => 'Event Management System',
        'email' => 'a2c2d6001@smtp-brevo.com'
    ],
    'to' => [
        ['email' => $toEmail, 'name' => 'Test User']
    ],
    'subject' => 'Test Email from Brevo API',
    'htmlContent' => '<h1>Test Email</h1><p>If you receive this, Brevo API is working!</p>'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.brevo.com/v3/smtp/email');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'accept: application/json',
    'api-key: ' . $apiKey,
    'content-type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";

if ($httpCode === 201) {
    echo "\n✅ Email sent successfully via Brevo API!\n";
    echo "Check your inbox and spam folder.\n";
} else {
    echo "\n❌ Failed to send email\n";
    $decoded = json_decode($response, true);
    if (isset($decoded['message'])) {
        echo "Error: " . $decoded['message'] . "\n";
    }
}
