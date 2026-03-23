<?php

/**
 * Check current state and migrate created events to default_event_dates table
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Check and Migrate Created Events ===\n\n";

// 1. Check current state of default_events
echo "1. Checking default_events table...\n";
$totalEvents = DB::table('default_events')->count();
$eventsWithDates = DB::table('default_events')
    ->whereNotNull('date')
    ->whereNotNull('school_year')
    ->count();
$eventsWithoutDates = DB::table('default_events')
    ->whereNull('date')
    ->orWhereNull('school_year')
    ->count();

echo "   Total events in default_events: {$totalEvents}\n";
echo "   Events WITH dates (created events): {$eventsWithDates}\n";
echo "   Events WITHOUT dates (templates): {$eventsWithoutDates}\n\n";

// 2. Show some examples of created events
if ($eventsWithDates > 0) {
    echo "2. Examples of created events currently in default_events:\n";
    $examples = DB::table('default_events')
        ->whereNotNull('date')
        ->whereNotNull('school_year')
        ->limit(5)
        ->get();
    
    foreach ($examples as $event) {
        echo "   ID {$event->id}: {$event->name}\n";
        echo "      Date: {$event->date}";
        if ($event->end_date) {
            echo " to {$event->end_date}";
        }
        echo "\n";
        echo "      School Year: {$event->school_year}\n";
        echo "      Month: {$event->month}\n\n";
    }
}

// 3. Check default_event_dates table
echo "3. Checking default_event_dates table...\n";
$datesCount = DB::table('default_event_dates')->count();
echo "   Current entries in default_event_dates: {$datesCount}\n\n";

// 4. Ask for confirmation
if ($eventsWithDates > 0) {
    echo "=== MIGRATION NEEDED ===\n";
    echo "Found {$eventsWithDates} created events that should be moved to default_event_dates table.\n\n";
    echo "This migration will:\n";
    echo "1. Copy created events from default_events to default_event_dates\n";
    echo "2. Remove date/school_year from default_events (convert back to templates)\n";
    echo "3. Add semester field automatically based on month\n\n";
    
    echo "Do you want to proceed? (yes/no): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    $answer = trim(strtolower($line));
    fclose($handle);
    
    if ($answer === 'yes' || $answer === 'y') {
        echo "\nRunning migration...\n\n";
        
        // Run the migration
        $exitCode = 0;
        passthru('php artisan migrate --path=database/migrations/2026_03_21_110000_migrate_created_events_to_dates_table.php', $exitCode);
        
        if ($exitCode === 0) {
            echo "\n=== Migration Complete! ===\n\n";
            
            // Show results
            echo "Results:\n";
            $newDatesCount = DB::table('default_event_dates')->count();
            $remainingWithDates = DB::table('default_events')->whereNotNull('date')->count();
            
            echo "   Entries in default_event_dates: {$newDatesCount}\n";
            echo "   Events with dates in default_events: {$remainingWithDates}\n\n";
            
            if ($remainingWithDates === 0) {
                echo "✓ Success! All created events moved to default_event_dates\n";
            } else {
                echo "⚠ Warning: Some events still have dates in default_events\n";
            }
        } else {
            echo "\n✗ Migration failed with exit code {$exitCode}\n";
        }
    } else {
        echo "\nMigration cancelled.\n";
    }
} else {
    echo "✓ No migration needed. All events are already in the correct tables.\n";
}

echo "\n=== Done ===\n";
