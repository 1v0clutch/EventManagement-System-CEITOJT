<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Checking Event Requests (OLD SYSTEM)...\n\n";

// Get all event requests
$requests = \App\Models\EventRequest::with(['requester', 'deanApprover', 'chairApprover'])->get();

echo "Total Event Requests: " . $requests->count() . "\n\n";

if ($requests->count() > 0) {
    foreach ($requests as $request) {
        echo "ID: {$request->id}\n";
        echo "Title: {$request->title}\n";
        echo "Requested by: {$request->requester->name} ({$request->requester->role})\n";
        echo "Status: {$request->status}\n";
        echo "Dean Approval: " . ($request->dean_approval ?? 'pending') . "\n";
        echo "Chair Approval: " . ($request->chair_approval ?? 'pending') . "\n";
        echo "\n";
    }
} else {
    echo "No event requests found in the database.\n";
}

echo "\n===========================================\n\n";
echo "Checking Event Approvals (NEW SYSTEM)...\n\n";

// Get all event approvals
$approvals = \App\Models\EventApproval::with(['host', 'approvers.approver'])->get();

echo "Total Event Approvals: " . $approvals->count() . "\n\n";

if ($approvals->count() > 0) {
    foreach ($approvals as $approval) {
        echo "ID: {$approval->id}\n";
        echo "Title: {$approval->title}\n";
        echo "Event Type: {$approval->event_type}\n";
        echo "Host: {$approval->host->name} ({$approval->host->role})\n";
        echo "Status: {$approval->status}\n";
        echo "Approvers:\n";
        foreach ($approval->approvers as $approver) {
            echo "  - {$approver->approver->name} ({$approver->approver->role}): {$approver->status}\n";
        }
        echo "\n";
    }
} else {
    echo "No event approvals found in the database.\n";
}
