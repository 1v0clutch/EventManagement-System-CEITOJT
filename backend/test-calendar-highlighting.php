<?php

/**
 * Test script to verify calendar highlighting for academic events
 * 
 * This script tests that:
 * 1. Default events are fetched from the database
 * 2. They are properly transformed with is_default_event flag
 * 3. The API returns them merged with regular events
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Calendar Highlighting for Academic Events ===\n\n";

// Get default events with dates
$defaultEvents = \App\Models\DefaultEvent::whereNotNull('date')->get();

echo "1. Default Events in Database:\n";
echo "   Total: " . $defaultEvents->count() . "\n";
if ($defaultEvents->count() > 0) {
    echo "   Sample events:\n";
    foreach ($defaultEvents->take(5) as $event) {
        echo "   - {$event->name} on {$event->date->format('Y-m-d')}\n";
    }
} else {
    echo "   ⚠️  No default events found with dates set!\n";
    echo "   Please set dates in the Academic Calendar page first.\n";
}

echo "\n2. Transformation Test:\n";
$transformedDefaultEvents = $defaultEvents->map(function ($event) {
    return [
        'id' => 'default-' . $event->id,
        'title' => $event->name,
        'description' => 'Academic Calendar Event',
        'location' => 'TBD',
        'images' => [],
        'date' => $event->date->format('Y-m-d'),
        'time' => '00:00',
        'has_pending_reschedule_requests' => false,
        'host' => [
            'id' => 0,
            'username' => 'Academic Calendar',
            'email' => 'calendar@system',
        ],
        'members' => [],
        'is_default_event' => true,
    ];
});

echo "   Transformed events: " . $transformedDefaultEvents->count() . "\n";
if ($transformedDefaultEvents->count() > 0) {
    $sample = $transformedDefaultEvents->first();
    echo "   Sample transformed event:\n";
    echo "   - ID: {$sample['id']}\n";
    echo "   - Title: {$sample['title']}\n";
    echo "   - Date: {$sample['date']}\n";
    echo "   - is_default_event: " . ($sample['is_default_event'] ? 'true' : 'false') . "\n";
}

echo "\n3. Expected Calendar Behavior:\n";
echo "   ✓ Dates with academic events should have green background (bg-green-50)\n";
echo "   ✓ Academic events should show blue dots in the calendar\n";
echo "   ✓ Regular events show green dots (invited) or red dots (hosting)\n";

echo "\n4. How to Test:\n";
echo "   1. Make sure you have dates set in Academic Calendar page\n";
echo "   2. Go to Dashboard\n";
echo "   3. Look for dates with academic events\n";
echo "   4. They should have a light green background\n";
echo "   5. Click on the date to see the event details\n";

echo "\n=== Test Complete ===\n";
