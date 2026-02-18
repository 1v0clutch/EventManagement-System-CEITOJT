# OTP-Based Password Reset Implementation Guide

## Overview
This implementation provides a secure OTP (One-Time Password) based password reset flow that sends a 6-digit code to the user's Gmail inbox instead of a reset link.

## Flow Diagram

```
User → Forgot Password Page → Request OTP
                                    ↓
                            OTP sent to Gmail
                                    ↓
User receives OTP → Verify OTP Page → Verify Code
                                    ↓
                            Token generated
                                    ↓
User → Reset Password Page → Create New Password
                                    ↓
                            Password updated
                                    ↓
                            Redirect to Login
```

## Backend Implementation

### Database Migration
- **File**: `backend/database/migrations/2026_02_16_000000_create_password_reset_otps_table.php`
- **Table**: `password_reset_otps`
- **Columns**:
  - `id`: Primary key
  - `email`: User email (indexed)
  - `otp`: 6-digit OTP code
  - `expires_at`: Expiration timestamp (10 minutes)
  - `used`: Boolean flag to mark OTP as used
  - `reset_token`: Temporary token for password reset
  - `timestamps`: Created/updated timestamps

### New Notification
- **File**: `backend/app/Notifications/OtpPasswordResetNotification.php`
- Sends OTP code via email with 10-minute expiration notice

### API Endpoints

#### 1. Request OTP
- **Endpoint**: `POST /api/request-otp`
- **Request**:
  ```json
  {
    "email": "main.firstname.lastname@cvsu.edu.ph"
  }
  ```
- **Response**:
  ```json
  {
    "message": "OTP sent to your email. Please check your Gmail inbox."
  }
  ```
- **Process**:
  - Validates email format
  - Generates 6-digit OTP
  - Deletes any existing OTP for the email
  - Stores OTP with 10-minute expiration
  - Sends OTP via email notification

#### 2. Verify OTP
- **Endpoint**: `POST /api/verify-otp`
- **Request**:
  ```json
  {
    "email": "main.firstname.lastname@cvsu.edu.ph",
    "otp": "123456"
  }
  ```
- **Response**:
  ```json
  {
    "message": "OTP verified successfully.",
    "reset_token": "temporary-token-hash"
  }
  ```
- **Process**:
  - Validates OTP exists, matches, and hasn't expired
  - Checks OTP hasn't been used
  - Marks OTP as used
  - Generates temporary reset token
  - Returns token for next step

#### 3. Reset Password with OTP
- **Endpoint**: `POST /api/reset-password-otp`
- **Request**:
  ```json
  {
    "email": "main.firstname.lastname@cvsu.edu.ph",
    "reset_token": "temporary-token-hash",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
  }
  ```
- **Response**:
  ```json
  {
    "message": "Password reset successfully."
  }
  ```
- **Process**:
  - Validates reset token
  - Updates user password
  - Cleans up OTP record
  - User can now login with new password

### Configuration

Update `backend/.env`:
```env
# Gmail SMTP Configuration (for sending OTP codes)
MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=main.firstname.lastname@cvsu.edu.ph
MAIL_PASSWORD=your-cvsu-google-password
MAIL_FROM_ADDRESS="${MAIL_USERNAME}"
MAIL_FROM_NAME="Event Management System"

# Frontend URL for redirects
FRONTEND_URL=http://localhost:5173
```

