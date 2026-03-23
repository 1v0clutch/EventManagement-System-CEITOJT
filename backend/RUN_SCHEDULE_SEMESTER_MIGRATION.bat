@echo off
echo Running User Schedules Semester Migration...
echo ============================================
echo.

cd /d "%~dp0"

php artisan migrate --path=database/migrations/2026_03_21_100000_add_semester_and_school_year_to_user_schedules.php

echo.
echo ============================================
echo Migration completed!
echo.
pause
