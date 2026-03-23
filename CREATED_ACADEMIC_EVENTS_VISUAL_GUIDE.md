# Created Academic Events - Visual Guide

## Before vs After

### BEFORE (Problem)

```
┌─────────────────────────────────────────────────────────┐
│  School Year: 2025-2026                                 │
├─────────────────────────────────────────────────────────┤
│  September Events:                                      │
│  • Opening Ceremony (default)                           │
│  • Custom Event A (created by user) ← PROBLEM          │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│  School Year: 2026-2027                                 │
├─────────────────────────────────────────────────────────┤
│  September Events:                                      │
│  • Opening Ceremony (default)                           │
│  • Custom Event A (created by user) ← APPEARS HERE TOO! │
└─────────────────────────────────────────────────────────┘

❌ Problem: "Custom Event A" appears in ALL school years
```

### AFTER (Solution)

```
┌─────────────────────────────────────────────────────────┐
│  School Year: 2025-2026                                 │
├─────────────────────────────────────────────────────────┤
│  September Events:                                      │
│  • Opening Ceremony (default)                           │
│  • Custom Event A (created for 2025-2026) ✓            │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│  School Year: 2026-2027                                 │
├─────────────────────────────────────────────────────────┤
│  September Events:                                      │
│  • Opening Ceremony (default)                           │
│  (Custom Event A does NOT appear here) ✓                │
└─────────────────────────────────────────────────────────┘

✓ Solution: Events are isolated to their specific school year
```

## Database Structure

### OLD Structure (Problem)

```
default_events table:
┌────┬─────────────────┬───────┬─────────────┐
│ id │ name            │ month │ school_year │
├────┼─────────────────┼───────┼─────────────┤
│ 1  │ Opening Day     │ 9     │ NULL        │ ← Template (OK)
│ 2  │ Custom Event A  │ 9     │ NULL        │ ← Created event (WRONG!)
└────┴─────────────────┴───────┴─────────────┘

❌ Both have school_year = NULL, so both appear everywhere
```

### NEW Structure (Solution)

```
default_events table (templates only):
┌────┬─────────────┬───────┬─────────────┐
│ id │ name        │ month │ school_year │
├────┼─────────────┼───────┼─────────────┤
│ 1  │ Opening Day │ 9     │ NULL        │ ← Template (appears everywhere)
└────┴─────────────┴───────┴─────────────┘

created_academic_events table (user-created):
┌────┬────────────────┬───────┬──────────┬─────────────┐
│ id │ name           │ month │ semester │ school_year │
├────┼────────────────┼───────┼──────────┼─────────────┤
│ 1  │ Custom Event A │ 9     │ 1        │ 2025-2026   │ ← Only in 2025-2026
│ 2  │ Custom Event B │ 10    │ 1        │ 2025-2026   │ ← Only in 2025-2026
│ 3  │ Custom Event C │ 9     │ 1        │ 2026-2027   │ ← Only in 2026-2027
└────┴────────────────┴───────┴──────────┴─────────────┘

✓ Each created event has explicit school_year
```

## User Flow

### Creating an Academic Event

```
┌─────────────────────────────────────────────────────────┐
│ Step 1: Select School Year                              │
│                                                         │
│  [2025-2026 ▼]                                         │
└─────────────────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────────────────┐
│ Step 2: Navigate to Month                               │
│                                                         │
│  September 2025                                         │
│  ┌─────────────────────────────────────────────────┐  │
│  │ • Opening Ceremony (Sept 1-3)                   │  │
│  │ • Faculty Meeting (Sept 5)                      │  │
│  │                                                 │  │
│  │ [+ Create Academic Event]                       │  │
│  └─────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────────────────┐
│ Step 3: Enter Event Name                                │
│                                                         │
│  [Orientation Week 2025        ] [Save] [Cancel]       │
└─────────────────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────────────────┐
│ Step 4: Set Date Range                                  │
│                                                         │
│  Start Date: [Sept 15, 2025 ▼]                         │
│  End Date:   [Sept 16, 2025 ▼]                         │
│                                                         │
│  [Save Date] [Cancel]                                   │
└─────────────────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────────────────┐
│ Result: Event Created                                   │
│                                                         │
│  September 2025                                         │
│  • Opening Ceremony (Sept 1-3)                          │
│  • Faculty Meeting (Sept 5)                             │
│  • Orientation Week 2025 (Sept 15-16) ← NEW!           │
│                                                         │
│  ✓ Only visible in 2025-2026 school year               │
└─────────────────────────────────────────────────────────┘
```

## API Flow

### Creating Event

```
Frontend                    Backend                     Database
   │                           │                            │
   │ POST /created-academic-events                         │
   │ {                         │                            │
   │   name: "Event A",        │                            │
   │   month: 9,               │                            │
   │   school_year: "2025-26"  │                            │
   │ }                         │                            │
   ├──────────────────────────>│                            │
   │                           │                            │
   │                           │ Determine semester (1)     │
   │                           │ Check for duplicates       │
   │                           │ Get max order              │
   │                           │                            │
   │                           │ INSERT INTO                │
   │                           │ created_academic_events    │
   │                           ├───────────────────────────>│
   │                           │                            │
   │                           │<───────────────────────────┤
   │                           │ Event created (ID: 1)      │
   │                           │                            │
   │<──────────────────────────┤                            │
   │ { event: { id: 1, ... } } │                            │
   │                           │                            │
```

