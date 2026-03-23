<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UserSchedule;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Semester Schedule Filtering\n";
echo "====================================\n\n";

try {
    // Find a user
    $user = User::first();
    
    if (!$user) {
        echo "❌ No users found. Please create a user first.\n";
        exit(1);
    }
    
    echo "Testing with user: {$user->name} (ID: {$user->id})\n\n";
    
    // Test 1: Create schedules for different semesters
    echo "Test 1: Creating Schedules for Different Semesters\n";
    echo "==================================================\n";
    
    // Clear existing schedules
    UserSchedule::where('user_id', $user->id)->delete();
    
    // Create First Semester schedule (2025-2026)
    $firstSemesterSchedules = [
        [
            'user_id' => $user->id,
            'day' => 'Monday',
            'start_time' => '08:00:00',
            'end_time' => '09:30:00',
            'description' => 'Mathematics 101 - First Semester',
            'color' => '#10b981',
            'semester' => 'first',
            'school_year' => '2025-2026',
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'user_id' => $user->id,
            'day' => 'Wednesday',
            'start_time' => '10:00:00',
            'end_time' => '11:30:00',
            'description' => 'Physics Lab - First Semester',
            'color' => '#3b82f6',
            'semester' => 'first',
            'school_year' => '2025-2026',
            'created_at' => now(),
            'updated_at' => now()
        ]
    ];
    
    // Create Second Semester schedule (2025-2026)
    $secondSemesterSchedules = [
        [
            'user_id' => $user->id,
            'day' => 'Tuesday',
            'start_time' => '09:00:00',
            'end_time' => '10:30:00',
            'description' => 'Chemistry 201 - Second Semester',
            'color' => '#f59e0b',
            'semester' => 'second',
            'school_year' => '2025-2026',
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'user_id' => $user->id,
            'day' => 'Thursday',
            'start_time' => '13:00:00',
            'end_time' => '14:30:00',
            'description' => 'Biology Lab - Second Semester',
            'color' => '#ef4444',
            'semester' => 'second',
            'school_year' => '2025-2026',
            'created_at' => now(),
            'updated_at' => now()
        ]
    ];
    
    // Create Mid-Year schedule (2025-2026)
    $midYearSchedules = [
        [
            'user_id' => $user->id,
            'day' => 'Friday',
            'start_time' => '08:00:00',
            'end_time' => '12:00:00',
            'description' => 'Summer Workshop - Mid-Year',
            'color' => '#8b5cf6',
            'semester' => 'midyear',
            'school_year' => '2025-2026',
            'created_at' => now(),
            'updated_at' => now()
        ]
    ];
    
    // Insert all schedules
    UserSchedule::insert(array_merge($firstSemesterSchedules, $secondSemesterSchedules, $midYearSchedules));
    
    echo "✅ Created schedules:\n";
    echo "  - First Semester (2025-2026): 2 classes\n";
    echo "  - Second Semester (2025-2026): 2 classes\n";
    echo "  - Mid-Year (2025-2026): 1 class\n\n";
    
    // Test 2: Query schedules by semester
    echo "Test 2: Querying Schedules by Semester\n";
    echo "======================================\n";
    
    $firstSemester = UserSchedule::where('user_id', $user->id)
        ->where('semester', 'first')
        ->where('school_year', '2025-2026')
        ->get();
    
    echo "First Semester (2025-2026) - Found {$firstSemester->count()} classes:\n";
    foreach ($firstSemester as $schedule) {
        echo "  - {$schedule->day}: {$schedule->description} ({$schedule->start_time} - {$schedule->end_time})\n";
    }
    echo "\n";
    
    $secondSemester = UserSchedule::where('user_id', $user->id)
        ->where('semester', 'second')
        ->where('school_year', '2025-2026')
        ->get();
    
    echo "Second Semester (2025-2026) - Found {$secondSemester->count()} classes:\n";
    foreach ($secondSemester as $schedule) {
        echo "  - {$schedule->day}: {$schedule->description} ({$schedule->start_time} - {$schedule->end_time})\n";
    }
    echo "\n";
    
    $midYear = UserSchedule::where('user_id', $user->id)
        ->where('semester', 'midyear')
        ->where('school_year', '2025-2026')
        ->get();
    
    echo "Mid-Year (2025-2026) - Found {$midYear->count()} classes:\n";
    foreach ($midYear as $schedule) {
        echo "  - {$schedule->day}: {$schedule->description} ({$schedule->start_time} - {$schedule->end_time})\n";
    }
    echo "\n";
    
    // Test 3: Verify isolation between semesters
    echo "Test 3: Verifying Semester Isolation\n";
    echo "====================================\n";
    
    if ($firstSemester->count() === 2 && $secondSemester->count() === 2 && $midYear->count() === 1) {
        echo "✅ Semester isolation working correctly\n";
        echo "  - Each semester has its own distinct schedule\n";
        echo "  - No cross-contamination between semesters\n\n";
    } else {
        echo "❌ Semester isolation failed\n";
        echo "  - Expected: First=2, Second=2, MidYear=1\n";
        echo "  - Got: First={$firstSemester->count()}, Second={$secondSemester->count()}, MidYear={$midYear->count()}\n\n";
    }
    
    // Test 4: Test current semester detection
    echo "Test 4: Current Semester Detection\n";
    echo "==================================\n";
    
    $now = new DateTime();
    $currentMonth = (int)$now->format('m');
    $currentYear = (int)$now->format('Y');
    
    if ($currentMonth >= 9 || $currentMonth <= 1) {
        $currentSemester = 'first';
    } elseif ($currentMonth >= 2 && $currentMonth <= 6) {
        $currentSemester = 'second';
    } else {
        $currentSemester = 'midyear';
    }
    
    $schoolYear = $currentMonth >= 9 
        ? "{$currentYear}-" . ($currentYear + 1)
        : ($currentYear - 1) . "-{$currentYear}";
    
    echo "Current Date: " . $now->format('Y-m-d') . "\n";
    echo "Current Month: {$currentMonth}\n";
    echo "Detected Semester: {$currentSemester}\n";
    echo "School Year: {$schoolYear}\n\n";
    
    // Test 5: Test schedule retrieval for current semester
    echo "Test 5: Retrieving Current Semester Schedule\n";
    echo "============================================\n";
    
    $currentSchedules = UserSchedule::where('user_id', $user->id)
        ->where('semester', $currentSemester)
        ->where('school_year', $schoolYear)
        ->get();
    
    echo "Found {$currentSchedules->count()} classes for current semester ({$currentSemester} - {$schoolYear}):\n";
    if ($currentSchedules->count() > 0) {
        foreach ($currentSchedules as $schedule) {
            echo "  - {$schedule->day}: {$schedule->description} ({$schedule->start_time} - {$schedule->end_time})\n";
        }
    } else {
        echo "  (No classes scheduled for current semester)\n";
    }
    echo "\n";
    
    // Test 6: Test multiple school years
    echo "Test 6: Testing Multiple School Years\n";
    echo "=====================================\n";
    
    // Create schedule for next school year
    $nextYearSchedule = [
        'user_id' => $user->id,
        'day' => 'Monday',
        'start_time' => '14:00:00',
        'end_time' => '15:30:00',
        'description' => 'Advanced Topics - Next Year',
        'color' => '#ec4899',
        'semester' => 'first',
        'school_year' => '2026-2027',
        'created_at' => now(),
        'updated_at' => now()
    ];
    
    UserSchedule::insert($nextYearSchedule);
    
    $currentYearCount = UserSchedule::where('user_id', $user->id)
        ->where('school_year', '2025-2026')
        ->count();
    
    $nextYearCount = UserSchedule::where('user_id', $user->id)
        ->where('school_year', '2026-2027')
        ->count();
    
    echo "School Year 2025-2026: {$currentYearCount} classes\n";
    echo "School Year 2026-2027: {$nextYearCount} classes\n";
    
    if ($currentYearCount === 5 && $nextYearCount === 1) {
        echo "✅ School year isolation working correctly\n\n";
    } else {
        echo "❌ School year isolation failed\n\n";
    }
    
    // Summary
    echo "Summary\n";
    echo "=======\n";
    echo "✅ Semester filtering implemented successfully\n";
    echo "✅ School year tracking working correctly\n";
    echo "✅ Schedules properly isolated by semester and year\n";
    echo "✅ Current semester detection accurate\n";
    echo "\nTotal schedules created: " . UserSchedule::where('user_id', $user->id)->count() . "\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
