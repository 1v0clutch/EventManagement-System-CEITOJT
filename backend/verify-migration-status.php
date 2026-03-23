<?php

/**
 * Verify the migration status - show current state of both tables
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== Migration Status Check ===\n\n";

// Check default_events table
echo "1. DEFAULT_EVENTS TABLE (Should contain only templates)\n";
echo "   " . str_repeat("-", 70) . "\n";

$totalInDefaultEvents = DB::table('default_events')->count();
$withDates = DB::table('default_events')->whereNotNull('date')->count();
$withoutDates = DB::table('default_events')->whereNull('date')->count();

echo "   Total events: {$totalInDefaultEvents}\n";
echo "   With dates (SHOULD BE 0): {$withDates}\n";
echo "   Without dates (templates): {$withoutDates}\n\n";

if ($withDates > 0) {
    echo "   ⚠ WARNING: Found {$withDates} events with dates in default_events!\n";
    echo "   These should be migrated to default_event_dates table.\n\n";
    
    echo "   Examples:\n";
    $examples = DB::table('default_events')
        ->whereNotNull('date')
        ->limit(3)
        ->get(['id', 'name', 'date', 'school_year']);
    
    foreach ($examples as $ex) {
        echo "      ID {$ex->id}: {$ex->name} ({$ex->date}, {$ex->school_year})\n";
    }
    echo "\n";
} else {
    echo "   ✓ GOOD: No events with dates. All are templates.\n\n";
}

// Check default_event_dates table
echo "2. DEFAULT_EVENT_DATES TABLE (Should contain created events)\n";
echo "   " . str_repeat("-", 70) . "\n";

$totalInDates = DB::table('default_event_dates')->count();
echo "   Total created events: {$totalInDates}\n\n";

if ($totalInDates === 0) {
    echo "   ⚠ WARNING: No events in default_event_dates!\n";
    echo "   You need to migrate created events from default_events.\n\n";
} else {
    echo "   ✓ GOOD: Found {$totalInDates} created events.\n\n";
    
    // Show breakdown by school year
    echo "   Breakdown by School Year:\n";
    $byYear = DB::table('default_event_dates')
        ->select('school_year', DB::raw('COUNT(*) as count'))
        ->groupBy('school_year')
        ->orderBy('school_year')
        ->get();
    
    foreach ($byYear as $year) {
        echo "      {$year->school_year}: {$year->count} events\n";
    }
    echo "\n";
    
    // Show breakdown by semester if column exists
    if (Schema::hasColumn('default_event_dates', 'semester')) {
        echo "   Breakdown by Semester:\n";
        $bySemester = DB::table('default_event_dates')
            ->select('semester', DB::raw('COUNT(*) as count'))
            ->groupBy('semester')
            ->orderBy('semester')
            ->get();
        
        foreach ($bySemester as $sem) {
            $semName = match($sem->semester) {
                1 => 'First Semester',
                2 => 'Second Semester',
                3 => 'Mid-Year',
                default => 'Unknown',
            };
            echo "      {$semName}: {$sem->count} events\n";
        }
        echo "\n";
    }
    
    // Show some examples
    echo "   Examples:\n";
    $examples = DB::table('default_event_dates as ded')
        ->join('default_events as de', 'ded.default_event_id', '=', 'de.id')
        ->select('ded.id', 'de.name', 'ded.date', 'ded.school_year', 'ded.semester')
        ->limit(5)
        ->get();
    
    foreach ($examples as $ex) {
        $semName = match($ex->semester ?? 0) {
            1 => 'First Sem',
            2 => 'Second Sem',
            3 => 'Mid-Year',
            default => 'N/A',
        };
        echo "      ID {$ex->id}: {$ex->name} ({$ex->date}, {$ex->school_year}, {$semName})\n";
    }
    echo "\n";
}

// Final verdict
echo "3. MIGRATION STATUS\n";
echo "   " . str_repeat("-", 70) . "\n";

if ($withDates === 0 && $totalInDates > 0) {
    echo "   ✓✓✓ PERFECT! Migration is complete.\n";
    echo "   - default_events contains only templates\n";
    echo "   - default_event_dates contains all created events\n";
} elseif ($withDates > 0 && $totalInDates === 0) {
    echo "   ✗✗✗ MIGRATION NEEDED!\n";
    echo "   Run: php migrate-created-events-now.php\n";
    echo "   Or:  RUN_MIGRATE_CREATED_EVENTS.bat\n";
} elseif ($withDates > 0 && $totalInDates > 0) {
    echo "   ⚠⚠⚠ PARTIAL MIGRATION\n";
    echo "   Some events are in both tables. Run migration again.\n";
} else {
    echo "   ⚠ No data in either table. System needs to be populated.\n";
}

echo "\n=== Check Complete ===\n";
