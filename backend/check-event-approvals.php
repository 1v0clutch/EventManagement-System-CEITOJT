<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Checking Event Approvals...\n\n";

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
        echo "Date: {$approval->date}\n";
        echo "Time: {$approval->time}\n";
        echo "Approvers:\n";
        foreach ($approval->approvers as $approver) {
            echo "  - {$approver->approver->name} ({$approver->approver->role}): {$approver->status}\n";
        }
        echo "\n";
    }
} else {
    echo "No event approvals found in the database.\n";
    echo "\nTo test the feature:\n";
    echo "1. Log in as a Faculty Member or Staff\n";
    echo "2. Go to /add-event\n";
    echo "3. Create a meeting (not an event)\n";
    echo "4. The meeting should be sent for approval\n";
}
