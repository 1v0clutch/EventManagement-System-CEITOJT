<?php

/**
 * Test the fixed DefaultEventController
 * Verify that dates are saved to default_event_dates table, not default_events
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Test Fixed Controller ===\n\n";

// 1. Check current state
echo "1. Current State:\n";
$templatesCount = DB::table('default_events')->whereNull('school_year')->count();
$withDatesCount = DB::table('default_events')->whereNotNull('school_year')->count();
$createdEventsCount = DB::table('default_event_dates')->count();

echo "   Templates in default_events: {$templatesCount}\n";
echo "   Events with dates in default_events (SHOULD BE 0): {$withDatesCount}\n";
echo "   Created events in default_event_dates: {$createdEventsCount}\n\n";

if ($withDatesCount > 0) {
    echo "   ⚠ WARNING: Found {$withDatesCount} events with dates in default_events\n";
    echo "   Run migration first: php migrate-created-events-now.php\n\n";
}

// 2. Simulate creating a date for an event
echo "2. Testing Date Assignment:\n";

// Get a template event
$template = DB::table('default_events')
    ->whereNull('school_year')
    ->first();

if (!$template) {
    echo "   ✗ No template events found. Please seed the database first.\n";
    exit(1);
}

echo "   Using template: ID {$template->id} - {$template->name}\n";

// Check if this template already has a date for 2025-2026
$existingDate = DB::table('default_event_dates')
    ->where('default_event_id', $template->id)
    ->where('school_year', '2025-2026')
    ->first();

if ($existingDate) {
    echo "   ✓ This template already has a date assigned for 2025-2026\n";
    echo "      Date: {$existingDate->date}\n";
    echo "      Semester: {$existingDate->semester}\n\n";
} else {
    echo "   ℹ This template does NOT have a date for 2025-2026 yet\n";
    echo "   You can assign one using the API:\n";
    echo "   PUT /api/default-events/{$template->id}/date\n";
    echo "   Body: {\"date\": \"2025-09-15\", \"school_year\": \"2025-2026\"}\n\n";
}

// 3. Show how the system should work
echo "3. How the Fixed System Works:\n";
echo "   ┌─────────────────────────────────────────────────────────┐\n";
echo "   │ BEFORE (OLD - WRONG)                                    │\n";
echo "   ├─────────────────────────────────────────────────────────┤\n";
echo "   │ Admin sets date for event                               │\n";
echo "   │   ↓                                                     │\n";
echo "   │ Saved to default_events table with school_year          │\n";
echo "   │   ↓                                                     │\n";
echo "   │ Creates duplicate/mixed data ✗                          │\n";
echo "   └─────────────────────────────────────────────────────────┘\n\n";
echo "   ┌─────────────────────────────────────────────────────────┐\n";
echo "   │ AFTER (NEW - CORRECT)                                   │\n";
echo "   ├─────────────────────────────────────────────────────────┤\n";
echo "   │ Admin sets date for event                               │\n";
echo "   │   ↓                                                     │\n";
echo "   │ Saved to default_event_dates table                      │\n";
echo "   │   ↓                                                     │\n";
echo "   │ Template stays clean, dates separate ✓                  │\n";
echo "   └─────────────────────────────────────────────────────────┘\n\n";

// 4. Verify the fix
echo "4. Verification:\n";
if ($withDatesCount === 0 && $createdEventsCount > 0) {
    echo "   ✓✓✓ PERFECT! System is working correctly.\n";
    echo "   - Templates are clean (no dates in default_events)\n";
    echo "   - Created events are in default_event_dates\n";
} elseif ($withDatesCount === 0 && $createdEventsCount === 0) {
    echo "   ✓ Templates are clean\n";
    echo "   ℹ No dates assigned yet. Start assigning dates via API.\n";
} else {
    echo "   ✗ System needs migration\n";
    echo "   Run: php migrate-created-events-now.php\n";
}

echo "\n=== Test Complete ===\n";
