@echo off
echo ========================================
echo Migrate Created Events to Proper Table
echo ========================================
echo.
echo This will move created default events (with dates)
echo from default_events table to default_event_dates table.
echo.
echo Before:
echo   default_events: Contains both templates AND created events
echo   default_event_dates: Empty or incomplete
echo.
echo After:
echo   default_events: Only templates (no dates)
echo   default_event_dates: All created events with dates
echo.
pause

php migrate-created-events-now.php

echo.
echo ========================================
echo Testing the result...
echo ========================================
php test-created-default-events.php

pause
