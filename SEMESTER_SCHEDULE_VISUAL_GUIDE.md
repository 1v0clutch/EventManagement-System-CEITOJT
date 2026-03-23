# Semester Schedule Visual Guide

## 🎨 UI Transformation

### Header Section

#### BEFORE
```
┌────────────────────────────────────────────────────┐
│ Weekly Schedule                    [Edit Schedule] │
│ 0 classes scheduled this week                      │
└────────────────────────────────────────────────────┘
```

#### AFTER
```
┌────────────────────────────────────────────────────┐
│ 🕐 Weekly Schedule  📅 Second Semester             │
│                                    [Edit Schedule] │
│ 5 classes scheduled this week • February - June   │
└────────────────────────────────────────────────────┘
```

### Day Selector

#### BEFORE (7 Days)
```
┌─────────┐
│ Monday  │ 0
├─────────┤
│ Tuesday │ 0
├─────────┤
│Wednesday│ 0
├─────────┤
│Thursday │ 0
├─────────┤
│ Friday  │ 0
├─────────┤
│Saturday │ 0
├─────────┤
│ Sunday  │ 0  ← REMOVED
└─────────┘
```

#### AFTER (6 Days)
```
┌─────────┐
│ Monday  │ 2  ← Class count badge
├─────────┤
│ Tuesday │ 1
├─────────┤
│Wednesday│ 0
├─────────┤
│Thursday │ 2
├─────────┤
│ Friday  │ 0
├─────────┤
│Saturday │ 0
└─────────┘
```

### Schedule Display

#### BEFORE
```
┌────────────────────────────────────────┐
│ Monday Schedule                        │
├────────────────────────────────────────┤
│ • 8:00 AM - 9:30 AM: Math 101         │
│ • 10:00 AM - 11:30 AM: Physics Lab    │
└────────────────────────────────────────┘
```

#### AFTER
```
┌────────────────────────────────────────────────────┐
│ Monday Schedule                    [+ Add Class]   │
├──────────────────────┬─────────────────────────────┤
│ Time Range           │ Class Description           │
├──────────────────────┼─────────────────────────────┤
│ 🕐 8:00 AM - 9:30 AM │ Mathematics 101             │
├──────────────────────┼─────────────────────────────┤
│ 🕐 10:00 AM-11:30 AM │ Physics Lab                 │
└──────────────────────┴─────────────────────────────┘
```

## 📅 Semester Timeline

```
Academic Year 2025-2026
═══════════════════════════════════════════════════════

FIRST SEMESTER (Sept - Jan)
├─ September 2025
├─ October 2025
├─ November 2025
├─ December 2025
└─ January 2026

SECOND SEMESTER (Feb - June)
├─ February 2026
├─ March 2026
├─ April 2026
├─ May 2026
└─ June 2026

MID-YEAR/SUMMER (July - Aug)
├─ July 2026
└─ August 2026

═══════════════════════════════════════════════════════
Academic Year 2026-2027 (Next Year)
```

## 🔄 Data Flow

### Saving Schedule

```
User Interface
     │
     │ 1. User clicks "Save Schedule"
     ▼
┌─────────────────────────────────────┐
│ Frontend (AccountDashboard.jsx)    │
│                                     │
│ Collects:                           │
│ • Schedule data (days/times)        │
│ • Current semester (e.g., "first")  │
│ • School year (e.g., "2025-2026")   │
└─────────────────────────────────────┘
     │
     │ 2. POST /api/schedules
     ▼
┌─────────────────────────────────────┐
│ Backend (ScheduleController.php)    │
│                                     │
│ Validates:                          │
│ • Days (Monday-Saturday only)       │
│ • Times (start < end)               │
│ • Semester (first/second/midyear)   │
│ • School year (YYYY-YYYY format)    │
└─────────────────────────────────────┘
     │
     │ 3. Delete old + Insert new
     ▼
┌─────────────────────────────────────┐
│ Database (user_schedules table)     │
│                                     │
│ Stores:                             │
│ • user_id                           │
│ • day, start_time, end_time         │
│ • description, color                │
│ • semester, school_year             │
└─────────────────────────────────────┘
     │
     │ 4. Success response
     ▼
User sees: "Schedule saved for Second Semester (2025-2026)!"
```

### Loading Schedule

