# Semester Filtering & Academic Year Implementation

## Overview
Implemented comprehensive semester filtering and academic year tracking for the weekly schedule system. Each schedule is now tied to a specific semester (first, second, or midyear) and school year (e.g., "2025-2026").

## Database Changes

### Migration: Add Semester and School Year Columns
**File**: `backend/database/migrations/2026_03_21_100000_add_semester_and_school_year_to_user_schedules.php`

Added two new columns to `user_schedules` table:
- `semester`: ENUM('first', 'second', 'midyear') - Tracks which semester the schedule belongs to
- `school_year`: VARCHAR(9) - Tracks the academic year (format: "YYYY-YYYY")
- Added composite index on `(user_id, semester, school_year)` for faster queries

### Run Migration
```bash
cd backend
php artisan migrate --path=database/migrations/2026_03_21_100000_add_semester_and_school_year_to_user_schedules.php
```

Or use the batch file:
```bash
backend/RUN_SCHEDULE_SEMESTER_MIGRATION.bat
```

## Backend Changes

### 1. UserSchedule Model
**File**: `backend/app/Models/UserSchedule.php`

Updated fillable fields to include:
```php
protected $fillable = [
    'user_id',
    'day',
    'start_time',
    'end_time',
    'description',
    'color',
    'semester',      // NEW
    'school_year'    // NEW
];
```

### 2. ScheduleController Updates
**File**: `backend/app/Http/Controllers/ScheduleController.php`

#### index() Method - Fetch Schedules
Now accepts query parameters for semester and school year:
```php
public function index(Request $request)
{
    // Get semester and school year from request, or calculate current
    $semester = $request->query('semester');
    $schoolYear = $request->query('school_year');
    
    // Auto-detect if not provided
    if (!$semester || !$schoolYear) {
        // Calculate current semester and school year
    }
    
    // Fetch schedules filtered by semester and school year
    $schedules = UserSchedule::where('user_id', $user->id)
        ->where('semester', $semester)
        ->where('school_year', $schoolYear)
        ->get();
}
```

#### store() Method - Save Schedules
Now requires semester and school year in request:
```php
public function store(Request $request)
{
    $request->validate([
        'schedule' => 'required|array',
        'semester' => 'required|in:first,second,midyear',
        'school_year' => 'required|string|regex:/^\d{4}-\d{4}$/'
    ]);
    
    // Delete existing schedules for this semester/year only
    UserSchedule::where('user_id', $user->id)
        ->where('semester', $semester)
        ->where('school_year', $schoolYear)
        ->delete();
    
    // Insert new schedules with semester and school year
}
```

### 3. DashboardController Updates
**File**: `backend/app/Http/Controllers/DashboardController.php`

Updated to filter schedules by current semester and school year:
```php
// Fetch user schedules for current semester and school year only
$userSchedules = UserSchedule::where('user_id', $user->id)
    ->where('semester', $currentSemester)
    ->where('school_year', $schoolYear)
    ->get();
```

## Frontend Changes

### AccountDashboard.jsx Updates
**File**: `frontend/src/pages/AccountDashboard.jsx`

#### fetchSchedule() Function
Now includes semester and school year in API request:
```javascript
const fetchSchedule = async () => {
    const cacheKey = `schedule:${user?.id}:${currentSemester.value}:${currentSemester.schoolYear}`;
    
    const response = await api.get(
        `/schedules?semester=${currentSemester.value}&school_year=${currentSemester.schoolYear}`
    );
};
```

#### handleScheduleSave() Function
Now sends semester and school year with schedule data:
```javascript
const handleScheduleSave = async () => {
    const response = await api.post('/schedules', { 
        schedule,
        semester: currentSemester.value,
        school_year: currentSemester.schoolYear
    });
};
```

## Semester Detection Logic

### Semester Periods
- **First Semester**: September (9) to January (1)
- **Second Semester**: February (2) to June (6)
- **Mid-Year/Summer**: July (7) to August (8)

