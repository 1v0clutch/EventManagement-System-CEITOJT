# Created Academic Events Implementation

## Overview

This implementation fixes the issue where user-created academic events (created via "Create Academic Event" button) were appearing across all academic years. Now, these events are properly isolated to specific academic years and semesters.

## Problem Statement

Previously, when users clicked "Create Academic Event", the system would:
1. Create an entry in `default_events` table with `school_year = null`
2. This made the event appear as a "template" across all academic years
3. Users couldn't distinguish between system default events and their custom created events

## Solution

Created a dedicated table and system for user-created academic events that:
1. Stores events with explicit `school_year` and `semester` fields
2. Ensures events only appear in their designated academic year
3. Maintains proper isolation between different academic years
4. Distinguishes between default/template events and user-created events

## Database Changes

### New Table: `created_academic_events`

```sql
CREATE TABLE created_academic_events (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    month INT NOT NULL,                    -- 1-12
    semester INT NOT NULL,                 -- 1 (First), 2 (Second), 3 (Mid-Year)
    school_year VARCHAR(255) NOT NULL,     -- e.g., "2025-2026"
    date DATE NULL,
    end_date DATE NULL,
    created_by BIGINT NOT NULL,            -- Foreign key to users
    order INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    UNIQUE KEY unique_created_academic_event (name, month, semester, school_year),
    INDEX idx_school_year_semester_month (school_year, semester, month),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);
```

### Key Features:
- **Isolation**: Events are tied to specific `school_year` and `semester`
- **Uniqueness**: Prevents duplicate event names within the same month/semester/year
- **Audit Trail**: Tracks who created each event via `created_by`
- **Ordering**: Maintains display order within each month

## Backend Implementation

### New Model: `CreatedAcademicEvent`

Location: `backend/app/Models/CreatedAcademicEvent.php`

Key methods:
- `scopeForSchoolYear($query, $schoolYear)` - Filter by school year
- `scopeForSemester($query, $semester)` - Filter by semester
- `scopeForMonth($query, $month)` - Filter by month
- `getSemesterFromMonth($month)` - Determine semester from month
- `getSemesterNameAttribute()` - Get human-readable semester name

### New Controller: `CreatedAcademicEventController`

Location: `backend/app/Http/Controllers/CreatedAcademicEventController.php`

Endpoints:
- `GET /api/created-academic-events` - List events (with filters)
- `POST /api/created-academic-events` - Create new event
- `PUT /api/created-academic-events/{event}` - Update event
- `DELETE /api/created-academic-events/{event}` - Delete event
- `PUT /api/created-academic-events/{event}/date` - Update event dates

### Updated: `DefaultEventController`

The `index` method now returns both:
1. Default/template events (from `default_events` + `default_event_dates`)
2. Created academic events (from `created_academic_events`)

Events are merged and include an `is_created` flag to distinguish between types.

## Frontend Changes

### Updated: `DefaultEvents.jsx`

1. **Create Event Flow**:
   - Changed from `/default-events/create-empty` to `/created-academic-events`
   - Now passes `school_year` during creation
   - Events are automatically assigned to correct semester based on month

2. **Save Date Flow**:
   - Detects if event is created (`is_created` flag)
   - Routes to appropriate endpoint based on event type
   - Uses `actual_id` for created events (since ID is prefixed with "created_")

3. **Event Display**:
   - Created events show with prefixed ID: `created_{id}`
   - Maintains `actual_id` for API operations
   - Seamlessly integrated with default events in the UI

## API Routes

### New Routes (Admin Only)

```php
// Created Academic Events
Route::get('/created-academic-events', [CreatedAcademicEventController::class, 'index']);
Route::post('/created-academic-events', [CreatedAcademicEventController::class, 'store']);
Route::put('/created-academic-events/{event}', [CreatedAcademicEventController::class, 'update']);
Route::delete('/created-academic-events/{event}', [CreatedAcademicEventController::class, 'destroy']);
Route::put('/created-academic-events/{event}/date', [CreatedAcademicEventController::class, 'updateDate']);
```

## Migration Instructions

### Step 1: Run Migration

```bash
cd backend
php artisan migrate --path=database/migrations/2026_03_23_120000_create_created_academic_events_table.php
```

Or use the batch file:
```bash
cd backend
RUN_CREATED_ACADEMIC_EVENTS_MIGRATION.bat
```

### Step 2: Test the System

```bash
cd backend
php test-created-academic-events.php
```

