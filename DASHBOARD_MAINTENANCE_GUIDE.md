# Dashboard Maintenance Guide

## Overview
This guide helps maintain the Dashboard component and prevent common bugs.

## Critical Areas to Watch

### 1. Semester Logic
**Location:** `frontend/src/pages/Dashboard.jsx` - `handleDateSelect` function

**Rule:** Always determine semester based on the **selected date**, not today's date.

**Correct Pattern:**
```javascript
// ✓ CORRECT: Use the selected date
const dateMonth = checkDate.getMonth() + 1;
let selectedDateSemester;

if (dateMonth >= 9 || dateMonth <= 1) {
  selectedDateSemester = 'first';
} else if (dateMonth >= 2 && dateMonth <= 6) {
  selectedDateSemester = 'second';
} else if (dateMonth >= 7 && dateMonth <= 8) {
  selectedDateSemester = 'midyear';
}

// Filter by the selected date's semester
return schedule.semester === selectedDateSemester;
```

**Incorrect Pattern:**
```javascript
// ✗ WRONG: Using today's date
const currentDate = new Date(); // Don't do this!
const currentMonth = currentDate.getMonth() + 1;
// ... determining semester from current date
```

### 2. Date Validation
**Location:** Multiple places in `Dashboard.jsx`

**Always validate dates before processing:**
```javascript
const checkDate = new Date(date);
if (isNaN(checkDate.getTime())) {
  console.error('Invalid date:', date);
  return; // or handle gracefully
}
```

### 3. Error Handling
**Pattern:** Wrap date operations in try-catch blocks

```javascript
try {
  const eventStartDate = new Date(defEvent.date);
  // ... date operations
} catch (error) {
  console.error('Error processing date:', error);
  return false; // or handle gracefully
}
```

### 4. Schedule Filtering
**Backend:** `backend/app/Http/Controllers/DashboardController.php`

The backend correctly filters schedules by:
- Current semester (based on today)
- Current school year
- User ID

**Frontend:** `frontend/src/pages/Dashboard.jsx`

The frontend displays schedules by:
- Day of week matching the selected date
- Semester matching the selected date's semester

## Common Pitfalls

### ❌ Pitfall 1: Using Current Date Instead of Selected Date
```javascript
// WRONG
const today = new Date();
const todaySemester = getSemester(today);
return schedule.semester === todaySemester; // Bug!
```

### ✓ Solution
```javascript
// CORRECT
const selectedDateSemester = getSemester(selectedDate);
return schedule.semester === selectedDateSemester;
```

### ❌ Pitfall 2: Not Validating Dates
```javascript
// WRONG
const date = new Date(someValue);
const month = date.getMonth(); // Could be NaN!
```

### ✓ Solution
```javascript
// CORRECT
const date = new Date(someValue);
if (isNaN(date.getTime())) {
  console.error('Invalid date');
  return;
}
const month = date.getMonth();
```

### ❌ Pitfall 3: Missing Error Handling
```javascript
// WRONG
const events = data.map(event => ({
  date: event.date.format('Y-m-d') // Could throw!
}));
```

### ✓ Solution
```javascript
// CORRECT
const events = data.map(event => {
  try {
    return {
      date: event.date.format('Y-m-d')
    };
  } catch (error) {
    console.error('Error formatting event:', error);
    return null;
  }
}).filter(Boolean); // Remove nulls
```

## Testing Checklist

Before deploying changes to Dashboard:

- [ ] Test viewing dates in first semester (September-January)
- [ ] Test viewing dates in second semester (February-June)
- [ ] Test viewing dates in midyear (July-August)
- [ ] Test with dates in the past
- [ ] Test with dates in the future
- [ ] Test with invalid date formats
- [ ] Test with missing/null data
- [ ] Test schedule display on different days of the week
- [ ] Test with users who have no schedules
- [ ] Test with users who have schedules in multiple semesters

## Test Files

1. **test-dashboard-semester-bug.html** - Tests semester logic
   - Open in browser to verify semester filtering works correctly
   - All tests should pass

2. **Manual Testing Steps:**
   ```
   1. Login to the application
   2. Navigate to Dashboard
   3. Click on a Tuesday in September 2026
   4. Verify first semester Tuesday classes appear
   5. Click on a Tuesday in March 2026
   6. Verify second semester Tuesday classes appear
   7. Click on a Monday in July 2026
   8. Verify midyear Monday classes appear
   ```

## Semester Definitions

```javascript
// First Semester: September (9) to January (1)
if (month >= 9 || month <= 1) return 'first';

// Second Semester: February (2) to June (6)
if (month >= 2 && month <= 6) return 'second';

// Mid-Year/Summer: July (7) to August (8)
if (month >= 7 && month <= 8) return 'midyear';
```

## Key Functions

### `handleDateSelect(date, events)`
- **Purpose:** Display events for a selected date
- **Critical:** Must use selected date's semester, not current date
- **Filters:** Regular events, default events, schedule events

### `applyDashboardData(data, isBackground)`
- **Purpose:** Apply fetched data to state
- **Critical:** Must handle invalid dates gracefully
- **Error Handling:** Wrapped in try-catch

### `fetchData()`
- **Purpose:** Fetch dashboard data from API
- **Caching:** Uses cache for instant load
- **Background Refresh:** Updates cache silently

## Related Documentation

- `DASHBOARD_SEMESTER_BUG_FIX.md` - Details of the semester bug fix
- `DASHBOARD_BUG_FIXES.md` - Previous bug fixes
- `SEMESTER_SCHEDULE_VISUAL_GUIDE.md` - Visual guide for semester system

## Contact

If you encounter issues with the Dashboard:
1. Check console for error messages
2. Review this maintenance guide
3. Run the test file: `test-dashboard-semester-bug.html`
4. Check related documentation files

## Version History

- **March 21, 2026** - Fixed semester filtering bug in `handleDateSelect`
- **Previous** - Various error handling improvements (see DASHBOARD_BUG_FIXES.md)
