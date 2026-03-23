# Fix: Created Events in Wrong Table

## The Issue You Found

Looking at your screenshot, **ID #80 "U-Games"** is stored in the `default_events` table with:
- date: 2026-04-27
- end_date: 2026-05-09
- school_year: 2025-2026

But the `default_event_dates` table is **empty**.

This is backwards! Created events should be in `default_event_dates`, not `default_events`.

## Why This Happened

Your system was originally storing created events directly in `default_events`. The `default_event_dates` table was added later but the existing data wasn't migrated.

## The Fix (3 Simple Steps)

### 1. Check Status
```bash
cd backend
php verify-migration-status.php
```

This shows you what's in each table.

### 2. Run Migration
```bash
RUN_MIGRATE_CREATED_EVENTS.bat
```

Or:
```bash
php migrate-created-events-now.php
```

### 3. Verify
```bash
php verify-migration-status.php
```

Should show:
- ✓ default_events: Only templates (no dates)
- ✓ default_event_dates: All created events with dates

## What Happens to ID #80

**Before Migration:**
```
default_events:
  ID: 80
  Name: U-Games
  Date: 2026-04-27
  End Date: 2026-05-09
  School Year: 2025-2026
  ↑ WRONG TABLE!

default_event_dates:
  (empty)
```

**After Migration:**
```
default_events:
  ID: 80
  Name: U-Games
  Date: NULL
  End Date: NULL
  School Year: NULL
  ↑ Now a template

default_event_dates:
  ID: 1
  Event ID: 80 (links to template)
  Date: 2026-04-27
  End Date: 2026-05-09
  School Year: 2025-2026
  Semester: 2 (Second Semester - auto-calculated)
  ↑ CORRECT TABLE!
```

## What the Migration Does

1. Finds all events with dates in `default_events`
2. Copies them to `default_event_dates`
3. Adds semester field automatically
4. Removes dates from `default_events` (converts back to templates)

## Safe to Run

- ✓ Checks for duplicates (won't create duplicates)
- ✓ Preserves all data
- ✓ Can be reversed if needed
- ✓ Shows detailed progress

## After Migration

Your API calls will work correctly:

```bash
# Get all created events for 2025-2026
GET /api/default-events/v2/scheduled?school_year=2025-2026

# Response will include U-Games:
{
  "events": [
    {
      "id": 1,
      "event_id": 80,
      "name": "U-Games",
      "date": "2026-04-27",
      "end_date": "2026-05-09",
      "semester": 2,
      "semester_name": "Second Semester",
      "school_year": "2025-2026"
    }
  ]
}
```

## Quick Start

```bash
cd backend
RUN_MIGRATE_CREATED_EVENTS.bat
```

That's it! Your created events will be in the correct table.

## Documentation

- `MIGRATION_GUIDE_CREATED_EVENTS.md` - Detailed guide
- `CREATED_EVENTS_VISUAL_GUIDE.md` - Visual diagrams
- `QUICK_START_CREATED_EVENTS.md` - Quick reference

## Summary

Run the migration to move ID #80 and all other created events from `default_events` to `default_event_dates` where they belong!
