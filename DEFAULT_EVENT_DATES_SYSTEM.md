# Default Event Dates System

## Overview
The `default_event_dates` table stores user-defined dates for default academic events within each school year. This allows the same base event template to have different dates across multiple school years.

## Database Structure

### Table: `default_event_dates`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| default_event_id | bigint | Foreign key to default_events table |
| school_year | string(20) | Format: "2025-2026" |
| date | date | Start date of the event |
| end_date | date | End date (nullable for single-day events) |
| month | integer | Extracted from date for quick filtering (1-12) |
| created_by | bigint | Foreign key to users table (nullable) |
| created_at | timestamp | Record creation time |
| updated_at | timestamp | Record update time |

### Constraints
- Unique constraint: `unique_event_date_per_school_year` on (default_event_id, school_year)
- Indexes on: (school_year, month), date

## Models

### DefaultEvent Model
Location: `backend/app/Models/DefaultEvent.php`

Key relationships:
- `eventDates()` - HasMany relationship to DefaultEventDate
- `getDateForSchoolYear($schoolYear)` - Get date for specific school year

### DefaultEventDate Model
Location: `backend/app/Models/DefaultEventDate.php`

Key relationships:
- `defaultEvent()` - BelongsTo DefaultEvent
- `creator()` - BelongsTo User

Scopes:
- `forSchoolYear($schoolYear)` - Filter by school year
- `forMonth($month)` - Filter by month
- `orderedByDate()` - Order by date ascending

## API Endpoints

### 1. Get All Default Events with Dates
**GET** `/api/default-events/v2?school_year=2025-2026`

Returns all base default events with their assigned dates for the specified school year.

Response:
```json
{
  "events": [
    {
      "id": 1,
      "name": "First Day of Classes",
      "month": 9,
      "order": 1,
      "date": "2025-09-01",
      "end_date": null,
      "school_year": "2025-2026",
      "has_date_set": true,
      "date_id": 5
    }
  ]
}
```

### 2. Set/Update Event Date (Admin Only)
**POST** `/api/default-events/v2/{id}/set-date`

Request body:
```json
{
  "date": "2025-09-01",
  "end_date": "2025-09-05",
  "school_year": "2025-2026"
}
```

Validation:
- Date must be within school year (September to August)
- End date must be after or equal to start date
- School year format: YYYY-YYYY

### 3. Remove Event Date (Admin Only)
**DELETE** `/api/default-events/v2/{id}/remove-date`

Request body:
```json
{
  "school_year": "2025-2026"
}
```

### 4. Get Scheduled Events
**GET** `/api/default-events/v2/scheduled?school_year=2025-2026&month=9`

Returns only events that have dates set for the specified school year.

Query parameters:
- `school_year` (required): Format "YYYY-YYYY"
- `month` (optional): Filter by month (1-12)

Response:
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
      "school_year": "2025-2026",
      "created_at": "2026-03-10 10:00:00"
    }
  ],
  "count": 1
}
```

### 5. Get Statistics
**GET** `/api/default-events/v2/statistics?school_year=2025-2026`

Returns statistics about date assignments for a school year.

Response:
```json
{
  "school_year": "2025-2026",
  "total_base_events": 20,
  "events_with_dates": 15,
  "events_without_dates": 5,
  "completion_percentage": 75.00,
  "events_by_month": {
    "9": 3,
    "10": 2,
    "11": 4
  }
}
```

## How It Works

### Architecture
1. **Base Events**: Stored in `default_events` table with `school_year = NULL`
   - These are templates (e.g., "First Day of Classes", "Midterm Exams")
   
2. **Date Assignments**: Stored in `default_event_dates` table
   - Links a base event to a specific date in a specific school year
   - One base event can have multiple date assignments (one per school year)

### Example Flow

1. Admin creates base event "Midterm Exams" (stored in default_events)
2. For school year 2025-2026, admin sets date to October 15-20, 2025
   - Creates entry in default_event_dates table
3. For school year 2026-2027, admin sets date to October 18-23, 2026
   - Creates another entry in default_event_dates table
4. Same base event, different dates per school year

### Benefits
- No duplication of event definitions
- Clean separation between event templates and actual dates
- Easy to manage events across multiple school years
- Historical tracking of who set dates and when

## Migration Files

1. **Create table**: `2026_03_10_100000_create_default_event_dates_table.php`
2. **Migrate existing data**: `2026_03_10_110000_migrate_existing_default_event_dates.php`

## Usage Example

```php
// Get all events with dates for current school year
$events = DefaultEventDate::with('defaultEvent')
    ->forSchoolYear('2025-2026')
    ->orderedByDate()
    ->get();

// Set date for an event
DefaultEventDate::updateOrCreate(
    [
        'default_event_id' => 1,
        'school_year' => '2025-2026',
    ],
    [
        'date' => '2025-09-01',
        'end_date' => null,
        'month' => 9,
        'created_by' => auth()->id(),
    ]
);
```
