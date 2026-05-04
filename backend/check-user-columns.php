<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = \App\Models\User::first();

if ($user) {
    echo "User columns:\n";
    print_r(array_keys($user->getAttributes()));
} else {
    echo "No users found\n";
}
