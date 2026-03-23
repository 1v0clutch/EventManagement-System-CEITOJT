<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    echo "Checking user_schedules table structure...\n\n";
    
    // Get all columns
    $columns = DB::select("SHOW COLUMNS FROM user_schedules");
    
    echo "Current columns in user_schedules:\n";
    foreach ($columns as $column) {
        echo "- {$column->Field} ({$column->Type})\n";
    }
    
    // Check if semester column exists
    $hasSemester = Schema::hasColumn('user_schedules', 'semester');
    $hasSchoolYear = Schema::hasColumn('user_schedules', 'school_year');
    
    echo "\n";
    echo "Has 'semester' column: " . ($hasSemester ? "YES" : "NO") . "\n";
    echo "Has 'school_year' column: " . ($hasSchoolYear ? "YES" : "NO") . "\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
