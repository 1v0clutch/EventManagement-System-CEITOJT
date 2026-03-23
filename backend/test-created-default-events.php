<?php

/**
 * Test script for Created Default Academic Events
 * Tests the system for tracking default events with dates, school year, and semester
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\DefaultEvent;
use App\Models\DefaultEventDate;
use Illuminate\Support\Facades\DB;

echo "=== Created Default Academic Events Test ===\n\n";

// 1. Check table structure
echo "1. Checking default_event_dates table structure...\n";
try {
    $columns = DB::select("DESCRIBE default_event_dates");
    $hasSchoolYear = false;
    $hasSemester = false;
    $hasDate = false;
    
    foreach ($columns as $column) {
        if ($column->Field === 'school_year') $hasSchoolYear = true;
        if ($column->Field === 'semester') $hasSemester = true;
        if ($column->Field === 'date') $hasDate = true;
    }
    
    echo "   School Year field: " . ($hasSchoolYear ? "✓" : "✗") . "\n";
    echo "   Semester field: " . ($hasSemester ? "✓" : "✗") . "\n";
    echo "   Date field: " . ($hasDate ? "✓" : "✗") . "\n\n";
    
    if (!$hasSemester) {
        echo "   ⚠ Semester field missing. Run migration:\n";
        echo "   php artisan migrate\n\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

// 2. Show created default events by school year
echo "2. Created Default Events by School Year:\n";
$schoolYears = DefaultEventDate::select('school_year')
    ->distinct()
    ->orderBy('school_year')
    ->pluck('school_year');

foreach ($schoolYears as $schoolYear) {
    $count = DefaultEventDate::forSchoolYear($schoolYear)->count();
    echo "   {$schoolYear}: {$count} events\n";
}
echo "\n";

// 3. Show created events by semester for 2025-2026
echo "3. Created Events by Semester (2025-2026):\n";
$schoolYear = '2025-2026';

if (DB::getSchemaBuilder()->hasColumn('default_event_dates', 'semester')) {
    for ($semester = 1; $semester <= 3; $semester++) {
        $events = DefaultEventDate::forSchoolYear($schoolYear)
            ->forSemester($semester)
            ->with('defaultEvent')
            ->get();
        
        $semesterName = match($semester) {
            1 => 'First Semester',
            2 => 'Second Semester',
            3 => 'Mid-Year',
        };
        
        echo "   {$semesterName}: {$events->count()} events\n";
        
        foreach ($events as $event) {
            echo "      - {$event->defaultEvent->name} ({$event->date->format('M d, Y')})\n";
        }
    }
} else {
    echo "   ⚠ Semester field not yet added to table\n";
}
echo "\n";

// 4. Show all created events with full details
echo "4. All Created Default Events (with dates set):\n";
$createdEvents = DefaultEventDate::with('defaultEvent', 'creator')
    ->orderBy('school_year')
    ->orderBy('date')
    ->get();

if ($createdEvents->count() === 0) {
    echo "   No events with dates set yet.\n\n";
} else {
    foreach ($createdEvents as $event) {
        $semesterInfo = DB::getSchemaBuilder()->hasColumn('default_event_dates', 'semester') 
            ? " | Semester: {$event->semester_name}" 
            : "";
        
        echo "   [{$event->school_year}] {$event->defaultEvent->name}\n";
        echo "      Date: {$event->date->format('M d, Y')}";
        if ($event->end_date) {
            echo " - {$event->end_date->format('M d, Y')}";
        }
        echo "{$semesterInfo}\n";
        if ($event->creator) {
            echo "      Created by: {$event->creator->name}\n";
        }
        echo "\n";
    }
}

// 5. Test semester determination
echo "5. Testing Semester Determination:\n";
$testMonths = [
    9 => 'September',
    12 => 'December',
    1 => 'January',
    3 => 'March',
    6 => 'June',
    7 => 'July',
    8 => 'August',
];

foreach ($testMonths as $month => $monthName) {
    $semester = DefaultEventDate::getSemesterFromMonth($month);
    $semesterName = match($semester) {
        1 => 'First Semester',
        2 => 'Second Semester',
        3 => 'Mid-Year',
    };
    echo "   {$monthName} (Month {$month}) → {$semesterName}\n";
}
echo "\n";

// 6. Statistics
echo "6. Statistics:\n";
$totalBase = DefaultEvent::whereNull('school_year')->count();
$totalCreated = DefaultEventDate::count();
$schoolYearsCount = DefaultEventDate::distinct('school_year')->count();

echo "   Base event templates: {$totalBase}\n";
echo "   Total created events (with dates): {$totalCreated}\n";
echo "   School years with events: {$schoolYearsCount}\n\n";

echo "=== Test Complete ===\n";
