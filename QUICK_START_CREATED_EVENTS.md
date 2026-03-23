# Quick Start: Created Default Events

## What You Have

The `default_event_dates` table is your **dedicated table for created default academic events** with dates, school year, and semester.

## Setup (3 Steps)

### Step 1: Run Migration
```bash
cd backend
php artisan migrate
```

This adds the `semester` field to track which semester each event belongs to.

### Step 2: Test
```bash
php backend/test-created-default-events.php
```

### Step 3: Use the API

**Get all created events:**
```bash
GET /api/default-events/v2/scheduled?school_year=2025-2026
```

**Get by semester:**
```bash
GET /api/default-events/v2/scheduled?school_year=2025-2026&semester=1
```

## Understanding the System

### Two Tables

1. **default_events** = Templates (no dates)
   - "Midterm Exams"
   - "Final Exams"
   - "First Day of Classes"

2. **default_event_dates** = Created Events (with dates) ⭐
   - Midterm Exams on Oct 15, 2025 (School Year: 2025-2026, Semester: 1)
   - Final Exams on May 10, 2026 (School Year: 2025-2026, Semester: 2)

### Semester Mapping

- **Semester 1**: Sep, Oct, Nov, Dec, Jan
- **Semester 2**: Feb, Mar, Apr, May, Jun
- **Semester 3**: Jul, Aug (Mid-Year)

## API Endpoints

```
GET    /api/default-events/v2/scheduled          - Get created events
GET    /api/default-events/v2/statistics         - Get statistics
POST   /api/default-events/v2/{id}/set-date      - Create event (Admin)
DELETE /api/default-events/v2/{id}/remove-date   - Delete event (Admin)
```

## Example: Create an Event

```bash
POST /api/default-events/v2/1/set-date
{
  "date": "2025-09-01",
  "end_date": null,
  "school_year": "2025-2026"
}
```

The system automatically determines:
- Month: 9 (September)
- Semester: 1 (First Semester)

## Documentation

- `CREATED_EVENTS_VISUAL_GUIDE.md` - Visual diagrams
- `CREATED_DEFAULT_EVENTS_SYSTEM.md` - Complete technical docs
- `FINAL_CREATED_EVENTS_SUMMARY.md` - Summary

## Done!

Your dedicated table for created default academic events is ready to use.
