# Before & After Migration - Visual Guide

## Your Current Problem (From Screenshot)

### BEFORE MIGRATION ❌

```
┌─────────────────────────────────────────────────────────────────┐
│ default_events table (MIXED - Templates + Created Events)      │
├────┬──────────┬───────┬────────────┬────────────┬─────────────┤
│ id │ name     │ month │ date       │ end_date   │ school_year │
├────┼──────────┼───────┼────────────┼────────────┼─────────────┤
│ 26 │ Event A  │ 5     │ NULL       │ NULL       │ NULL        │ ← Template
│ 27 │ Event B  │ 5     │ NULL       │ NULL       │ NULL        │ ← Template
│ 80 │ U-Games  │ 4     │ 2026-04-27 │ 2026-05-09 │ 2025-2026   │ ← Created (WRONG!)
│ 68 │ Event C  │ 7     │ NULL       │ NULL       │ NULL        │ ← Template
└────┴──────────┴───────┴────────────┴────────────┴─────────────┘
                           ↑ ID #80 has dates - shouldn't be here!

┌─────────────────────────────────────────────────────────────────┐
│ default_event_dates table (EMPTY)                              │
├────┬─────────────┬─────────────┬──────────┬──────┬──────────┤
│ id │ event_id    │ school_year │ semester │ date │ end_date │
├────┼─────────────┼─────────────┼──────────┼──────┼──────────┤
│    │             │             │          │      │          │ ← EMPTY!
└────┴─────────────┴─────────────┴──────────┴──────┴──────────┘
```

### AFTER MIGRATION ✓

```
┌─────────────────────────────────────────────────────────────────┐
│ default_events table (CLEAN - Only Templates)                  │
├────┬──────────┬───────┬──────┬──────────┬─────────────┤
│ id │ name     │ month │ date │ end_date │ school_year │
├────┼──────────┼───────┼──────┼──────────┼─────────────┤
│ 26 │ Event A  │ 5     │ NULL │ NULL     │ NULL        │ ← Template
│ 27 │ Event B  │ 5     │ NULL │ NULL     │ NULL        │ ← Template
│ 80 │ U-Games  │ 4     │ NULL │ NULL     │ NULL        │ ← Template (cleaned)
│ 68 │ Event C  │ 7     │ NULL │ NULL     │ NULL        │ ← Template
└────┴──────────┴───────┴──────┴──────────┴─────────────┘
                           ↑ All dates removed - pure templates

┌─────────────────────────────────────────────────────────────────────────┐
│ default_event_dates table (POPULATED - Created Events)                 │
├────┬──────────┬─────────────┬──────────┬────────────┬────────────┤
│ id │ event_id │ school_year │ semester │ date       │ end_date   │
├────┼──────────┼─────────────┼──────────┼────────────┼────────────┤
│ 1  │ 80       │ 2025-2026   │ 2        │ 2026-04-27 │ 2026-05-09 │
└────┴──────────┴─────────────┴──────────┴────────────┴────────────┘
       ↑ Links to template #80    ↑ Second Semester (April = month 4)
```

## The Transformation

```
ID #80 "U-Games" BEFORE:
═══════════════════════════════════════════════════════════
Table: default_events
├─ id: 80
├─ name: U-Games
├─ month: 4
├─ date: 2026-04-27          ← Has date (WRONG!)
├─ end_date: 2026-05-09      ← Has end date (WRONG!)
└─ school_year: 2025-2026    ← Has school year (WRONG!)

Problem: This is a CREATED event, not a template!


ID #80 "U-Games" AFTER:
═══════════════════════════════════════════════════════════
Table: default_events (Template)
├─ id: 80
├─ name: U-Games
├─ month: 4
├─ date: NULL                ← Cleaned
├─ end_date: NULL            ← Cleaned
└─ school_year: NULL         ← Cleaned

Table: default_event_dates (Created Event)
├─ id: 1
├─ default_event_id: 80      ← Links to template
├─ school_year: 2025-2026    ← Moved here
├─ semester: 2               ← Auto-calculated (April = 2nd Sem)
├─ date: 2026-04-27          ← Moved here
├─ end_date: 2026-05-09      ← Moved here
└─ month: 4

✓ Template and Created Event are now separate!
```

## How to Run Migration

```bash
cd backend
RUN_MIGRATE_CREATED_EVENTS.bat
```

## What You'll See

```
=== Migrate Created Events to default_event_dates ===

1. Checking table structure...
   ✓ Semester column exists

2. Finding created events in default_events table...
   Found 1 created events

3. Events to migrate:
   ID 80: U-Games
      Date: 2026-04-27 to 2026-05-09
      School Year: 2025-2026 | Semester: Second Semester

4. Migrating events...
   ✓ Migrated ID 80: U-Games

5. Cleaning up default_events table...
   ✓ Cleaned 1 events (converted back to templates)

=== Migration Complete! ===

Summary:
   Migrated: 1 events
   Skipped: 0 events
   Total in default_event_dates: 1
   Templates in default_events: 80

✓ Your created events are now in the default_event_dates table!
```

## Verification

```bash
php verify-migration-status.php
```

Expected output:
```
1. DEFAULT_EVENTS TABLE (Should contain only templates)
   Total events: 80
   With dates (SHOULD BE 0): 0
   Without dates (templates): 80
   ✓ GOOD: No events with dates. All are templates.

2. DEFAULT_EVENT_DATES TABLE (Should contain created events)
   Total created events: 1
   ✓ GOOD: Found 1 created events.

3. MIGRATION STATUS
   ✓✓✓ PERFECT! Migration is complete.
```

## Summary

Run the migration to move ID #80 from the wrong table to the correct table!
