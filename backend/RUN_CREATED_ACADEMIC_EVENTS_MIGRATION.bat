@echo off
echo ========================================
echo Running Created Academic Events Migration
echo ========================================
echo.
echo This migration will:
echo 1. Create the 'created_academic_events' table
echo 2. Set up proper isolation for user-created academic events
echo 3. Ensure events are tied to specific school years and semesters
echo.
echo Press any key to continue or Ctrl+C to cancel...
pause > nul

php artisan migrate --path=database/migrations/2026_03_23_120000_create_created_academic_events_table.php

echo.
echo ========================================
echo Migration Complete!
echo ========================================
echo.
echo Next steps:
echo 1. Test creating a new academic event from the frontend
echo 2. Verify it only appears in the current school year
echo 3. Switch to a different school year and confirm it doesn't appear
echo.
pause
