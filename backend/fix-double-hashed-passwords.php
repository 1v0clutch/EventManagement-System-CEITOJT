<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "=== Fix Double-Hashed Passwords ===\n\n";
echo "ISSUE: The User model had 'password' => 'hashed' in casts, causing double hashing.\n";
echo "This has been fixed in the model, but existing users need password reset.\n\n";

$users = User::all();
echo "Found {$users->count()} users.\n\n";

if ($users->count() === 0) {
    echo "No users to fix.\n";
    exit;
}

echo "Options:\n";
echo "1. Reset all passwords to a temporary password (users must use Forgot Password)\n";
echo "2. Set a specific password for a single user (for testing)\n";
echo "3. Exit\n\n";
echo "Enter your choice (1-3): ";

$handle = fopen("php://stdin", "r");
$choice = trim(fgets($handle));

if ($choice === '1') {
    $tempPassword = 'TempPass123!';
    echo "\nThis will set all passwords to: {$tempPassword}\n";
    echo "Type 'yes' to continue: ";
    $confirm = trim(fgets($handle));
    
    if ($confirm !== 'yes') {
        echo "Aborted.\n";
        exit;
    }
    
    echo "\nResetting passwords...\n";
    foreach ($users as $user) {
        DB::table('users')
            ->where('id', $user->id)
            ->update(['password' => Hash::make($tempPassword)]);
        echo "✓ Reset password for: {$user->email}\n";
    }
    echo "\n✅ All passwords reset to: {$tempPassword}\n";
    
} elseif ($choice === '2') {
    echo "\nAvailable users:\n";
    foreach ($users as $index => $user) {
        echo ($index + 1) . ". {$user->email}\n";
    }
    echo "\nEnter user number: ";
    $userNum = (int)trim(fgets($handle)) - 1;
    
    if (!isset($users[$userNum])) {
        echo "Invalid user number.\n";
        exit;
    }
    
    $selectedUser = $users[$userNum];
    echo "Enter new password for {$selectedUser->email}: ";
    $newPassword = trim(fgets($handle));
    
    if (strlen($newPassword) < 6) {
        echo "Password must be at least 6 characters.\n";
        exit;
    }
    
    DB::table('users')
        ->where('id', $selectedUser->id)
        ->update(['password' => Hash::make($newPassword)]);
    
    echo "\n✅ Password updated for: {$selectedUser->email}\n";
    
} else {
    echo "Exiting.\n";
}

fclose($handle);
