<?php

/**
 * Test script to verify the default event date update route
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Default Event Date Update Route ===\n\n";

// Check if routes are loaded
$routes = app('router')->getRoutes();
$defaultEventRoutes = [];

foreach ($routes as $route) {
    $uri = $route->uri();
    if (strpos($uri, 'default-events') !== false) {
        $defaultEventRoutes[] = [
            'method' => implode('|', $route->methods()),
            'uri' => $uri,
            'action' => $route->getActionName(),
        ];
    }
}

echo "1. Default Event Routes Found:\n";
if (empty($defaultEventRoutes)) {
    echo "   ⚠️  No default-events routes found!\n";
} else {
    foreach ($defaultEventRoutes as $route) {
        echo "   - {$route['method']} /{$route['uri']}\n";
        echo "     Action: {$route['action']}\n";
    }
}

echo "\n2. Testing Default Event Model:\n";
$defaultEvents = \App\Models\DefaultEvent::all();
echo "   Total default events: " . $defaultEvents->count() . "\n";

if ($defaultEvents->count() > 0) {
    $testEvent = $defaultEvents->first();
    echo "   Sample event:\n";
    echo "   - ID: {$testEvent->id}\n";
    echo "   - Name: {$testEvent->name}\n";
    echo "   - Month: {$testEvent->month}\n";
    echo "   - Date: " . ($testEvent->date ? $testEvent->date->format('Y-m-d') : 'Not set') . "\n";
    echo "   - School Year: " . ($testEvent->school_year ?? 'Base event') . "\n";
}

echo "\n3. Expected Route:\n";
echo "   PUT /api/default-events/{id}/date\n";
echo "   Controller: DefaultEventController@updateDate\n";
echo "   Middleware: auth:sanctum\n";

echo "\n4. Frontend Call:\n";
echo "   await api.put(`/default-events/\${event.id}/date`, {\n";
echo "     date: selectedDate,\n";
echo "     school_year: currentSchoolYear\n";
echo "   });\n";

echo "\n=== Test Complete ===\n";
