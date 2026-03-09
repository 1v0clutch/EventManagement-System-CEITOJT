<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== Testing Login Credentials ===\n\n";

$testPassword = 'password123';
$testEmail = 'admin@cvsu.edu.ph';

$user = User::where('email', $testEmail)->first();

if ($user) {
    echo "User: {$user->email}\n";
    echo "Testing password: {$testPassword}\n";
    
    $passwordMatches = Hash::check($testPassword, $user->password);
    
    if ($passwordMatches) {
        echo "✓ Password is CORRECT!\n";
    } else {
        echo "✗ Password is INCORRECT!\n";
        echo "\nTrying to create a new hash for comparison:\n";
        $newHash = Hash::make($testPassword);
        echo "New hash: " . substr($newHash, 0, 30) . "...\n";
        echo "User hash: " . substr($user->password, 0, 30) . "...\n";
    }
} else {
    echo "User not found!\n";
}