### School Year Calculation
```javascript
// If current month is September or later, school year is current-next
// Otherwise, school year is previous-current
const schoolYear = currentMonth >= 9 
    ? `${currentYear}-${currentYear + 1}`
    : `${currentYear - 1}-${currentYear}`;
```

### Examples
| Current Date | Semester | School Year |
|-------------|----------|-------------|
| 2026-01-15 | first | 2025-2026 |
| 2026-03-20 | second | 2025-2026 |
| 2026-07-10 | midyear | 2025-2026 |
| 2026-09-01 | first | 2026-2027 |
| 2026-12-15 | first | 2026-2027 |

## Features

### 1. Semester Isolation
- Each semester has its own independent schedule
- Changing schedule for one semester doesn't affect others
- Users can have different classes in different semesters

### 2. Academic Year Tracking
- Schedules are tied to specific school years
- Supports multiple school years simultaneously
- Easy to archive old schedules

### 3. Automatic Filtering
- System automatically detects current semester
- Only shows schedules relevant to current period
- No manual semester selection needed

### 4. Data Integrity
- Composite index ensures fast queries
- Proper validation on semester and school year
- Transaction-based saves prevent data corruption

## API Endpoints

### GET /api/schedules
**Query Parameters:**
- `semester` (optional): first, second, or midyear
- `school_year` (optional): Format "YYYY-YYYY"

**Response:**
```json
{
    "schedule": {
        "Monday": [
            {
                "id": 1,
                "startTime": "08:00",
                "endTime": "09:30",
                "description": "Mathematics 101",
                "color": "#10b981",
                "semester": "first",
                "schoolYear": "2025-2026"
            }
        ]
    },
    "initialized": true,
    "semester": "first",
    "schoolYear": "2025-2026"
}
```

### POST /api/schedules
**Request Body:**
```json
{
    "schedule": {
        "Monday": [
            {
                "startTime": "08:00",
                "endTime": "09:30",
                "description": "Mathematics 101"
            }
        ]
    },
    "semester": "first",
    "school_year": "2025-2026"
}
```

**Response:**
```json
{
    "message": "Schedule saved successfully",
    "count": 5,
    "semester": "first",
    "schoolYear": "2025-2026"
}
```

## Testing

### Test File
**File**: `backend/test-semester-schedule-filtering.php`

Run the test:
```bash
cd backend
php test-semester-schedule-filtering.php
```

### Test Coverage
1. ✅ Create schedules for different semesters
2. ✅ Query schedules by semester
3. ✅ Verify semester isolation
4. ✅ Test current semester detection
5. ✅ Retrieve current semester schedule
6. ✅ Test multiple school years

## Benefits

### For Users
- Clear separation between semester schedules
- No confusion about which classes are current
- Easy to plan ahead for future semesters
- Historical schedules preserved

### For System
- Better data organization
- Faster queries with proper indexing
- Easier to implement semester-specific features
- Supports multi-year planning

### For Maintenance
- Clear data structure
- Easy to archive old data
- Simple to add new semesters
- Scalable for future needs

## Migration Path

### For Existing Data
Existing schedules without semester/school year will:
1. Default to 'first' semester
2. Need school year to be set manually or via migration script
3. Continue to work but should be updated

### Recommended Steps
1. Run the migration to add columns
2. Run test script to verify functionality
3. Update existing schedules with proper semester/year
4. Deploy frontend changes
5. Notify users to review their schedules

## Summary

Successfully implemented:
- ✅ Semester filtering (first, second, midyear)
- ✅ Academic year tracking (YYYY-YYYY format)
- ✅ Automatic semester detection
- ✅ Isolated schedules per semester/year
- ✅ Backend validation and filtering
- ✅ Frontend integration
- ✅ Comprehensive testing
- ✅ Database indexing for performance

The weekly schedule system now properly handles semester-based scheduling with full academic year support!