```
User visits Account Dashboard
     │
     │ 1. Detect current semester
     ▼
┌─────────────────────────────────────┐
│ Frontend (AccountDashboard.jsx)    │
│                                     │
│ Calculates:                         │
│ • Current month → Semester          │
│ • Current year → School year        │
└─────────────────────────────────────┘
     │
     │ 2. GET /api/schedules?semester=second&school_year=2025-2026
     ▼
┌─────────────────────────────────────┐
│ Backend (ScheduleController.php)    │
│                                     │
│ Queries:                            │
│ WHERE user_id = ?                   │
│   AND semester = 'second'           │
│   AND school_year = '2025-2026'     │
└─────────────────────────────────────┘
     │
     │ 3. Returns filtered schedules
     ▼
┌─────────────────────────────────────┐
│ Frontend displays:                  │
│ • Only Second Semester classes      │
│ • Only 2025-2026 school year        │
│ • Monday-Saturday (no Sunday)       │
└─────────────────────────────────────┘
```

## 🎯 Semester Detection Examples

### Example 1: March 21, 2026 (Today)
```
Current Date: 2026-03-21
Current Month: 3 (March)

Semester Logic:
├─ Month 3 is between 2 and 6
└─ Result: SECOND SEMESTER

School Year Logic:
├─ Month 3 is before September
└─ Result: 2025-2026

Display:
┌────────────────────────────────────┐
│ 📅 Second Semester                 │
│ February - June                    │
│ School Year: 2025-2026             │
└────────────────────────────────────┘
```

### Example 2: September 1, 2026
```
Current Date: 2026-09-01
Current Month: 9 (September)

Semester Logic:
├─ Month 9 is >= 9
└─ Result: FIRST SEMESTER

School Year Logic:
├─ Month 9 is September or later
└─ Result: 2026-2027

Display:
┌────────────────────────────────────┐
│ 📅 First Semester                  │
│ September - January                │
│ School Year: 2026-2027             │
└────────────────────────────────────┘
```

### Example 3: July 15, 2026
```
Current Date: 2026-07-15
Current Month: 7 (July)

Semester Logic:
├─ Month 7 is between 7 and 8
└─ Result: MID-YEAR

School Year Logic:
├─ Month 7 is before September
└─ Result: 2025-2026

Display:
┌────────────────────────────────────┐
│ 📅 Mid-Year Semester               │
│ July - August                      │
│ School Year: 2025-2026             │
└────────────────────────────────────┘
```

## 📊 Database Structure

### user_schedules Table

```
┌────────────────────────────────────────────────────────────┐
│ user_schedules                                             │
├────────────┬──────────────┬──────────────┬────────────────┤
│ Column     │ Type         │ Example      │ Description    │
├────────────┼──────────────┼──────────────┼────────────────┤
│ id         │ BIGINT       │ 1            │ Primary key    │
│ user_id    │ BIGINT       │ 42           │ User reference │
│ day        │ VARCHAR(255) │ "Monday"     │ Day of week    │
│ start_time │ TIME         │ 08:00:00     │ Class start    │
│ end_time   │ TIME         │ 09:30:00     │ Class end      │
│ description│ VARCHAR(255) │ "Math 101"   │ Class name     │
│ color      │ VARCHAR(255) │ "#10b981"    │ Display color  │
│ semester   │ ENUM         │ "first"      │ NEW: Semester  │
│ school_year│ VARCHAR(9)   │ "2025-2026"  │ NEW: Year      │
│ created_at │ TIMESTAMP    │ 2026-03-21   │ Created date   │
│ updated_at │ TIMESTAMP    │ 2026-03-21   │ Updated date   │
└────────────┴──────────────┴──────────────┴────────────────┘

Indexes:
├─ PRIMARY KEY (id)
├─ FOREIGN KEY (user_id) REFERENCES users(id)
└─ INDEX (user_id, semester, school_year)  ← NEW: Fast queries
```

### Sample Data

```sql
-- First Semester 2025-2026
INSERT INTO user_schedules VALUES
(1, 42, 'Monday', '08:00:00', '09:30:00', 'Math 101', '#10b981', 'first', '2025-2026', NOW(), NOW()),
(2, 42, 'Wednesday', '10:00:00', '11:30:00', 'Physics Lab', '#3b82f6', 'first', '2025-2026', NOW(), NOW());

-- Second Semester 2025-2026
INSERT INTO user_schedules VALUES
(3, 42, 'Tuesday', '09:00:00', '10:30:00', 'Chemistry 201', '#f59e0b', 'second', '2025-2026', NOW(), NOW()),
(4, 42, 'Thursday', '13:00:00', '14:30:00', 'Biology Lab', '#ef4444', 'second', '2025-2026', NOW(), NOW());

-- Mid-Year 2025-2026
INSERT INTO user_schedules VALUES
(5, 42, 'Friday', '08:00:00', '12:00:00', 'Summer Workshop', '#8b5cf6', 'midyear', '2025-2026', NOW(), NOW());
```

