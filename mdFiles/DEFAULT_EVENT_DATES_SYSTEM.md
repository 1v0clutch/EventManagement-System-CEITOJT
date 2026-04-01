<<<<<<< HEAD
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
=======
# Default Event Dates System - Polished Architecture

## Overview

This document describes the improved architecture for storing and managing dates for default academic calendar events across different school years.

## Problem Solved

Previously, the system duplicated entire event records in the `default_events` table when a user set a date for a specific school year. This approach had several issues:

1. Data duplication (event name, month, order repeated)
2. Difficult to track which events have dates set
3. Hard to maintain consistency across school years
4. No audit trail of who set the dates

## New Architecture

### Database Tables

#### 1. `default_events` (Base Events)
Stores the template/base definition of academic calendar events:
- `id` - Primary key
- `name` - Event name (e.g., "Beginning of Classes")
- `month` - Default month (1-12)
- `order` - Display order within the month
- `school_year` - NULL for base events (kept for backward compatibility)
- `date` - NULL for base events (kept for backward compatibility)
- `end_date` - NULL for base events (kept for backward compatibility)

#### 2. `default_event_dates` (NEW - Date Assignments)
Tracks when users set specific dates for events in each school year:
- `id` - Primary key
- `default_event_id` - Foreign key to base event
- `school_year` - School year (e.g., "2025-2026")
- `date` - Assigned date
- `end_date` - Optional end date for multi-day events
- `month` - Extracted from date for quick filtering
- `created_by` - User who set the date (audit trail)
- `created_at` / `updated_at` - Timestamps

**Unique Constraint:** One date assignment per event per school year

## Benefits

1. **No Data Duplication** - Base event defined once, dates stored separately
2. **Clear Audit Trail** - Track who set dates and when
3. **Easy Statistics** - Quickly see completion percentage per school year
4. **Flexible** - Same event can have different dates across school years
5. **Maintainable** - Update event names/details in one place

## API Endpoints

### V2 Endpoints (New Architecture)

#### Get Events with Dates
```
GET /api/default-events/v2?school_year=2025-2026
```
Returns all base events with their assigned dates for the specified school year.
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d

Response:
```json
{
  "events": [
    {
      "id": 1,
<<<<<<< HEAD
      "name": "First Day of Classes",
      "month": 9,
      "order": 1,
      "date": "2025-09-01",
=======
      "name": "Beginning of Classes",
      "month": 9,
      "order": 1,
      "date": "2025-09-15",
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
      "end_date": null,
      "school_year": "2025-2026",
      "has_date_set": true,
      "date_id": 5
    }
  ]
}
```

<<<<<<< HEAD
### 2. Set/Update Event Date (Admin Only)
**POST** `/api/default-events/v2/{id}/set-date`

Request body:
```json
{
  "date": "2025-09-01",
  "end_date": "2025-09-05",
=======
#### Set/Update Event Date
```
POST /api/default-events/v2/{id}/set-date
```
Body:
```json
{
  "date": "2025-09-15",
  "end_date": "2025-09-17",
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
  "school_year": "2025-2026"
}
```

<<<<<<< HEAD
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
=======
#### Remove Event Date
```
DELETE /api/default-events/v2/{id}/remove-date?school_year=2025-2026
```

#### Get Scheduled Events
```
GET /api/default-events/v2/scheduled?school_year=2025-2026&month=9
```
Returns only events that have dates assigned.

#### Get Statistics
```
GET /api/default-events/v2/statistics?school_year=2025-2026
```
Returns completion statistics:
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
```json
{
  "school_year": "2025-2026",
  "total_base_events": 20,
  "events_with_dates": 15,
  "events_without_dates": 5,
  "completion_percentage": 75.00,
  "events_by_month": {
<<<<<<< HEAD
    "9": 3,
    "10": 2,
    "11": 4
=======
    "9": 5,
    "10": 4,
    "11": 3,
    "12": 3
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
  }
}
```

<<<<<<< HEAD
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
=======
## Migration Path

### Step 1: Run Migrations
```bash
php artisan migrate
```

This will:
1. Create the `default_event_dates` table
2. Migrate existing data from `default_events` where `school_year` is set
3. Preserve backward compatibility

### Step 2: Test the New System
```bash
php backend/test-default-event-dates-system.php
```

### Step 3: Update Frontend (Optional)
The old API endpoints still work. Update frontend gradually to use V2 endpoints.

## Models

### DefaultEvent Model
```php
// Get date for specific school year
$event = DefaultEvent::find(1);
$dateAssignment = $event->getDateForSchoolYear('2025-2026');

// Get all date assignments
$allDates = $event->eventDates;
```

### DefaultEventDate Model
```php
// Query by school year
$dates = DefaultEventDate::forSchoolYear('2025-2026')->get();

// Query by month
$septemberDates = DefaultEventDate::forMonth(9)->get();

// Get with event details
$dates = DefaultEventDate::with('defaultEvent')->get();
```

## Validation Rules

1. **Date cannot be Sunday** - Academic events not allowed on Sundays
2. **Date must be within school year** - September (start year) to August (end year)
3. **End date must be >= start date**
4. **End date cannot be Sunday**
5. **School year format** - Must match pattern: YYYY-YYYY

## Backward Compatibility

The old API endpoints (`/api/default-events`) continue to work using the original `DefaultEventController`. This allows gradual migration of frontend code.

## Future Enhancements

1. **Bulk Date Assignment** - Set dates for multiple events at once
2. **Copy Dates Between Years** - Copy all dates from one school year to another
3. **Date Templates** - Save common date patterns
4. **Conflict Detection** - Warn if dates overlap with other events
5. **Approval Workflow** - Require approval before dates are finalized

## Database Schema Diagram

```
┌─────────────────────┐
│  default_events     │
│  (Base Templates)   │
├─────────────────────┤
│ id (PK)             │
│ name                │
│ month               │
│ order               │
└──────────┬──────────┘
           │
           │ 1:N
           │
┌──────────▼──────────────┐
│ default_event_dates     │
│ (Date Assignments)      │
├─────────────────────────┤
│ id (PK)                 │
│ default_event_id (FK)   │
│ school_year             │
│ date                    │
│ end_date                │
│ month                   │
│ created_by (FK)         │
│ created_at              │
│ updated_at              │
└─────────────────────────┘
```

## Testing

Run the test script to verify the system:
```bash
php backend/test-default-event-dates-system.php
```

This will:
1. Check table structure
2. Test CRUD operations
3. Verify constraints
4. Test API endpoints
5. Check statistics

## Conclusion

This polished architecture provides a clean, maintainable solution for managing default academic calendar dates across multiple school years with proper audit trails and no data duplication.
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
