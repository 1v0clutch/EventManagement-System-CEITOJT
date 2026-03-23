# Created Academic Events - Quick Start Guide

## What Changed?

User-created academic events are now properly isolated to specific academic years. When you create an academic event in 2025-2026, it will ONLY appear in 2025-2026, not in other years.

## Installation

### 1. Run the Migration

```bash
cd backend
RUN_CREATED_ACADEMIC_EVENTS_MIGRATION.bat
```

Or manually:
```bash
cd backend
php artisan migrate --path=database/migrations/2026_03_23_120000_create_created_academic_events_table.php
```

### 2. Test the System

```bash
cd backend
php test-created-academic-events.php
```

Expected output:
```
✓ Created event: Test Academic Event 2025-2026
✓ Found 1 event(s) for 2025-2026
✓ Found 1 event(s) for 2026-2027
✓ Events are properly isolated
✓ Duplicate prevention working correctly
```

## How to Use

### Creating an Academic Event

1. Go to **Default Events** page
2. Select the school year (e.g., 2025-2026)
3. Navigate to the month where you want to add an event
4. Click **"Create Academic Event"** button
5. Enter the event name
6. Click **Save** (or press Enter)
7. Set the date range in the date picker
8. Click **Save Date**

### Viewing Events

- Events are automatically filtered by the selected school year
- Switch between school years using the year selector
- Created events appear alongside default events
- Each event shows its date range (if set)

### Editing Events

- Click the **Edit Date** button to change dates
- Created events can be fully edited
- Changes only affect the current school year

### Deleting Events

- Created academic events can be deleted
- Default/template events cannot be deleted (only dates can be removed)

## Key Concepts

### Default Events vs Created Events

| Feature | Default Events | Created Events |
|---------|---------------|----------------|
| **Storage** | `default_events` table | `created_academic_events` table |
| **Visibility** | All school years | Specific school year only |
| **Purpose** | System templates | User-created one-time events |
| **Dates** | Per-year in `default_event_dates` | Stored with event |
| **Can Delete** | No (only remove dates) | Yes |

### Semester Assignment

Events are automatically assigned to semesters based on month:

- **First Semester**: September, October, November, December, January
- **Second Semester**: February, March, April, May, June
- **Mid-Year**: July, August

## Examples

### Example 1: Create Event for Specific Year

```
School Year: 2025-2026
Month: September
Event Name: "Orientation Week 2025"
Date: Sept 1-5, 2025

Result: Event appears ONLY in 2025-2026
```

### Example 2: Same Event Name, Different Years

```
Year 2025-2026:
  - "Orientation Week" (Sept 1-5, 2025)

Year 2026-2027:
  - "Orientation Week" (Sept 1-5, 2026)

Result: Two separate events, isolated by year
```

### Example 3: Multiple Events in Same Month

```
School Year: 2025-2026
Month: September

Events:
  1. "Orientation Week" (Sept 1-5)
  2. "Department Meeting" (Sept 10)
  3. "Welcome Party" (Sept 15)

Result: All three appear in September 2025-2026 only
```

## Verification Steps

After installation, verify the system works:

### ✓ Test 1: Create Event
1. Select school year 2025-2026
2. Create event "Test Event A" in September
3. Verify it appears in the list

### ✓ Test 2: Isolation
1. Switch to school year 2026-2027
2. Verify "Test Event A" does NOT appear
3. Switch back to 2025-2026
4. Verify "Test Event A" IS visible

### ✓ Test 3: Duplicate Prevention
1. Try to create another "Test Event A" in September 2025-2026
2. Should show error: "Event with this name already exists"

### ✓ Test 4: Date Setting
1. Click "Edit Date" on "Test Event A"
2. Set date range: Sept 15-16, 2025
3. Save and verify dates appear correctly

### ✓ Test 5: Cleanup
1. Delete "Test Event A"
2. Verify it's removed from the list

## Troubleshooting

### Problem: Event appears in all years

**Cause**: Event was created using old system (stored in `default_events` with `school_year = null`)

**Solution**: Delete the event and recreate it using the new system

### Problem: Cannot create event

**Possible causes**:
1. Duplicate name in same month/semester/year
2. Not logged in as admin
3. Missing required fields

**Solution**: Check error message and adjust accordingly

### Problem: Event not showing up

**Possible causes**:
1. Wrong school year selected
2. Event created in different semester
3. Browser cache issue

**Solution**: 
1. Verify correct school year is selected
2. Refresh the page
3. Clear browser cache if needed

## API Reference

### Create Event
```javascript
POST /api/created-academic-events
{
  "name": "Event Name",
  "month": 9,
  "school_year": "2025-2026"
}
```

### Update Date
```javascript
PUT /api/created-academic-events/{id}/date
{
  "date": "2025-09-15",
  "end_date": "2025-09-16"
}
```

### Delete Event
```javascript
DELETE /api/created-academic-events/{id}
```

### List Events
```javascript
GET /api/created-academic-events?school_year=2025-2026&semester=1
```

## Support

If you encounter issues:

1. Check the console for error messages
2. Verify database migration ran successfully
3. Run the test script: `php test-created-academic-events.php`
4. Review the full documentation: `CREATED_ACADEMIC_EVENTS_IMPLEMENTATION.md`

## Summary

✓ Created academic events are now isolated by school year
✓ Events only appear in their designated year
✓ System prevents duplicate event names
✓ Seamless integration with existing default events
✓ Full CRUD operations supported
