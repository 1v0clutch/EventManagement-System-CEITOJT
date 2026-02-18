@echo off
REM Supabase Email Setup Script for Windows
REM This script helps you set up Supabase email integration

echo ==========================================
echo Supabase Email Migration Setup
echo ==========================================
echo.

REM Check if .env file exists
if not exist "backend\.env" (
    echo Error: backend\.env file not found
    echo Please copy backend\.env.example to backend\.env first
    pause
    exit /b 1
)

echo This script will help you configure Supabase for email sending.
echo.

REM Prompt for Supabase URL
set /p SUPABASE_URL="Enter your Supabase Project URL (e.g., https://xxxxx.supabase.co): "

REM Prompt for Supabase Anon Key
set /p SUPABASE_ANON_KEY="Enter your Supabase Anon Key: "

REM Prompt for Supabase Service Role Key
set /p SUPABASE_SERVICE_ROLE_KEY="Enter your Supabase Service Role Key: "

REM Prompt for email from address
set /p MAIL_FROM_ADDRESS="Enter your FROM email address (must be verified in Resend): "

REM Prompt for email from name
set /p MAIL_FROM_NAME="Enter your FROM name (e.g., Event Management System): "

echo.
echo Updating backend\.env file...

REM Create a temporary PowerShell script to update .env
echo $envFile = "backend\.env" > update_env.ps1
echo $content = Get-Content $envFile >> update_env.ps1
echo. >> update_env.ps1
echo # Update or add Supabase configuration >> update_env.ps1
echo if ($content -match "SUPABASE_URL=") { >> update_env.ps1
echo     $content = $content -replace "SUPABASE_URL=.*", "SUPABASE_URL=%SUPABASE_URL%" >> update_env.ps1
echo } else { >> update_env.ps1
echo     $content += "`nSUPABASE_URL=%SUPABASE_URL%" >> update_env.ps1
echo } >> update_env.ps1
echo. >> update_env.ps1
echo if ($content -match "SUPABASE_ANON_KEY=") { >> update_env.ps1
echo     $content = $content -replace "SUPABASE_ANON_KEY=.*", "SUPABASE_ANON_KEY=%SUPABASE_ANON_KEY%" >> update_env.ps1
echo } else { >> update_env.ps1
echo     $content += "`nSUPABASE_ANON_KEY=%SUPABASE_ANON_KEY%" >> update_env.ps1
echo } >> update_env.ps1
echo. >> update_env.ps1
echo if ($content -match "SUPABASE_SERVICE_ROLE_KEY=") { >> update_env.ps1
echo     $content = $content -replace "SUPABASE_SERVICE_ROLE_KEY=.*", "SUPABASE_SERVICE_ROLE_KEY=%SUPABASE_SERVICE_ROLE_KEY%" >> update_env.ps1
echo } else { >> update_env.ps1
echo     $content += "`nSUPABASE_SERVICE_ROLE_KEY=%SUPABASE_SERVICE_ROLE_KEY%" >> update_env.ps1
echo } >> update_env.ps1
echo. >> update_env.ps1
echo if ($content -match "MAIL_FROM_ADDRESS=") { >> update_env.ps1
echo     $content = $content -replace "MAIL_FROM_ADDRESS=.*", "MAIL_FROM_ADDRESS=%MAIL_FROM_ADDRESS%" >> update_env.ps1
echo } else { >> update_env.ps1
echo     $content += "`nMAIL_FROM_ADDRESS=%MAIL_FROM_ADDRESS%" >> update_env.ps1
echo } >> update_env.ps1
echo. >> update_env.ps1
echo if ($content -match "MAIL_FROM_NAME=") { >> update_env.ps1
echo     $content = $content -replace "MAIL_FROM_NAME=.*", "MAIL_FROM_NAME=`"%MAIL_FROM_NAME%`"" >> update_env.ps1
echo } else { >> update_env.ps1
echo     $content += "`nMAIL_FROM_NAME=`"%MAIL_FROM_NAME%`"" >> update_env.ps1
echo } >> update_env.ps1
echo. >> update_env.ps1
echo # Comment out SendGrid >> update_env.ps1
echo $content = $content -replace "^MAIL_MAILER=sendgrid", "# MAIL_MAILER=sendgrid" >> update_env.ps1
echo $content = $content -replace "^SENDGRID_API_KEY=", "# SENDGRID_API_KEY=" >> update_env.ps1
echo. >> update_env.ps1
echo # Set mail mailer to log >> update_env.ps1
echo if ($content -match "^MAIL_MAILER=") { >> update_env.ps1
echo     $content = $content -replace "^MAIL_MAILER=.*", "MAIL_MAILER=log" >> update_env.ps1
echo } else { >> update_env.ps1
echo     $content += "`nMAIL_MAILER=log" >> update_env.ps1
echo } >> update_env.ps1
echo. >> update_env.ps1
echo $content ^| Set-Content $envFile >> update_env.ps1

REM Execute the PowerShell script
powershell -ExecutionPolicy Bypass -File update_env.ps1

REM Clean up
del update_env.ps1

echo.
echo Configuration updated successfully!
echo.
echo Next steps:
echo 1. Deploy the Supabase Edge Function (see SUPABASE_EMAIL_MIGRATION_GUIDE.md)
echo 2. Clear Laravel cache: cd backend ^&^& php artisan config:clear
echo 3. Test the integration by requesting an OTP
echo.
echo For detailed instructions, see: SUPABASE_EMAIL_MIGRATION_GUIDE.md
echo.
pause
