<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Event;
use App\Models\EventRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "=== Performance Test ===\n\n";

// Enable query logging
DB::enableQueryLog();

// Test 1: Events query with optimized eager loading
echo "Test 1: Optimized Events Query\n";
echo str_repeat("-", 50) . "\n";

$user = User::first();
if ($user) {
    $startTime = microtime(true);
    
    $events = Event::with([
            'host:id,name,email',
            'members:id,name,email',
            'images:id,event_id,image_path,original_filename,order'
        ])
        ->withCount([
            'rescheduleRequests as has_pending_reschedule_requests' => function ($query) {
                $query->where('status', 'pending');
            }
        ])
        ->where(function ($query) use ($user) {
            $query->where('host_id', $user->id)
                ->orWhereHas('members', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
        })
        ->orderBy('date')
        ->orderBy('time')
        ->get();
    
    $endTime = microtime(true);
    $executionTime = ($endTime - $startTime) * 1000;
    
    echo "Events found: " . $events->count() . "\n";
    echo "Execution time: " . number_format($executionTime, 2) . " ms\n";
    echo "Queries executed: " . count(DB::getQueryLog()) . "\n\n";
}

// Clear query log
DB::flushQueryLog();

// Test 2: Event Requests query with optimized eager loading
echo "Test 2: Optimized Event Requests Query\n";
echo str_repeat("-", 50) . "\n";

$startTime = microtime(true);

$requests = EventRequest::with([
        'requester:id,name,email,role,department',
        'reviewer:id,name,email,role',
        'deanApprover:id,name,email,role',
        'chairApprover:id,name,email,role'
    ])
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

$endTime = microtime(true);
$executionTime = ($endTime - $startTime) * 1000;

echo "Requests found: " . $requests->count() . "\n";
echo "Execution time: " . number_format($executionTime, 2) . " ms\n";
echo "Queries executed: " . count(DB::getQueryLog()) . "\n\n";

// Clear query log
DB::flushQueryLog();

// Test 3: Check indexes
echo "Test 3: Verify Indexes\n";
echo str_repeat("-", 50) . "\n";

$tables = ['events', 'event_requests', 'event_user', 'default_events'];

foreach ($tables as $table) {
    $indexes = DB::select("SHOW INDEX FROM {$table}");
    $indexNames = array_unique(array_column($indexes, 'Key_name'));
    echo "{$table}: " . count($indexNames) . " indexes\n";
    
    // Show index names
    foreach ($indexNames as $indexName) {
        if ($indexName !== 'PRIMARY') {
            echo "  - {$indexName}\n";
        }
    }
    echo "\n";
}

echo "\n=== Performance Test Complete ===\n";