**Gmail Setup (Without 2FA)**:
1. Go to [myaccount.google.com/security](https://myaccount.google.com/security)
2. Scroll down to "Less secure app access"
3. Turn it ON
4. Use your CVSU email password in `MAIL_PASSWORD`

**Example:**
```env
MAIL_USERNAME=main.john.doe@cvsu.edu.ph
MAIL_PASSWORD=YourCVSUPassword123
```

The OTP system will send codes through Gmail using your CVSU credentials.

## Frontend Implementation

### New Pages

#### 1. ForgotPassword.jsx (Updated)
- **Route**: `/forgot-password`
- **Purpose**: Request OTP
- **Features**:
  - Email validation (CVSU format)
  - Sends OTP request
  - Redirects to OTP verification page
  - Link to request new OTP

#### 2. VerifyOtp.jsx (New)
- **Route**: `/verify-otp`
- **Purpose**: Verify OTP code
- **Features**:
  - 6-digit OTP input field
  - Auto-formats numeric input only
  - Real-time validation
  - Displays user email
  - Option to request new OTP
  - Redirects to password reset page on success

#### 3. ResetPasswordOtp.jsx (New)
- **Route**: `/reset-password-otp`
- **Purpose**: Create new password
- **Features**:
  - Password and confirm password fields
  - Password requirements display
  - Validation (8+ characters, matching)
  - Redirects to login on success
  - Link to start over if needed

### Route Configuration
Updated `frontend/src/App.jsx` with new routes:
```javascript
<Route path="/forgot-password" element={<ForgotPassword />} />
<Route path="/verify-otp" element={<VerifyOtp />} />
<Route path="/reset-password-otp" element={<ResetPasswordOtp />} />
```

## Security Features

1. **OTP Expiration**: 10-minute validity window
2. **One-Time Use**: OTP marked as used after verification
3. **Email Validation**: CVSU email format required
4. **Password Requirements**: Minimum 8 characters
5. **Temporary Tokens**: Reset tokens valid only after OTP verification
6. **Database Cleanup**: OTP records deleted after successful reset

## User Experience Flow

### Step 1: Request Password Reset
1. User clicks "Forgot Password" on login page
2. Enters email address
3. Receives OTP in Gmail inbox

### Step 2: Verify OTP
1. User navigates to OTP verification page
2. Enters 6-digit code from email
3. System verifies code validity and expiration
4. Receives temporary reset token

### Step 3: Create New Password
1. User enters new password (8+ characters)
2. Confirms password
3. System updates password in database
4. User redirected to login page
5. User logs in with new password

## Testing the Implementation

### 1. Run Database Migration
```bash
cd backend
php artisan migrate
```

### 2. Configure Gmail SMTP
- Enable 2-factor authentication on your Google account
- Generate an App Password from [Google Account Security](https://myaccount.google.com/security)
- Use your CVSU email as MAIL_USERNAME
- Use the generated App Password as MAIL_PASSWORD
- Update `.env` with credentials

### 3. Test OTP Flow
```bash
# Request OTP
curl -X POST http://localhost:8000/api/request-otp \
  -H "Content-Type: application/json" \
  -d '{"email":"main.firstname.lastname@cvsu.edu.ph"}'

# Verify OTP (use code from email)
curl -X POST http://localhost:8000/api/verify-otp \
  -H "Content-Type: application/json" \
  -d '{"email":"main.firstname.lastname@cvsu.edu.ph","otp":"123456"}'

# Reset Password
curl -X POST http://localhost:8000/api/reset-password-otp \
  -H "Content-Type: application/json" \
  -d '{
    "email":"main.firstname.lastname@cvsu.edu.ph",
    "reset_token":"token-from-verify",
    "password":"newpassword123",
    "password_confirmation":"newpassword123"
  }'
```

## Troubleshooting

### OTP Not Received
- Check your CVSU email inbox and spam folder
- Verify MAIL_USERNAME and MAIL_PASSWORD in `.env` are correct
- Ensure you're using a Gmail App Password (not your regular Gmail password)
- Check Laravel logs: `storage/logs/laravel.log`

### OTP Expired
- OTP valid for 10 minutes
- User must request new OTP if expired

### Reset Token Invalid
- Token only valid after OTP verification
- Token expires if not used within session
- User must start over if token expires

### Email Format Error
- Must use format: `main.firstname.lastname@cvsu.edu.ph`
- Case-insensitive but format must match exactly

## Files Modified/Created

### Backend
- ✅ `backend/database/migrations/2026_02_16_000000_create_password_reset_otps_table.php` (NEW)
- ✅ `backend/app/Notifications/OtpPasswordResetNotification.php` (NEW)
- ✅ `backend/app/Http/Controllers/AuthController.php` (UPDATED)
- ✅ `backend/routes/api.php` (UPDATED)
- ✅ `backend/.env.example` (UPDATED)

### Frontend
- ✅ `frontend/src/pages/ForgotPassword.jsx` (UPDATED)
- ✅ `frontend/src/pages/VerifyOtp.jsx` (NEW)
- ✅ `frontend/src/pages/ResetPasswordOtp.jsx` (NEW)
- ✅ `frontend/src/App.jsx` (UPDATED)

## Next Steps

1. Run database migration
2. Configure Gmail SMTP in `.env`
3. Test the complete flow in development
4. Deploy to production with proper email configuration
