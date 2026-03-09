<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Checking Event Request Details...\n\n";

// Get all event requests with full details
$requests = \App\Models\EventRequest::with(['requester', 'deanApprover', 'chairApprover'])->get();

foreach ($requests as $request) {
    echo "========================================\n";
    echo "ID: {$request->id}\n";
    echo "Title: {$request->title}\n";
    echo "Requested by: {$request->requester->name} (ID: {$request->requester->id}, Role: {$request->requester->role})\n";
    echo "Status: {$request->status}\n";
    echo "Required Approvers: " . json_encode($request->required_approvers) . "\n";
    echo "Dean Approved By: " . ($request->dean_approved_by ?? 'NULL') . "\n";
    echo "Chair Approved By: " . ($request->chair_approved_by ?? 'NULL') . "\n";
    echo "All Approvals Received: " . ($request->all_approvals_received ? 'true' : 'false') . "\n";
    echo "\n";
}

echo "\n========================================\n";
echo "Checking Dean and Chairperson users...\n\n";

$dean = \App\Models\User::where('role', 'Dean')->first();
$chair = \App\Models\User::where('role', 'Chairperson')->first();

if ($dean) {
    echo "Dean found: {$dean->name} (ID: {$dean->id})\n";
} else {
    echo "No Dean found in the system!\n";
}

if ($chair) {
    echo "Chairperson found: {$chair->name} (ID: {$chair->id})\n";
} else {
    echo "No Chairperson found in the system!\n";
}
