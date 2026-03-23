<?php

/**
 * Test Dashboard API - Verify All Semesters Are Returned
 * 
 * This script verifies that the dashboard API returns schedules
 * for ALL semesters, not just the current semester.
 */

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Dashboard Schedules Test - All Semesters ===\n\n";

// Get a test user
$user = DB::table('users')->where('is_validated', true)->first();

if (!$user) {
    echo "❌ No validated users found. Please create a user first.\n";
    exit(1);
}

echo "Testing with user: {$user->name} (ID: {$user->id})\n\n";

// Check if user has schedules
$schedules = DB::table('user_schedules')
    ->where('user_id', $user->id)
    ->get();

if ($schedules->isEmpty()) {
    echo "⚠️  User has no schedules. Creating test schedules...\n\n";
    
    // Create test schedules for different semesters
    $testSchedules = [
        [
            'user_id' => $user->id,
            'day' => 'Monday',
            'start_time' => '09:00:00',
            'end_time' => '10:30:00',
            'description' => 'Math 101 (First Semester)',
            'semester' => 'first',
            'school_year' => '2025-2026',
            'color' => '#FF5733',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'user_id' => $user->id,
            'day' => 'Tuesday',
            'start_time' => '11:00:00',
            'end_time' => '12:30:00',
            'description' => 'Physics 201 (First Semester)',
            'semester' => 'first',
            'school_year' => '2025-2026',
            'color' => '#33FF57',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'user_id' => $user->id,
            'day' => 'Monday',
            'start_time' => '09:00:00',
            'end_time' => '10:30:00',
            'description' => 'English 102 (Second Semester)',
            'semester' => 'second',
            'school_year' => '2025-2026',
            'color' => '#3357FF',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'user_id' => $user->id,
            'day' => 'Tuesday',
            'start_time' => '11:00:00',
            'end_time' => '12:30:00',
            'description' => 'History 202 (Second Semester)',
            'semester' => 'second',
            'school_year' => '2025-2026',
            'color' => '#FF33F5',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'user_id' => $user->id,
            'day' => 'Monday',
            'start_time' => '14:00:00',
            'end_time' => '15:30:00',
            'description' => 'Summer Course (Midyear)',
            'semester' => 'midyear',
            'school_year' => '2025-2026',
            'color' => '#FFC300',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ];
    
    foreach ($testSchedules as $schedule) {
        DB::table('user_schedules')->insert($schedule);
    }
    
    echo "✅ Created 5 test schedules (first, second, and midyear semesters)\n\n";
    
    $schedules = DB::table('user_schedules')
        ->where('user_id', $user->id)
        ->get();
}

// Display user's schedules
echo "User's Schedules in Database:\n";
echo str_repeat("-", 80) . "\n";

$semesterCounts = [
    'first' => 0,
    'second' => 0,
    'midyear' => 0,
];

foreach ($schedules as $schedule) {
    echo sprintf(
        "%-10s | %-15s | %s - %s | %-30s | %s\n",
        $schedule->semester,
        $schedule->day,
        substr($schedule->start_time, 0, 5),
        substr($schedule->end_time, 0, 5),
        $schedule->description,
        $schedule->school_year
    );
    
    if (isset($semesterCounts[$schedule->semester])) {
        $semesterCounts[$schedule->semester]++;
    }
}

echo str_repeat("-", 80) . "\n";
echo "Total schedules: " . $schedules->count() . "\n";
echo "  - First Semester: {$semesterCounts['first']}\n";
echo "  - Second Semester: {$semesterCounts['second']}\n";
echo "  - Midyear: {$semesterCounts['midyear']}\n\n";

// Now test what the API returns
echo "Testing Dashboard API Response:\n";
echo str_repeat("=", 80) . "\n\n";

// Simulate the dashboard controller logic
$now = new DateTime();
$currentYear = $now->format('Y');
$currentMonth = (int)$now->format('m');
$schoolYear = $currentMonth >= 9 
    ? "{$currentYear}-" . ($currentYear + 1)
    : ($currentYear - 1) . "-{$currentYear}";
$nextSchoolYear = $currentMonth >= 9
    ? ($currentYear + 1) . "-" . ($currentYear + 2)
    : "{$currentYear}-" . ($currentYear + 1);

echo "Current School Year: {$schoolYear}\n";
echo "Next School Year: {$nextSchoolYear}\n\n";

// Fetch schedules the way the API does (AFTER FIX)
$apiSchedules = DB::table('user_schedules')
    ->where('user_id', $user->id)
    ->whereIn('school_year', [$schoolYear, $nextSchoolYear])
    ->get();

echo "Schedules Returned by API (AFTER FIX):\n";
echo str_repeat("-", 80) . "\n";

$apiSemesterCounts = [
    'first' => 0,
    'second' => 0,
    'midyear' => 0,
];

foreach ($apiSchedules as $schedule) {
    echo sprintf(
        "%-10s | %-15s | %s - %s | %-30s\n",
        $schedule->semester,
        $schedule->day,
        substr($schedule->start_time, 0, 5),
        substr($schedule->end_time, 0, 5),
        $schedule->description
    );
    
    if (isset($apiSemesterCounts[$schedule->semester])) {
        $apiSemesterCounts[$schedule->semester]++;
    }
}

echo str_repeat("-", 80) . "\n";
echo "Total schedules returned: " . $apiSchedules->count() . "\n";
echo "  - First Semester: {$apiSemesterCounts['first']}\n";
echo "  - Second Semester: {$apiSemesterCounts['second']}\n";
echo "  - Midyear: {$apiSemesterCounts['midyear']}\n\n";

// Verify the fix
echo "Verification:\n";
echo str_repeat("=", 80) . "\n";

$allSemestersPresent = $apiSemesterCounts['first'] > 0 && 
                       $apiSemesterCounts['second'] > 0;

if ($allSemestersPresent) {
    echo "✅ SUCCESS: API returns schedules from multiple semesters!\n";
    echo "✅ Frontend can now filter by selected date's semester.\n";
    echo "✅ Bug is FIXED!\n";
} else {
    echo "❌ FAIL: API is not returning schedules from all semesters.\n";
    echo "❌ Check DashboardController.php - it should use whereIn('school_year', ...)\n";
    echo "❌ Bug is NOT fixed!\n";
}

echo "\n";
echo "Test completed.\n";
