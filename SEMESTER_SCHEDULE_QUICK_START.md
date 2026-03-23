# Semester Schedule Quick Start Guide

## 🚀 Quick Setup (3 Steps)

### Step 1: Run the Migration
```bash
cd backend
php artisan migrate --path=database/migrations/2026_03_21_100000_add_semester_and_school_year_to_user_schedules.php
```

Or double-click:
```
backend/RUN_SCHEDULE_SEMESTER_MIGRATION.bat
```

### Step 2: Test the Implementation
```bash
cd backend
php test-semester-schedule-filtering.php
```

### Step 3: Verify in Browser
1. Go to Account Dashboard
2. Click "Edit Schedule"
3. Add classes for current semester
4. Click "Save Schedule"
5. Verify semester badge shows correct semester

## 📋 What Changed

### Before
- ❌ All schedules mixed together
- ❌ No semester awareness
- ❌ No academic year tracking
- ❌ Includes Sunday

### After
- ✅ Schedules separated by semester
- ✅ Automatic semester detection
- ✅ Academic year tracking (e.g., "2025-2026")
- ✅ Sunday removed (Monday-Saturday only)

## 🎯 Key Features

### 1. Semester Filtering
Each schedule is tied to a specific semester:
- **First Semester**: September - January
- **Second Semester**: February - June
- **Mid-Year**: July - August

### 2. Academic Year Tracking
Schedules include school year (e.g., "2025-2026"):
- Automatically calculated based on current date
- Supports multiple years simultaneously
- Easy to archive old schedules

### 3. Automatic Detection
System automatically:
- Detects current semester
- Calculates school year
- Filters schedules accordingly
- Shows only relevant classes

### 4. No Sunday
Weekly schedule now shows Monday-Saturday only:
- Cleaner interface
- Aligns with academic schedules
- More focused layout

## 📊 Current Semester Logic

```
January (1)     → First Semester  → Previous-Current Year
February (2)    → Second Semester → Previous-Current Year
March (3)       → Second Semester → Previous-Current Year
April (4)       → Second Semester → Previous-Current Year
May (5)         → Second Semester → Previous-Current Year
June (6)        → Second Semester → Previous-Current Year
July (7)        → Mid-Year        → Previous-Current Year
August (8)      → Mid-Year        → Previous-Current Year
September (9)   → First Semester  → Current-Next Year
October (10)    → First Semester  → Current-Next Year
November (11)   → First Semester  → Current-Next Year
December (12)   → First Semester  → Current-Next Year
```

## 🔍 Example Scenarios

### Scenario 1: It's March 21, 2026
- **Current Semester**: Second Semester
- **School Year**: 2025-2026
- **Displayed**: Only classes scheduled for Second Semester 2025-2026
- **Hidden**: First Semester and Mid-Year classes

### Scenario 2: It's September 1, 2026
- **Current Semester**: First Semester
- **School Year**: 2026-2027
- **Displayed**: Only classes scheduled for First Semester 2026-2027
- **Hidden**: Previous year's classes

### Scenario 3: It's July 15, 2026
- **Current Semester**: Mid-Year
- **School Year**: 2025-2026
- **Displayed**: Only mid-year/summer classes
- **Hidden**: Regular semester classes

## 💾 Database Structure

### user_schedules Table
```sql
CREATE TABLE user_schedules (
    id BIGINT PRIMARY KEY,
    user_id BIGINT,
    day VARCHAR(255),
    start_time TIME,
    end_time TIME,
    description VARCHAR(255),
    color VARCHAR(255),
    semester ENUM('first', 'second', 'midyear'),  -- NEW
    school_year VARCHAR(9),                        -- NEW
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_user_semester_year (user_id, semester, school_year)
);
```

## 🔧 API Usage

### Fetch Schedule (Current Semester)
```javascript
GET /api/schedules
// Automatically uses current semester and school year
```

### Fetch Schedule (Specific Semester)
```javascript
GET /api/schedules?semester=first&school_year=2025-2026
```

### Save Schedule
```javascript
POST /api/schedules
{
    "schedule": {
        "Monday": [
            {
                "startTime": "08:00",
                "endTime": "09:30",
                "description": "Math 101"
            }
        ]
    },
    "semester": "first",
    "school_year": "2025-2026"
}
```

## ✅ Verification Checklist

After setup, verify:

- [ ] Migration ran successfully
- [ ] Test script passes all tests
- [ ] Account Dashboard shows semester badge
- [ ] Schedule saves with semester info
- [ ] Only current semester classes display
- [ ] Sunday is not shown in day selector
- [ ] School year displays correctly
- [ ] Edit mode works properly
- [ ] Save button includes semester in message

## 🐛 Troubleshooting

### Issue: Migration fails
**Solution**: Check if columns already exist
```bash
php artisan migrate:status
```

### Issue: Schedules not showing
**Solution**: Check semester and school year match
```bash
php test-semester-schedule-filtering.php
```

### Issue: Wrong semester detected
**Solution**: Verify server date/time is correct
```bash
php -r "echo date('Y-m-d H:i:s');"
```

### Issue: Sunday still showing
**Solution**: Clear browser cache and refresh
```bash
Ctrl + Shift + R (Windows/Linux)
Cmd + Shift + R (Mac)
```

## 📚 Related Files

### Backend
- `backend/database/migrations/2026_03_21_100000_add_semester_and_school_year_to_user_schedules.php`
- `backend/app/Models/UserSchedule.php`
- `backend/app/Http/Controllers/ScheduleController.php`
- `backend/app/Http/Controllers/DashboardController.php`
- `backend/test-semester-schedule-filtering.php`

### Frontend
- `frontend/src/pages/AccountDashboard.jsx`
- `frontend/src/components/Calendar.jsx`
- `frontend/src/pages/Dashboard.jsx`

### Documentation
- `SEMESTER_FILTERING_IMPLEMENTATION.md` - Full technical details
- `CLASS_SCHEDULE_IMPROVEMENTS.md` - UI/UX improvements
- `WEEKLY_SCHEDULE_BEFORE_AFTER.md` - Visual comparison

## 🎉 Success Indicators

You'll know it's working when:
1. ✅ Semester badge shows in header (e.g., "Second Semester")
2. ✅ School year displays (e.g., "2025-2026")
3. ✅ Only 6 days shown (Monday-Saturday)
4. ✅ Save message includes semester info
5. ✅ Different semesters have different schedules
6. ✅ Calendar shows schedule events correctly

## 📞 Support

If you encounter issues:
1. Check the test script output
2. Review browser console for errors
3. Check Laravel logs: `backend/storage/logs/laravel.log`
4. Verify database structure matches migration
5. Ensure frontend and backend are in sync

---

**Ready to use!** The semester filtering and academic year tracking are now fully implemented. 🎓
