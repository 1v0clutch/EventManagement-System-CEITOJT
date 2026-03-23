<?php

/**
 * Directly migrate created events from default_events to default_event_dates
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== Migrate Created Events to default_event_dates ===\n\n";

// Helper function to determine semester
function getSemesterFromMonth($month) {
    if (in_array($month, [9, 10, 11, 12, 1])) {
        return 1; // First Semester
    }
    if (in_array($month, [2, 3, 4, 5, 6])) {
        return 2; // Second Semester
    }
    return 3; // Mid-Year
}

// 1. Ensure semester column exists
echo "1. Checking table structure...\n";
if (!Schema::hasColumn('default_event_dates', 'semester')) {
    echo "   Adding semester column...\n";
    Schema::table('default_event_dates', function ($table) {
        $table->integer('semester')->after('school_year')->nullable();
        $table->index(['school_year', 'semester']);
    });
    echo "   ✓ Semester column added\n\n";
} else {
    echo "   ✓ Semester column exists\n\n";
}

// 2. Find created events in default_events
echo "2. Finding created events in default_events table...\n";
$createdEvents = DB::table('default_events')
    ->whereNotNull('date')
    ->whereNotNull('school_year')
    ->get();

echo "   Found {$createdEvents->count()} created events\n\n";

if ($createdEvents->count() === 0) {
    echo "✓ No events to migrate. System is already clean!\n";
    exit(0);
}

// 3. Show what will be migrated
echo "3. Events to migrate:\n";
foreach ($createdEvents as $event) {
    $semester = getSemesterFromMonth($event->month);
    $semesterName = match($semester) {
        1 => 'First Semester',
        2 => 'Second Semester',
        3 => 'Mid-Year',
    };
    echo "   ID {$event->id}: {$event->name}\n";
    echo "      Date: {$event->date}" . ($event->end_date ? " to {$event->end_date}" : "") . "\n";
    echo "      School Year: {$event->school_year} | Semester: {$semesterName}\n\n";
}

// 4. Migrate each event
echo "4. Migrating events...\n";
$migrated = 0;
$skipped = 0;

foreach ($createdEvents as $event) {
    $semester = getSemesterFromMonth($event->month);
    
    // Check if already exists in default_event_dates
    $exists = DB::table('default_event_dates')
        ->where('default_event_id', $event->id)
        ->where('school_year', $event->school_year)
        ->exists();
    
    if ($exists) {
        echo "   ⊘ Skipped ID {$event->id} (already in default_event_dates)\n";
        $skipped++;
        continue;
    }
    
    // Insert into default_event_dates
    try {
        DB::table('default_event_dates')->insert([
            'default_event_id' => $event->id,
            'school_year' => $event->school_year,
            'semester' => $semester,
            'date' => $event->date,
            'end_date' => $event->end_date,
            'month' => $event->month,
            'created_by' => null,
            'created_at' => $event->created_at ?? now(),
            'updated_at' => $event->updated_at ?? now(),
        ]);
        
        echo "   ✓ Migrated ID {$event->id}: {$event->name}\n";
        $migrated++;
    } catch (Exception $e) {
        echo "   ✗ Error migrating ID {$event->id}: " . $e->getMessage() . "\n";
    }
}

echo "\n5. Cleaning up default_events table...\n";
// Remove date, end_date, school_year from default_events to convert back to templates
$updated = DB::table('default_events')
    ->whereNotNull('date')
    ->update([
        'date' => null,
        'end_date' => null,
        'school_year' => null,
    ]);

echo "   ✓ Cleaned {$updated} events (converted back to templates)\n\n";

// 6. Summary
echo "=== Migration Complete! ===\n\n";
echo "Summary:\n";
echo "   Migrated: {$migrated} events\n";
echo "   Skipped: {$skipped} events\n";
echo "   Total in default_event_dates: " . DB::table('default_event_dates')->count() . "\n";
echo "   Templates in default_events: " . DB::table('default_events')->whereNull('school_year')->count() . "\n\n";

echo "✓ Your created events are now in the default_event_dates table!\n";