This will:
- Create test events in different school years
- Verify isolation between school years
- Test semester filtering
- Verify duplicate prevention
- Clean up test data

### Step 3: Frontend Testing

1. Navigate to Default Events page
2. Select a school year (e.g., 2025-2026)
3. Click "Create Academic Event" for any month
4. Enter event name and set dates
5. Switch to a different school year (e.g., 2026-2027)
6. Verify the created event does NOT appear
7. Switch back to original school year
8. Verify the event IS visible

## Data Flow

### Creating an Academic Event

```
User clicks "Create Academic Event"
    ↓
Frontend: POST /api/created-academic-events
    {
        name: "New Event",
        month: 9,
        school_year: "2025-2026"
    }
    ↓
Backend: CreatedAcademicEventController@store
    - Determines semester from month
    - Checks for duplicates
    - Creates event with created_by = current user
    ↓
Database: Insert into created_academic_events
    ↓
Response: Returns created event with ID
    ↓
Frontend: Refreshes event list
    - Event appears with ID "created_{id}"
    - Only visible in 2025-2026 school year
```

### Fetching Events

```
Frontend: GET /api/default-events?school_year=2025-2026
    ↓
Backend: DefaultEventController@index
    - Fetches default events (templates)
    - Fetches default_event_dates for school year
    - Fetches created_academic_events for school year
    - Merges all events
    - Adds is_created flag
    ↓
Response: Combined array of events
    [
        { id: 1, name: "Default Event", is_created: false, ... },
        { id: "created_5", actual_id: 5, name: "Custom Event", is_created: true, ... }
    ]
    ↓
Frontend: Displays all events
    - Default events: editable dates only
    - Created events: fully editable
```

## Key Differences

### Default Events (Templates)
- Stored in: `default_events` table
- School Year: `null` (templates)
- Dates: Stored in `default_event_dates` per school year
- Purpose: System-wide recurring events
- Visibility: All school years (with year-specific dates)

### Created Academic Events
- Stored in: `created_academic_events` table
- School Year: Specific (e.g., "2025-2026")
- Dates: Stored directly in the event record
- Purpose: User-created one-time events
- Visibility: Only in designated school year

## Benefits

1. **Proper Isolation**: Events don't leak across academic years
2. **Clear Ownership**: Track who created each event
3. **Flexible Management**: Users can create year-specific events
4. **Data Integrity**: Unique constraints prevent duplicates
5. **Audit Trail**: Created_by and timestamps for accountability
6. **Performance**: Indexed queries for fast filtering

## Testing Checklist

- [ ] Migration runs successfully
- [ ] Can create academic event for current year
- [ ] Event appears in current year only
- [ ] Event does NOT appear in other years
- [ ] Can set/update dates for created events
- [ ] Can delete created events
- [ ] Duplicate prevention works
- [ ] Semester filtering works correctly
- [ ] Events are properly ordered within months
- [ ] Created events integrate seamlessly with default events in UI

## Troubleshooting

### Events appearing in wrong year
- Check `school_year` field in database
- Verify frontend is passing correct `currentSchoolYear`
- Check API response includes correct `school_year`

### Cannot create event
- Verify user has admin role
- Check for duplicate event names in same month/semester/year
- Review validation errors in API response

### Events not showing up
- Verify `is_created` flag is being checked correctly
- Check if events are being filtered by semester
- Ensure API is returning both default and created events

## Future Enhancements

1. **Bulk Operations**: Copy events from one year to another
2. **Templates**: Convert created events into default templates
3. **Permissions**: Fine-grained control over who can create events
4. **History**: Track changes to created events
5. **Categories**: Add event categories or tags
6. **Notifications**: Alert users when events are created/modified

## Files Modified/Created

### Created:
- `backend/database/migrations/2026_03_23_120000_create_created_academic_events_table.php`
- `backend/app/Models/CreatedAcademicEvent.php`
- `backend/app/Http/Controllers/CreatedAcademicEventController.php`
- `backend/test-created-academic-events.php`
- `backend/RUN_CREATED_ACADEMIC_EVENTS_MIGRATION.bat`
- `CREATED_ACADEMIC_EVENTS_IMPLEMENTATION.md`

### Modified:
- `backend/routes/api.php` - Added new routes
- `backend/app/Http/Controllers/DefaultEventController.php` - Updated index method
- `frontend/src/pages/DefaultEvents.jsx` - Updated create and save flows
