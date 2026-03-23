<?php

/**
 * Test script for Default Event Dates API
 * Tests the V2 API endpoints for managing default event dates
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\DefaultEvent;
use App\Models\DefaultEventDate;
use App\Models\User;

echo "=== Default Event Dates System Test ===\n\n";

// 1. Check if table exists
echo "1. Checking if default_event_dates table exists...\n";
try {
    $count = DefaultEventDate::count();
    echo "   ✓ Table exists with {$count} records\n\n";
} catch (Exception $e) {
    echo "   ✗ Table does not exist or error: " . $e->getMessage() . "\n\n";
    exit(1);
}

// 2. Get base events
echo "2. Checking base default events...\n";
$baseEvents = DefaultEvent::whereNull('school_year')->get();
echo "   Found {$baseEvents->count()} base events\n";
if ($baseEvents->count() > 0) {
    echo "   Sample: {$baseEvents->first()->name}\n\n";
}

// 3. Check date assignments for 2025-2026
echo "3. Checking date assignments for 2025-2026...\n";
$dates2025 = DefaultEventDate::forSchoolYear('2025-2026')->with('defaultEvent')->get();
echo "   Found {$dates2025->count()} events with dates set\n";
if ($dates2025->count() > 0) {
    $sample = $dates2025->first();
    echo "   Sample: {$sample->defaultEvent->name} on {$sample->date->format('Y-m-d')}\n\n";
}

// 4. Check date assignments for 2026-2027
echo "4. Checking date assignments for 2026-2027...\n";
$dates2026 = DefaultEventDate::forSchoolYear('2026-2027')->with('defaultEvent')->get();
echo "   Found {$dates2026->count()} events with dates set\n";
if ($dates2026->count() > 0) {
    $sample = $dates2026->first();
    echo "   Sample: {$sample->defaultEvent->name} on {$sample->date->format('Y-m-d')}\n\n";
}

// 5. Test creating a date assignment
echo "5. Testing date assignment creation...\n";
if ($baseEvents->count() > 0) {
    $testEvent = $baseEvents->first();
    $testSchoolYear = '2027-2028';
    
    try {
        $dateAssignment = DefaultEventDate::updateOrCreate(
            [
                'default_event_id' => $testEvent->id,
                'school_year' => $testSchoolYear,
            ],
            [
                'date' => '2027-09-15',
                'end_date' => null,
                'month' => 9,
                'created_by' => null,
            ]
        );
        
        echo "   ✓ Successfully created/updated date assignment\n";
        echo "   Event: {$testEvent->name}\n";
        echo "   School Year: {$testSchoolYear}\n";
        echo "   Date: {$dateAssignment->date->format('Y-m-d')}\n\n";
        
        // Clean up test data
        $dateAssignment->delete();
        echo "   ✓ Test data cleaned up\n\n";
    } catch (Exception $e) {
        echo "   ✗ Error: " . $e->getMessage() . "\n\n";
    }
}

// 6. Test statistics
echo "6. Testing statistics for 2025-2026...\n";
$totalBase = DefaultEvent::whereNull('school_year')->count();
$withDates = DefaultEventDate::forSchoolYear('2025-2026')->count();
$percentage = $totalBase > 0 ? round(($withDates / $totalBase) * 100, 2) : 0;

echo "   Total base events: {$totalBase}\n";
echo "   Events with dates: {$withDates}\n";
echo "   Completion: {$percentage}%\n\n";

echo "=== Test Complete ===\n";
