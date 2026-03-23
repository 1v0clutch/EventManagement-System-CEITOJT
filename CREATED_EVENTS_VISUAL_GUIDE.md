# Created Default Events - Visual Guide

## System Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    EVENT MANAGEMENT SYSTEM                   │
└─────────────────────────────────────────────────────────────┘

┌──────────────────────────┐         ┌──────────────────────────┐
│   BASE EVENT TEMPLATES   │         │   CREATED EVENTS         │
│   (default_events)       │────────▶│   (default_event_dates)  │
│                          │         │                          │
│  • Midterm Exams         │         │  2025-2026, Semester 1   │
│  • Final Exams           │         │  Oct 15-20, 2025         │
│  • First Day of Classes  │         │  Created by: Admin       │
│  • Graduation            │         │                          │
│                          │         │  2026-2027, Semester 1   │
│  (No dates, reusable)    │         │  Oct 18-23, 2026         │
│                          │         │  Created by: Admin       │
└──────────────────────────┘         └──────────────────────────┘
     Templates                        Actual Scheduled Events
```

## Data Flow

```
ADMIN CREATES EVENT
        │
        ▼
┌─────────────────────┐
│ 1. Select Template  │  "Midterm Exams"
└─────────────────────┘
        │
        ▼
┌─────────────────────┐
│ 2. Set Date         │  October 15, 2025
└─────────────────────┘
        │
        ▼
┌─────────────────────┐
│ 3. Auto-Determine   │  Semester: 1 (First)
│    Semester         │  School Year: 2025-2026
└─────────────────────┘
        │
        ▼
┌─────────────────────┐
│ 4. Save to          │  default_event_dates
│    Created Events   │  (Your dedicated table!)
└─────────────────────┘
```

## Table Comparison

### default_events (Templates)
```
┌────┬──────────────────┬───────┬───────┬─────────────┐
│ id │ name             │ month │ order │ school_year │
├────┼──────────────────┼───────┼───────┼─────────────┤
│ 1  │ Midterm Exams    │ 10    │ 5     │ NULL        │
│ 2  │ Final Exams      │ 5     │ 10    │ NULL        │
│ 3  │ First Day        │ 9     │ 1     │ NULL        │
└────┴──────────────────┴───────┴───────┴─────────────┘
         ↑ Reusable templates, no specific dates
```

### default_event_dates (Created Events) ⭐
```
┌────┬─────────────┬─────────────┬──────────┬────────────┬────────────┬──────────────┐
│ id │ event_id    │ school_year │ semester │ date       │ end_date   │ created_by   │
├────┼─────────────┼─────────────┼──────────┼────────────┼────────────┼──────────────┤
│ 10 │ 1           │ 2025-2026   │ 1        │ 2025-10-15 │ 2025-10-20 │ 5            │
│ 11 │ 1           │ 2026-2027   │ 1        │ 2026-10-18 │ 2026-10-23 │ 5            │
│ 12 │ 2           │ 2025-2026   │ 2        │ 2026-05-10 │ 2026-05-15 │ 5            │
│ 13 │ 3           │ 2025-2026   │ 1        │ 2025-09-01 │ NULL       │ 5            │
└────┴─────────────┴─────────────┴──────────┴────────────┴────────────┴──────────────┘
         ↑ Actual events with dates, school year, and semester
```

## Semester Mapping

```
SCHOOL YEAR: 2025-2026
═══════════════════════════════════════════════════════════

┌─────────────────────────────────────────────────────────┐
│ FIRST SEMESTER (semester = 1)                           │
├─────────────────────────────────────────────────────────┤
│ September 2025                                          │
│ October 2025                                            │
│ November 2025                                           │
│ December 2025                                           │
│ January 2026                                            │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ SECOND SEMESTER (semester = 2)                          │
├─────────────────────────────────────────────────────────┤
│ February 2026                                           │
│ March 2026                                              │
│ April 2026                                              │
│ May 2026                                                │
│ June 2026                                               │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ MID-YEAR (semester = 3)                                 │
├─────────────────────────────────────────────────────────┤
│ July 2026                                               │
│ August 2026                                             │
└─────────────────────────────────────────────────────────┘
```

## Query Examples

### Get All Created Events for 2025-2026
```sql
SELECT 
    ded.id,
    de.name,
    ded.date,
    ded.end_date,
    ded.semester,
    ded.school_year
FROM default_event_dates ded
JOIN default_events de ON ded.default_event_id = de.id
WHERE ded.school_year = '2025-2026'
ORDER BY ded.date;
```

### Get Only First Semester Events
```sql
SELECT 
    ded.id,
    de.name,
    ded.date,
    ded.semester
FROM default_event_dates ded
JOIN default_events de ON ded.default_event_id = de.id
WHERE ded.school_year = '2025-2026'
  AND ded.semester = 1
ORDER BY ded.date;
```

### Count Events by Semester
```sql
SELECT 
    semester,
    COUNT(*) as event_count,
    CASE semester
        WHEN 1 THEN 'First Semester'
        WHEN 2 THEN 'Second Semester'
        WHEN 3 THEN 'Mid-Year'
    END as semester_name
FROM default_event_dates
WHERE school_year = '2025-2026'
GROUP BY semester
ORDER BY semester;
```

## API Response Example

### GET /api/default-events/v2/scheduled?school_year=2025-2026&semester=1

```json
{
  "events": [
    {
      "id": 13,
      "event_id": 3,
      "name": "First Day of Classes",
      "date": "2025-09-01",
      "end_date": null,
      "month": 9,
      "semester": 1,
      "semester_name": "First Semester",
      "school_year": "2025-2026",
      "created_at": "2026-03-21 10:00:00"
    },
    {
      "id": 10,
      "event_id": 1,
      "name": "Midterm Exams",
      "date": "2025-10-15",
      "end_date": "2025-10-20",
      "month": 10,
      "semester": 1,
      "semester_name": "First Semester",
      "school_year": "2025-2026",
      "created_at": "2026-03-21 10:00:00"
    }
  ],
  "count": 2
}
```

## Key Points

✓ **default_event_dates IS your "Created Default Events" table**
✓ Each row represents an actual scheduled event
✓ Includes school year, semester, and specific dates
✓ Tracks who created it and when
✓ One base template can have multiple created instances
✓ Semester is automatically determined from the date
