# Created Default Academic Events System

## Overview

This system manages **created default academic events** - events that have been instantiated with specific dates and assigned to a school year and semester. This is separate from the base event templates.

## Key Concept

### Two-Level System

1. **Base Event Templates** (`default_events` table)
   - Generic event definitions (e.g., "First Day of Classes", "Midterm Exams")
   - No specific dates or school years
   - Reusable templates

2. **Created Events** (`default_event_dates` table)
   - Actual events with specific dates
   - Linked to a school year (e.g., "2025-2026")
   - Assigned to a semester (First, Second, or Mid-Year)
   - Tracks who created them and when

## Database Schema

### Table: `default_event_dates`

This table stores all created default academic events.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| default_event_id | bigint | Links to base event template |
| school_year | string(20) | Format: "2025-2026" |
| semester | integer | 1=First, 2=Second, 3=Mid-Year |
| date | date | Event start date |
| end_date | date | Event end date (nullable) |
| month | integer | Month extracted from date (1-12) |
| created_by | bigint | User who created this event |
| created_at | timestamp | When event was created |
| updated_at | timestamp | Last update time |

### Semester Mapping

- **First Semester (1)**: September, October, November, December, January
- **Second Semester (2)**: February, March, April, May, June
- **Mid-Year (3)**: July, August

## API Endpoints

### Get All Events for a School Year
```
GET /api/default-events/v2?school_year=2025-2026
```

Returns base events merged with their created instances (if dates are set).

### Get Only Created Events (with dates set)
```
GET /api/default-events/v2/scheduled?school_year=2025-2026
GET /api/default-events/v2/scheduled?school_year=2025-2026&semester=1
GET /api/default-events/v2/scheduled?school_year=2025-2026&month=9
```

Returns only events that have been created (dates assigned).

**Response Example:**
```json
{
  "events": [
    {
      "id": 5,
      "event_id": 1,
      "name": "First Day of Classes",
      "date": "2025-09-01",
      "end_date": null,
      "month": 9,
      "semester": 1,
      "semester_name": "First Semester",
      "school_year": "2025-2026",
      "created_at": "2026-03-21 10:00:00"
    }
  ],
  "count": 1
}
```

### Create a Default Event (Admin Only)
```
POST /api/default-events/v2/{id}/set-date
```

**Request Body:**
```json
{
  "date": "2025-09-01",
  "end_date": "2025-09-05",
  "school_year": "2025-2026"
}
```

The semester is automatically determined from the date.

### Delete a Created Event (Admin Only)
```
DELETE /api/default-events/v2/{id}/remove-date
```

**Request Body:**
```json
{
  "school_year": "2025-2026"
}
```

### Get Statistics
```
GET /api/default-events/v2/statistics?school_year=2025-2026
```

**Response Example:**
```json
{
  "school_year": "2025-2026",
  "total_base_events": 20,
  "events_with_dates": 15,
  "events_without_dates": 5,
  "completion_percentage": 75.00,
  "events_by_month": {
    "9": 3,
    "10": 2
  },
  "events_by_semester": {
    "First Semester": 8,
    "Second Semester": 6,
    "Mid-Year": 1
  }
}
```

## Usage Examples

### Query Created Events by Semester

```php
// Get all First Semester events for 2025-2026
$firstSemesterEvents = DefaultEventDate::forSchoolYear('2025-2026')
    ->forSemester(1)
    ->with('defaultEvent')
    ->orderedByDate()
    ->get();

// Get all Second Semester events
$secondSemesterEvents = DefaultEventDate::forSchoolYear('2025-2026')
    ->forSemester(2)
    ->with('defaultEvent')
    ->orderedByDate()
    ->get();

// Get Mid-Year events
$midYearEvents = DefaultEventDate::forSchoolYear('2025-2026')
    ->forSemester(3)
    ->with('defaultEvent')
    ->orderedByDate()
    ->get();
```

### Create a New Event Instance

```php
use App\Models\DefaultEventDate;

// Create event for September 1, 2025
$eventDate = DefaultEventDate::updateOrCreate(
    [
        'default_event_id' => 1, // Base event template ID
        'school_year' => '2025-2026',
    ],
    [
        'date' => '2025-09-01',
        'end_date' => null,
        'month' => 9,
        'semester' => 1, // Automatically determined
        'created_by' => auth()->id(),
    ]
);
```

### Get All Created Events for Display

```php
// Get all created events with their details
$createdEvents = DefaultEventDate::with(['defaultEvent', 'creator'])
    ->forSchoolYear('2025-2026')
    ->orderedByDate()
    ->get();

foreach ($createdEvents as $event) {
    echo "{$event->defaultEvent->name}\n";
    echo "Date: {$event->date->format('M d, Y')}\n";
    echo "Semester: {$event->semester_name}\n";
    echo "Created by: {$event->creator->name}\n\n";
}
```

## Migration Steps

1. **Run the migration to add semester field:**
   ```bash
   php artisan migrate
   ```

2. **Test the system:**
   ```bash
   php backend/test-created-default-events.php
   ```

3. **Verify data:**
   - Check that existing events have semester assigned
   - Verify semester determination logic
   - Test API endpoints

## Benefits

✓ Clear separation between templates and created events
✓ Track which events have been scheduled for each school year
✓ Filter events by semester for better organization
✓ Historical tracking of who created events and when
✓ No duplication of event definitions
✓ Easy to see completion status per school year
