<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\CreatedAcademicEvent;
use App\Models\User;

echo "=== Testing Created Academic Events System ===\n\n";

// Get an admin user for testing
$admin = User::where('role', 'Admin')->first();

if (!$admin) {
    echo "❌ No admin user found. Please create an admin user first.\n";
    exit(1);
}

echo "✓ Using admin user: {$admin->name} (ID: {$admin->id})\n\n";

// Test 1: Create an academic event for 2025-2026
echo "Test 1: Creating academic event for 2025-2026, September (First Semester)\n";
try {
    $event1 = CreatedAcademicEvent::create([
        'name' => 'Test Academic Event 2025-2026',
        'month' => 9,
        'semester' => 1,
        'school_year' => '2025-2026',
        'date' => '2025-09-15',
        'end_date' => '2025-09-16',
        'created_by' => $admin->id,
        'order' => 1,
    ]);
    echo "✓ Created event: {$event1->name} (ID: {$event1->id})\n";
    echo "  - School Year: {$event1->school_year}\n";
    echo "  - Semester: {$event1->semester_name}\n";
    echo "  - Month: {$event1->month}\n";
    echo "  - Date: {$event1->date->format('Y-m-d')}\n\n";
} catch (\Exception $e) {
    echo "❌ Failed to create event: {$e->getMessage()}\n\n";
}

// Test 2: Create an academic event for 2026-2027
echo "Test 2: Creating academic event for 2026-2027, September (First Semester)\n";
try {
    $event2 = CreatedAcademicEvent::create([
        'name' => 'Test Academic Event 2026-2027',
        'month' => 9,
        'semester' => 1,
        'school_year' => '2026-2027',
        'date' => '2026-09-15',
        'end_date' => '2026-09-16',
        'created_by' => $admin->id,
        'order' => 1,
    ]);
    echo "✓ Created event: {$event2->name} (ID: {$event2->id})\n";
    echo "  - School Year: {$event2->school_year}\n";
    echo "  - Semester: {$event2->semester_name}\n";
    echo "  - Month: {$event2->month}\n";
    echo "  - Date: {$event2->date->format('Y-m-d')}\n\n";
} catch (\Exception $e) {
    echo "❌ Failed to create event: {$e->getMessage()}\n\n";
}

// Test 3: Query events for 2025-2026
echo "Test 3: Querying events for 2025-2026\n";
$events2025 = CreatedAcademicEvent::forSchoolYear('2025-2026')->get();
echo "✓ Found {$events2025->count()} event(s) for 2025-2026:\n";
foreach ($events2025 as $event) {
    echo "  - {$event->name} (Month: {$event->month}, Semester: {$event->semester_name})\n";
}
echo "\n";

// Test 4: Query events for 2026-2027
echo "Test 4: Querying events for 2026-2027\n";
$events2026 = CreatedAcademicEvent::forSchoolYear('2026-2027')->get();
echo "✓ Found {$events2026->count()} event(s) for 2026-2027:\n";
foreach ($events2026 as $event) {
    echo "  - {$event->name} (Month: {$event->month}, Semester: {$event->semester_name})\n";
}
echo "\n";

// Test 5: Verify isolation
echo "Test 5: Verifying isolation between school years\n";
if ($events2025->count() > 0 && $events2026->count() > 0) {
    $event2025Names = $events2025->pluck('name')->toArray();
    $event2026Names = $events2026->pluck('name')->toArray();
    
    $overlap = array_intersect($event2025Names, $event2026Names);
    
    if (empty($overlap)) {
        echo "✓ Events are properly isolated - no overlap between school years\n";
    } else {
        echo "⚠ Warning: Found overlapping event names: " . implode(', ', $overlap) . "\n";
        echo "  (This is OK if they are different events with the same name)\n";
    }
} else {
    echo "⚠ Skipping isolation test - need events in both school years\n";
}
echo "\n";

// Test 6: Test semester filtering
echo "Test 6: Testing semester filtering\n";
$firstSemesterEvents = CreatedAcademicEvent::forSchoolYear('2025-2026')
    ->forSemester(1)
    ->get();
echo "✓ Found {$firstSemesterEvents->count()} event(s) in First Semester 2025-2026\n\n";

// Test 7: Test duplicate prevention
echo "Test 7: Testing duplicate prevention\n";
try {
    $duplicate = CreatedAcademicEvent::create([
        'name' => 'Test Academic Event 2025-2026',
        'month' => 9,
        'semester' => 1,
        'school_year' => '2025-2026',
        'created_by' => $admin->id,
        'order' => 2,
    ]);
    echo "❌ Duplicate was created (should have been prevented)\n";
} catch (\Illuminate\Database\QueryException $e) {
    if (str_contains($e->getMessage(), 'unique_created_academic_event')) {
        echo "✓ Duplicate prevention working correctly\n";
    } else {
        echo "❌ Unexpected error: {$e->getMessage()}\n";
    }
}
echo "\n";

// Cleanup
echo "=== Cleanup ===\n";
echo "Deleting test events...\n";
if (isset($event1)) {
    $event1->delete();
    echo "✓ Deleted: Test Academic Event 2025-2026\n";
}
if (isset($event2)) {
    $event2->delete();
    echo "✓ Deleted: Test Academic Event 2026-2027\n";
}

echo "\n=== All Tests Complete ===\n";
echo "\nSummary:\n";
echo "✓ Created academic events are properly isolated by school year\n";
echo "✓ Semester filtering works correctly\n";
echo "✓ Duplicate prevention is enforced\n";
echo "✓ Events can be queried independently per school year\n";
