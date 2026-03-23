# Migration Guide: Move Created Events to Proper Table

## The Problem

Currently, your created default events (like ID #80 "U-Games") are stored in the `default_events` table with dates and school_year filled in. This is incorrect because:

- `default_events` should only contain **templates** (event names without dates)
- `default_event_dates` should contain **created events** (templates with actual dates assigned)

## Current State (WRONG)

```
default_events table:
┌────┬──────────┬───────┬────────────┬────────────┬─────────────┐
│ id │ name     │ month │ date       │ end_date   │ school_year │
├────┼──────────┼───────┼────────────┼────────────┼─────────────┤
│ 80 │ U-Games  │ 4     │ 2026-04-27 │ 2026-05-09 │ 2025-2026   │ ← WRONG!
└────┴──────────┴───────┴────────────┴────────────┴─────────────┘

default_event_dates table:
(empty) ← WRONG!
```

## Desired State (CORRECT)

```
default_events table (templates only):
┌────┬──────────┬───────┬──────┬──────────┬─────────────┐
│ id │ name     │ month │ date │ end_date │ school_year │
├────┼──────────┼───────┼──────┼──────────┼─────────────┤
│ 80 │ U-Games  │ 4     │ NULL │ NULL     │ NULL        │ ← Template
└────┴──────────┴───────┴──────┴──────────┴─────────────┘

default_event_dates table (created events):
┌────┬─────────────┬─────────────┬──────────┬────────────┬────────────┐
│ id │ event_id    │ school_year │ semester │ date       │ end_date   │
├────┼─────────────┼─────────────┼──────────┼────────────┼────────────┤
│ 1  │ 80          │ 2025-2026   │ 2        │ 2026-04-27 │ 2026-05-09 │
└────┴─────────────┴─────────────┴──────────┴────────────┴────────────┘
                                     ↑ Created event with date
```

## Migration Steps

### Step 1: Check Current Status
```bash
cd backend
php verify-migration-status.php
```

This will show you:
- How many events have dates in `default_events` (should be 0)
- How many events are in `default_event_dates` (should have your created events)

### Step 2: Run Migration
```bash
# Option 1: Use batch file (Windows)
RUN_MIGRATE_CREATED_EVENTS.bat

# Option 2: Run directly
php migrate-created-events-now.php
```

### Step 3: Verify Results
```bash
php verify-migration-status.php
```

You should see:
- ✓ default_events contains only templates (no dates)
- ✓ default_event_dates contains all created events

## What the Migration Does

1. **Finds** all events in `default_events` that have dates and school_year set
2. **Copies** them to `default_event_dates` with:
   - Proper semester assignment (auto-calculated from month)
   - All date information preserved
   - Link to the original template
3. **Cleans** the `default_events` table by removing dates/school_year
4. **Converts** those events back to templates

## Example Migration

**Before:**
```sql
-- default_events
ID: 80, Name: U-Games, Date: 2026-04-27, School Year: 2025-2026

-- default_event_dates
(empty)
```

**After:**
```sql
-- default_events (template)
ID: 80, Name: U-Games, Date: NULL, School Year: NULL

-- default_event_dates (created event)
ID: 1, Event ID: 80, Date: 2026-04-27, School Year: 2025-2026, Semester: 2
```

## Benefits After Migration

✓ **Clear Separation**: Templates vs Created Events
✓ **Proper Structure**: Each table has its specific purpose
✓ **Semester Tracking**: Automatically assigned based on date
✓ **Reusability**: Same template can be used for multiple years
✓ **Better Queries**: Easy to filter by school year and semester

## API Usage After Migration

### Get All Created Events
```bash
GET /api/default-events/v2/scheduled?school_year=2025-2026
```

### Get Events by Semester
```bash
# First Semester
GET /api/default-events/v2/scheduled?school_year=2025-2026&semester=1

# Second Semester (where U-Games will be)
GET /api/default-events/v2/scheduled?school_year=2025-2026&semester=2
```

### Get Statistics
```bash
GET /api/default-events/v2/statistics?school_year=2025-2026
```

Response will show:
```json
{
  "school_year": "2025-2026",
  "total_base_events": 80,
  "events_with_dates": 1,
  "events_without_dates": 79,
  "completion_percentage": 1.25,
  "events_by_semester": {
    "Second Semester": 1
  }
}
```

## Troubleshooting

### Issue: Migration script shows 0 events to migrate
**Solution**: Your events might already be in the correct table. Run `verify-migration-status.php` to check.

### Issue: Events appear in both tables
**Solution**: Run the migration again. It will skip duplicates and clean up `default_events`.

### Issue: Semester field is NULL
**Solution**: Run the semester migration first:
```bash
php artisan migrate --path=database/migrations/2026_03_21_100000_add_semester_to_default_event_dates_table.php
```

## Files Created

1. `backend/migrate-created-events-now.php` - Main migration script
2. `backend/verify-migration-status.php` - Status checker
3. `backend/RUN_MIGRATE_CREATED_EVENTS.bat` - Easy runner
4. `backend/database/migrations/2026_03_21_110000_migrate_created_events_to_dates_table.php` - Laravel migration
5. `MIGRATION_GUIDE_CREATED_EVENTS.md` - This guide

## Summary

Your ID #80 "U-Games" event will be moved from `default_events` to `default_event_dates` where it belongs. After migration, you'll have a clean separation between templates and created events.

Run the migration now to fix your database structure!
