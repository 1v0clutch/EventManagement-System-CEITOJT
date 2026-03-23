@echo off
echo ========================================
echo Add Semester to Default Event Dates
echo ========================================
echo.
echo This will add the semester field to the default_event_dates table
echo and automatically populate it based on existing dates.
echo.
pause

php artisan migrate --path=database/migrations/2026_03_21_100000_add_semester_to_default_event_dates_table.php

echo.
echo ========================================
echo Migration Complete!
echo ========================================
echo.
echo Testing the system...
php test-created-default-events.php

pause
