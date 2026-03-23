<?php

/**
 * Test Dashboard Default Events Display
 * Verify that default events from default_event_dates table appear on dashboard
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Dashboard Default Events Test ===\n\n";

// 1. Check current school year
$now = new \DateTime();
$currentYear = $now->format('Y');
$currentMonth = (int)$now->format('m');
$schoolYear = $currentMonth >= 9 
    ? "{$currentYear}-" . ($currentYear + 1)
    : ($currentYear - 1) . "-{$currentYear}";

echo "1. Current School Year: {$schoolYear}\n\n";

// 2. Check default_event_dates table
echo "2. Checking default_event_dates table:\n";
$createdEvents = DB::table('default_event_dates')
    ->where('school_year', $schoolYear)
    ->count();

echo "   Events for {$schoolYear}: {$createdEvents}\n";

if ($createdEvents === 0) {
    echo "   ⚠ No events found for current school year!\n";
    echo "   Create some events first or run migration.\n\n";
} else {
    echo "   ✓ Found {$createdEvents} events\n\n";
    
    // Show some examples
    echo "   Examples:\n";
    $examples = DB::table('default_event_dates as ded')
        ->join('default_events as de', 'ded.default_event_id', '=', 'de.id')
        ->where('ded.school_year', $schoolYear)
        ->select('de.name', 'ded.date', 'ded.end_date', 'ded.semester')
        ->limit(5)
        ->get();
    
    foreach ($examples as $ex) {
        $semName = match($ex->semester ?? 0) {
            1 => 'First Sem',
            2 => 'Second Sem',
            3 => 'Mid-Year',
            default => 'N/A',
        };
        echo "      - {$ex->name} ({$ex->date}, {$semName})\n";
    }
    echo "\n";
}

// 3. Check what Dashboard API would return
echo "3. Simulating Dashboard API Response:\n";

$defaultEventDates = DB::table('default_event_dates as ded')
    ->join('default_events as de', 'ded.default_event_id', '=', 'de.id')
    ->where('ded.school_year', $schoolYear)
    ->select(
        'ded.id',
        'ded.default_event_id',
        'de.name',
        'ded.date',
        'ded.end_date',
        'ded.school_year',
        'ded.semester'
    )
    ->orderBy('ded.date')
    ->limit(10)
    ->get();

if ($defaultEventDates->count() === 0) {
    echo "   ✗ Dashboard would return NO default events\n";
    echo "   This is why events don't show on calendar!\n\n";
} else {
    echo "   ✓ Dashboard would return {$defaultEventDates->count()} events:\n\n";
    
    foreach ($defaultEventDates as $event) {
        echo "   {\n";
        echo "      id: 'default-{$event->default_event_id}',\n";
        echo "      name: '{$event->name}',\n";
        echo "      date: '{$event->date}',\n";
        echo "      end_date: " . ($event->end_date ? "'{$event->end_date}'" : "null") . ",\n";
        echo "      school_year: '{$event->school_year}',\n";
        echo "      semester: {$event->semester}\n";
        echo "   }\n\n";
    }
}

// 4. Check old table (should be empty)
echo "4. Checking old default_events table:\n";
$oldEvents = DB::table('default_events')
    ->whereNotNull('date')
    ->whereNotNull('school_year')
    ->count();

if ($oldEvents > 0) {
    echo "   ⚠ Found {$oldEvents} events with dates in default_events\n";
    echo "   These should be migrated to default_event_dates!\n";
    echo "   Run: php migrate-created-events-now.php\n\n";
} else {
    echo "   ✓ No events with dates in default_events (correct!)\n\n";
}

// 5. Final verdict
echo "5. Dashboard Display Status:\n";
if ($createdEvents > 0 && $oldEvents === 0) {
    echo "   ✓✓✓ READY! Events should display on dashboard.\n";
    echo "   - Events are in default_event_dates table\n";
    echo "   - DashboardController is updated to fetch from correct table\n";
    echo "   - Frontend should now show events on calendar\n";
} elseif ($createdEvents === 0) {
    echo "   ⚠ No events to display\n";
    echo "   Create events via Admin > Academic Calendar\n";
} else {
    echo "   ✗ Migration needed\n";
    echo "   Run: php migrate-created-events-now.php\n";
}

echo "\n=== Test Complete ===\n";
