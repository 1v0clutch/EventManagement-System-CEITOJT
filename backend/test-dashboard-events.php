<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Event;
use App\Models\DefaultEventDate;
use App\Models\UserSchedule;

echo "=== Dashboard Events Test ===\n\n";

// Get a test user
$user = User::where('is_validated', true)->first();

if (!$user) {
    echo "❌ No validated users found\n";
    exit(1);
}

echo "Testing with user: {$user->name} (ID: {$user->id})\n\n";

// Check regular events (meetings/events)
$regularEvents = Event::where('host_id', $user->id)
    ->where('is_personal', false)
    ->count();

echo "📅 Regular Events (hosted): {$regularEvents}\n";

// Check personal events
$personalEvents = Event::where('host_id', $user->id)
    ->where('is_personal', true)
    ->count();

echo "👤 Personal Events: {$personalEvents}\n";

// Check invited events
$invitedEvents = Event::whereHas('members', function ($q) use ($user) {
    $q->where('users.id', $user->id);
})->where('is_personal', false)->count();

echo "📨 Invited Events: {$invitedEvents}\n";

// Check academic events
$academicEvents = DefaultEventDate::count();

echo "🎓 Academic Event Dates: {$academicEvents}\n";

// Check weekly schedules
$schedules = UserSchedule::where('user_id', $user->id)->count();

echo "📚 Weekly Schedules: {$schedules}\n\n";

// Show sample of each type
echo "=== Sample Events ===\n\n";

// Sample personal event
$samplePersonal = Event::where('host_id', $user->id)
    ->where('is_personal', true)
    ->first();

if ($samplePersonal) {
    echo "Personal Event Example:\n";
    echo "  Title: {$samplePersonal->title}\n";
    echo "  Date: {$samplePersonal->date}\n";
    echo "  Time: {$samplePersonal->time}\n";
    echo "  is_personal: " . ($samplePersonal->is_personal ? 'true' : 'false') . "\n\n";
}

// Sample regular event
$sampleRegular = Event::where('host_id', $user->id)
    ->where('is_personal', false)
    ->first();

if ($sampleRegular) {
    echo "Regular Event Example:\n";
    echo "  Title: {$sampleRegular->title}\n";
    echo "  Date: {$sampleRegular->date}\n";
    echo "  Time: {$sampleRegular->time}\n";
    echo "  is_personal: " . ($sampleRegular->is_personal ? 'true' : 'false') . "\n\n";
}

// Sample academic event
$sampleAcademic = DefaultEventDate::with('defaultEvent')->first();

if ($sampleAcademic) {
    echo "Academic Event Example:\n";
    echo "  Name: {$sampleAcademic->defaultEvent->name}\n";
    echo "  Date: {$sampleAcademic->date}\n";
    echo "  Semester: {$sampleAcademic->semester}\n\n";
}

// Sample schedule
$sampleSchedule = UserSchedule::where('user_id', $user->id)->first();

if ($sampleSchedule) {
    echo "Weekly Schedule Example:\n";
    echo "  Day: {$sampleSchedule->day}\n";
    echo "  Time: {$sampleSchedule->start_time} - {$sampleSchedule->end_time}\n";
    echo "  Description: {$sampleSchedule->description}\n";
    echo "  Semester: {$sampleSchedule->semester}\n\n";
}

echo "✅ Test complete!\n";
echo "\nNOTE: After the fix, the dashboard should now return:\n";
echo "  - Regular events (meetings/events you host)\n";
echo "  - Personal events (your personal calendar items)\n";
echo "  - Invited events (events you're invited to)\n";
echo "  - Academic events (from default_event_dates)\n";
echo "  - Weekly schedules (your class schedule)\n";