## 🔍 Query Examples

### Get Current Semester Schedule
```sql
-- Assuming current semester is 'second' and school year is '2025-2026'
SELECT * FROM user_schedules
WHERE user_id = 42
  AND semester = 'second'
  AND school_year = '2025-2026'
ORDER BY 
  FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'),
  start_time;
```

### Get All Schedules for a School Year
```sql
SELECT semester, day, description, start_time, end_time
FROM user_schedules
WHERE user_id = 42
  AND school_year = '2025-2026'
ORDER BY 
  FIELD(semester, 'first', 'second', 'midyear'),
  FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'),
  start_time;
```

### Count Classes per Semester
```sql
SELECT 
  semester,
  school_year,
  COUNT(*) as class_count
FROM user_schedules
WHERE user_id = 42
GROUP BY semester, school_year
ORDER BY school_year, FIELD(semester, 'first', 'second', 'midyear');
```

## 🎨 Color Coding

### Semester Colors (UI)
```
First Semester:   🟢 Green (#10b981)
Second Semester:  🔵 Blue (#3b82f6)
Mid-Year:         🟣 Purple (#8b5cf6)
```

### Class Colors (Auto-assigned)
```
Same class name = Same color across all instances

Example:
"Math 101" → Always #10b981 (Green)
"Physics Lab" → Always #3b82f6 (Blue)
"Chemistry 201" → Always #f59e0b (Amber)
```

## ✅ Validation Rules

### Day Validation
```
✅ Allowed: Monday, Tuesday, Wednesday, Thursday, Friday, Saturday
❌ Not Allowed: Sunday
```

### Time Validation
```
✅ Valid: "08:00", "13:30", "8:00 AM", "1:30 PM"
❌ Invalid: "25:00", "8:60", "invalid"

Rule: start_time < end_time
```

### Semester Validation
```
✅ Valid: "first", "second", "midyear"
❌ Invalid: "third", "summer", "fall"
```

### School Year Validation
```
✅ Valid: "2025-2026", "2026-2027"
❌ Invalid: "2025", "25-26", "2025/2026"

Format: YYYY-YYYY (exactly 9 characters)
```

## 🚀 Performance Optimizations

### Database Index
```sql
CREATE INDEX idx_user_semester_year 
ON user_schedules (user_id, semester, school_year);

Benefits:
├─ Fast semester filtering
├─ Quick school year queries
└─ Efficient user schedule lookups
```

### Frontend Caching
```javascript
Cache Key Format:
`schedule:${userId}:${semester}:${schoolYear}`

Examples:
- schedule:42:first:2025-2026
- schedule:42:second:2025-2026
- schedule:42:midyear:2025-2026

Benefits:
├─ Instant load from cache
├─ Background refresh
└─ Reduced API calls
```

## 📱 Responsive Design

### Desktop View
```
┌─────────────────────────────────────────────────────────┐
│ Day Selector (Left)    │  Schedule Display (Right)      │
│                        │                                 │
│ ┌─────────┐           │  ┌──────────────────────────┐  │
│ │ Monday  │ 2         │  │ Monday Schedule          │  │
│ └─────────┘           │  │                          │  │
│ ┌─────────┐           │  │ Time    │ Description    │  │
│ │ Tuesday │ 1         │  │ 8:00 AM │ Math 101       │  │
│ └─────────┘           │  │ 10:00AM │ Physics Lab    │  │
│ ...                   │  └──────────────────────────┘  │
└─────────────────────────────────────────────────────────┘
```

### Mobile View
```
┌──────────────────────┐
│ Day Selector (Top)   │
│ [Mon] [Tue] [Wed]... │
└──────────────────────┘
┌──────────────────────┐
│ Schedule (Bottom)    │
│                      │
│ Monday Schedule      │
│ ┌──────────────────┐ │
│ │ 8:00 AM          │ │
│ │ Math 101         │ │
│ └──────────────────┘ │
└──────────────────────┘
```

## 🎉 Success Indicators

### Visual Confirmation
```
✅ Semester badge visible in header
✅ School year displayed
✅ Only 6 days shown (Mon-Sat)
✅ Class count badges on each day
✅ Green theme consistent
✅ Empty states helpful
✅ Loading states smooth
```

### Functional Confirmation
```
✅ Save includes semester info
✅ Fetch filters by semester
✅ Different semesters isolated
✅ Calendar shows schedule events
✅ No Sunday events displayed
✅ Performance maintained
```

---

**The semester schedule system is now fully visual and functional!** 🎓✨
