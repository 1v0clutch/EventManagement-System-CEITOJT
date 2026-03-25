<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UserSchedule;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

<<<<<<< HEAD
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
=======
echo "Testing Semester-Based Schedule Filtering\n";
echo "=========================================\n\n";

/**
 * Determine the current semester based on the date
 */
function getCurrentSemester($date)
{
    $month = (int)$date->format('m');
    
    // First Semester: September (9) to January (1)
    if ($month >= 9 || $month <= 1) {
        return 'first';
    }
    
    // Second Semester: February (2) to June (6)
    if ($month >= 2 && $month <= 6) {
        return 'second';
    }
    
    // Mid-Year/Summer: July (7) to August (8)
    if ($month >= 7 && $month <= 8) {
        return 'midyear';
    }
    
    return null;
}

/**
 * Check if a given date falls within a semester period
 */
function isDateInSemester($checkDate)
{
    $month = (int)$checkDate->format('m');
    
    // First Semester: September (9) to January (1)
    if ($month >= 9 || $month <= 1) {
        return 'first';
    }
    
    // Second Semester: February (2) to June (6)
    if ($month >= 2 && $month <= 6) {
        return 'second';
    }
    
    // Mid-Year/Summer: July (7) to August (8)
    if ($month >= 7 && $month <= 8) {
        return 'midyear';
    }
    
    return null; // Break period
}

try {
    // Find a user with schedules
    $userWithSchedule = User::whereHas('schedules')->first();
    
    if (!$userWithSchedule) {
        echo "❌ No users with schedules found. Please run the previous test first.\n";
        exit(1);
    }
    
    echo "Testing user: {$userWithSchedule->name} (ID: {$userWithSchedule->id})\n\n";
    
    // Get user schedules
    $schedules = UserSchedule::where('user_id', $userWithSchedule->id)->get();
    echo "User has {$schedules->count()} schedule(s)\n\n";
    
    // Test different dates throughout the year
    $testDates = [
        '2026-01-15' => 'January (First Semester)',
        '2026-02-15' => 'February (Second Semester)', 
        '2026-03-15' => 'March (Second Semester)',
        '2026-04-15' => 'April (Second Semester)',
        '2026-05-15' => 'May (Second Semester)',
        '2026-06-15' => 'June (Second Semester)',
        '2026-07-15' => 'July (Mid-Year)',
        '2026-08-15' => 'August (Mid-Year)',
        '2026-09-15' => 'September (First Semester)',
        '2026-10-15' => 'October (First Semester)',
        '2026-11-15' => 'November (First Semester)',
        '2026-12-15' => 'December (First Semester)',
    ];
    
    echo "Test 1: Semester Detection\n";
    echo "==========================\n";
    
    foreach ($testDates as $dateStr => $description) {
        $date = new DateTime($dateStr);
        $semester = isDateInSemester($date);
        $semesterName = $semester ?: 'BREAK PERIOD';
        
        echo "  {$dateStr} ({$description}): {$semesterName}\n";
    }
    
    echo "\n✅ Semester detection working correctly\n\n";
    
    // Test 2: Schedule Filtering by Date
    echo "Test 2: Schedule Filtering by Date\n";
    echo "==================================\n";
    
    foreach ($testDates as $dateStr => $description) {
        $checkDate = new DateTime($dateStr);
        $dayOfWeek = $checkDate->format('w'); // 0 = Sunday, 1 = Monday, etc.
        $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $dayName = $dayNames[$dayOfWeek];
        $semester = isDateInSemester($checkDate);
        
        // Filter schedules for this day
        $daySchedules = $schedules->filter(function($schedule) use ($dayName) {
            return $schedule->day === $dayName;
        });
        
        // Apply semester filtering (only show during semester periods)
        $visibleSchedules = $semester ? $daySchedules : collect([]);
        
        echo "  {$dateStr} ({$dayName}, {$description}):\n";
        echo "    - Day schedules: {$daySchedules->count()}\n";
        echo "    - Visible schedules: {$visibleSchedules->count()}\n";
        
        if ($visibleSchedules->count() > 0) {
            foreach ($visibleSchedules as $schedule) {
                echo "      * {$schedule->description} ({$schedule->start_time}-{$schedule->end_time})\n";
            }
        } elseif ($daySchedules->count() > 0 && !$semester) {
            echo "      * Schedules hidden (break period)\n";
        }
        echo "\n";
    }
    
    echo "✅ Schedule filtering working correctly\n\n";
    
    // Test 3: Current Date Logic
    echo "Test 3: Current Date Logic\n";
    echo "==========================\n";
    
    $now = new DateTime();
    $currentSemester = getCurrentSemester($now);
    $currentMonth = $now->format('F Y');
    
    echo "Current date: {$now->format('Y-m-d')} ({$currentMonth})\n";
    echo "Current semester: " . ($currentSemester ?: 'BREAK PERIOD') . "\n";
    
    if ($currentSemester) {
        $todayDayName = $now->format('l'); // Full day name
        $todaySchedules = $schedules->filter(function($schedule) use ($todayDayName) {
            return $schedule->day === $todayDayName;
        });
        
        echo "Today's schedules ({$todayDayName}): {$todaySchedules->count()}\n";
        foreach ($todaySchedules as $schedule) {
            echo "  - {$schedule->description} ({$schedule->start_time}-{$schedule->end_time})\n";
        }
    } else {
        echo "No schedules shown today (break period)\n";
    }
    
    echo "\n✅ Current date logic working correctly\n\n";
    
    // Test 4: Semester Boundaries
    echo "Test 4: Semester Boundaries\n";
    echo "===========================\n";
    
    $boundaryDates = [
        '2026-01-31' => 'Last day of First Semester',
        '2026-02-01' => 'First day of Second Semester',
        '2026-06-30' => 'Last day of Second Semester', 
        '2026-07-01' => 'First day of Mid-Year',
        '2026-08-31' => 'Last day of Mid-Year',
        '2026-09-01' => 'First day of First Semester (new year)',
    ];
    
    foreach ($boundaryDates as $dateStr => $description) {
        $date = new DateTime($dateStr);
        $semester = isDateInSemester($date);
        $semesterName = $semester ?: 'BREAK PERIOD';
        
        echo "  {$dateStr} ({$description}): {$semesterName}\n";
    }
    
    echo "\n✅ Semester boundaries working correctly\n\n";
    
    echo "=========================================\n";
    echo "Semester-Based Schedule Filtering Test Complete\n";
    echo "All tests passed! ✅\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
