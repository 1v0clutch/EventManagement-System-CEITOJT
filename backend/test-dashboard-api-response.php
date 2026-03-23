<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\DashboardController;

echo "=== Dashboard API Response Test ===\n\n";

// Get a test user
$user = User::where('is_validated', true)->first();

if (!$user) {
    echo "❌ No validated users found\n";
    exit(1);
}

echo "Testing dashboard API for user: {$user->name} (ID: {$user->id})\n\n";

// Create a mock request
$request = Request::create('/api/dashboard', 'GET');
$request->setUserResolver(function () use ($user) {
    return $user;
});

// Call the dashboard controller
$controller = new DashboardController();
$response = $controller->index($request);

// Get the response data
$data = json_decode($response->getContent(), true);

echo "=== API Response Summary ===\n\n";

// Check events
$events = $data['events'] ?? [];
echo "Total Events Returned: " . count($events) . "\n\n";

// Count by type
$personalCount = 0;
$regularCount = 0;
$meetingCount = 0;

foreach ($events as $event) {
    if ($event['is_personal'] ?? false) {
        $personalCount++;
    } elseif (($event['event_type'] ?? 'event') === 'meeting') {
        $meetingCount++;
    } else {
        $regularCount++;
    }
}

echo "Event Breakdown:\n";
echo "  📅 Regular Events: {$regularCount}\n";
echo "  🤝 Meetings: {$meetingCount}\n";
echo "  👤 Personal Events: {$personalCount}\n\n";

// Check default events
$defaultEvents = $data['defaultEvents'] ?? [];
echo "🎓 Academic Events: " . count($defaultEvents) . "\n\n";

// Check schedules
$schedules = $data['userSchedules'] ?? [];
echo "📚 Weekly Schedules: " . count($schedules) . "\n\n";

// Show sample personal event if exists
if ($personalCount > 0) {
    echo "=== Sample Personal Event ===\n";
    foreach ($events as $event) {
        if ($event['is_personal'] ?? false) {
            echo "Title: {$event['title']}\n";
            echo "Date: {$event['date']}\n";
            echo "Time: {$event['time']}\n";
            echo "is_personal: " . ($event['is_personal'] ? 'true' : 'false') . "\n";
            echo "Color: Purple (frontend will render this)\n";
            break;
        }
    }
    echo "\n✅ Personal events are now included in the API response!\n";
} else {
    echo "ℹ️  No personal events found for this user.\n";
    echo "   Create a personal event to test the display.\n";
}

echo "\n=== Response Structure ===\n";
echo "Keys in response: " . implode(', ', array_keys($data)) . "\n\n";

echo "✅ Dashboard API test complete!\n";
echo "\nThe frontend Calendar component will now receive:\n";
echo "  - events[] (including personal events)\n";
echo "  - defaultEvents[] (academic calendar)\n";
echo "  - userSchedules[] (weekly class schedule)\n";
echo "  - members[] (for event creation)\n";
echo "  - schoolYear and nextSchoolYear\n";