### Fetching Events

```
Frontend                    Backend                     Database
   │                           │                            │
   │ GET /default-events?      │                            │
   │     school_year=2025-2026 │                            │
   ├──────────────────────────>│                            │
   │                           │                            │
   │                           │ Query default_events       │
   │                           │ WHERE school_year IS NULL  │
   │                           ├───────────────────────────>│
   │                           │<───────────────────────────┤
   │                           │ [Opening Day, ...]         │
   │                           │                            │
   │                           │ Query default_event_dates  │
   │                           │ WHERE school_year='2025-26'│
   │                           ├───────────────────────────>│
   │                           │<───────────────────────────┤
   │                           │ [dates for templates]      │
   │                           │                            │
   │                           │ Query created_academic_events
   │                           │ WHERE school_year='2025-26'│
   │                           ├───────────────────────────>│
   │                           │<───────────────────────────┤
   │                           │ [Event A, Event B, ...]    │
   │                           │                            │
   │                           │ Merge all events           │
   │                           │ Add is_created flag        │
   │                           │                            │
   │<──────────────────────────┤                            │
   │ { events: [              │                            │
   │   {id: 1, is_created: false},                         │
   │   {id: "created_1", is_created: true},                │
   │   ...                     │                            │
   │ ]}                        │                            │
```

## Event Types Comparison

```
┌─────────────────────────────────────────────────────────────────┐
│                    DEFAULT EVENTS                               │
├─────────────────────────────────────────────────────────────────┤
│ Purpose:      System-wide recurring events                      │
│ Storage:      default_events + default_event_dates              │
│ School Year:  NULL (template) + per-year dates                  │
│ Visibility:   All years (with year-specific dates)              │
│ Created By:   System/Admin (seeded)                             │
│ Can Delete:   No (only remove dates)                            │
│ Example:      "Opening Ceremony" - happens every year           │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                  CREATED ACADEMIC EVENTS                        │
├─────────────────────────────────────────────────────────────────┤
│ Purpose:      User-created one-time events                      │
│ Storage:      created_academic_events                           │
│ School Year:  Specific (e.g., "2025-2026")                      │
│ Visibility:   Only in designated year                           │
│ Created By:   User (tracked in created_by)                      │
│ Can Delete:   Yes                                               │
│ Example:      "Special Workshop 2025" - only for 2025-2026      │
└─────────────────────────────────────────────────────────────────┘
```

## Semester Assignment

```
Month → Semester Mapping:

┌──────────────┬──────────────────────┐
│ Month        │ Semester             │
├──────────────┼──────────────────────┤
│ September    │ 1 (First Semester)   │
│ October      │ 1 (First Semester)   │
│ November     │ 1 (First Semester)   │
│ December     │ 1 (First Semester)   │
│ January      │ 1 (First Semester)   │
├──────────────┼──────────────────────┤
│ February     │ 2 (Second Semester)  │
│ March        │ 2 (Second Semester)  │
│ April        │ 2 (Second Semester)  │
│ May          │ 2 (Second Semester)  │
│ June         │ 2 (Second Semester)  │
├──────────────┼──────────────────────┤
│ July         │ 3 (Mid-Year)         │
│ August       │ 3 (Mid-Year)         │
└──────────────┴──────────────────────┘

Automatically assigned when creating event!
```

## UI Indicators

```
┌─────────────────────────────────────────────────────────┐
│ September 2025                                          │
├─────────────────────────────────────────────────────────┤
│                                                         │
│ 📅 Opening Ceremony                                     │
│    Sept 1-3, 2025                                       │
│    [Edit Date]                                          │
│    ↑ Default Event (cannot delete)                      │
│                                                         │
│ 📅 Orientation Week 2025                                │
│    Sept 15-16, 2025                                     │
│    [Edit Date] [Delete]                                 │
│    ↑ Created Event (can delete)                         │
│                                                         │
│ [+ Create Academic Event]                               │
└─────────────────────────────────────────────────────────┘
```

## Data Isolation Example

```
Database State:

created_academic_events:
┌────┬────────────────────┬───────┬─────────────┐
│ id │ name               │ month │ school_year │
├────┼────────────────────┼───────┼─────────────┤
│ 1  │ Workshop A         │ 9     │ 2024-2025   │
│ 2  │ Seminar B          │ 10    │ 2024-2025   │
│ 3  │ Conference C       │ 9     │ 2025-2026   │
│ 4  │ Training D         │ 11    │ 2025-2026   │
│ 5  │ Meeting E          │ 9     │ 2026-2027   │
└────┴────────────────────┴───────┴─────────────┘

Query: school_year = "2025-2026"
Result: [Conference C, Training D]
        ↑ Only events for 2025-2026

Query: school_year = "2026-2027"
Result: [Meeting E]
        ↑ Only events for 2026-2027

✓ Perfect isolation!
```

## Summary

```
┌─────────────────────────────────────────────────────────┐
│                    KEY BENEFITS                         │
├─────────────────────────────────────────────────────────┤
│ ✓ Events isolated by school year                        │
│ ✓ No cross-year contamination                           │
│ ✓ Clear distinction between templates and created       │
│ ✓ Automatic semester assignment                         │
│ ✓ Duplicate prevention per year                         │
│ ✓ Audit trail (created_by tracking)                     │
│ ✓ Seamless UI integration                               │
└─────────────────────────────────────────────────────────┘
```
