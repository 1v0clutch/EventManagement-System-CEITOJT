<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== Checking Test Users ===\n\n";

$testEmails = [
    'admin@cvsu.edu.ph',
    'dean.rodriguez@cvsu.edu.ph',
    'maria.garcia@cvsu.edu.ph',
];

foreach ($testEmails as $email) {
    $user = User::where('email', $email)->first();
    if ($user) {
        echo "✓ Found: {$email}\n";
        echo "  Name: {$user->name}\n";
        echo "  Role: {$user->role}\n";
        echo "  Department: {$user->department}\n";
        echo "  Email Verified: " . ($user->email_verified_at ? 'Yes' : 'No') . "\n";
        echo "  Is Validated: " . ($user->is_validated ? 'Yes' : 'No') . "\n";
        echo "  Password Hash: " . substr($user->password, 0, 20) . "...\n\n";
    } else {
        echo "✗ NOT FOUND: {$email}\n\n";
    }
}

echo "Total users in database: " . User::count() . "\n";
