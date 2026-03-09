<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing /event-requests/my-requests endpoint...\n\n";

// Test for Faculty Member (Keith Coner - ID: 5)
echo "=== FACULTY MEMBER VIEW (Keith Coner - ID: 5) ===\n";
$facultyUser = \App\Models\User::find(5);
if ($facultyUser) {
    echo "User: {$facultyUser->name} ({$facultyUser->role})\n\n";
    
    // Simulate the controller logic for Faculty
    $requests = \App\Models\EventRequest::where('requested_by', $facultyUser->id)
        ->with(['requester', 'reviewer', 'deanApprover', 'chairApprover'])
        ->orderBy('created_at', 'desc')
        ->get();
    
    echo "Found {$requests->count()} requests:\n";
    foreach ($requests as $request) {
        echo "- ID: {$request->id}, Title: {$request->title}, Status: {$request->status}\n";
        echo "  Dean Approved: " . ($request->dean_approved_by ? 'Yes' : 'No') . "\n";
        echo "  Chair Approved: " . ($request->chair_approved_by ? 'Yes' : 'No') . "\n\n";
    }
} else {
    echo "Faculty user not found!\n";
}

echo "\n=== DEAN VIEW (Gabriel Ian - ID: 4) ===\n";
$deanUser = \App\Models\User::find(4);
if ($deanUser) {
    echo "User: {$deanUser->name} ({$deanUser->role})\n\n";
    
    // Simulate the controller logic for Dean
    $requests = \App\Models\EventRequest::with(['requester', 'reviewer', 'deanApprover', 'chairApprover'])
        ->where('dean_approved_by', $deanUser->id)
        ->orderBy('created_at', 'desc')
        ->get();
    
    echo "Found {$requests->count()} requests Dean has approved/declined:\n";
    foreach ($requests as $request) {
        echo "- ID: {$request->id}, Title: {$request->title}, Status: {$request->status}\n";
        echo "  Requested by: {$request->requester->name}\n";
        echo "  Dean Approved: " . ($request->dean_approved_by ? 'Yes' : 'No') . "\n";
        echo "  Chair Approved: " . ($request->chair_approved_by ? 'Yes' : 'No') . "\n\n";
    }
} else {
    echo "Dean user not found!\n";
}

echo "\n=== CHAIRPERSON VIEW ===\n";
$chairUser = \App\Models\User::where('role', 'Chairperson')->first();
if ($chairUser) {
    echo "User: {$chairUser->name} ({$chairUser->role})\n\n";
    
    // Simulate the controller logic for Chairperson
    $requests = \App\Models\EventRequest::with(['requester', 'reviewer', 'deanApprover', 'chairApprover'])
        ->where('chair_approved_by', $chairUser->id)
        ->orderBy('created_at', 'desc')
        ->get();
    
    echo "Found {$requests->count()} requests Chairperson has approved/declined:\n";
    foreach ($requests as $request) {
        echo "- ID: {$request->id}, Title: {$request->title}, Status: {$request->status}\n";
        echo "  Requested by: {$request->requester->name}\n";
        echo "  Dean Approved: " . ($request->dean_approved_by ? 'Yes' : 'No') . "\n";
        echo "  Chair Approved: " . ($request->chair_approved_by ? 'Yes' : 'No') . "\n\n";
    }
} else {
    echo "No Chairperson found in the system!\n";
}