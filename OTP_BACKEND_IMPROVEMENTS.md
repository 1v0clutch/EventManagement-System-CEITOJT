# OTP Backend Logic Improvements

## Overview
This document outlines the improvements made to the OTP password reset flow in the Event Management System backend to ensure proper SendGrid integration with verified sender authentication.

---

## Key Improvements

### 1. **SendGrid Verified Sender Integration**

#### Before
- OTP emails were sent without explicit sender configuration
- No guarantee that verified SendGrid sender was being used
- Generic email message without proper formatting

#### After
- OTP notification explicitly uses verified SendGrid sender from `.env`
- Proper `from()` and `replyTo()` configuration in mail message
- Uses Laravel's mail configuration for consistency

**Code Change** (OtpPasswordResetNotification.php):
```php
public function toMail(object $notifiable): MailMessage
{
    return (new MailMessage)
        ->from(
            config('mail.from.address'),      // main.gabrielian.deleon@cvsu.edu.ph
            config('mail.from.name')           // Event Management System
        )
        ->replyTo(
            config('mail.reply_to.address'),   // support@cvsu.edu.ph
            config('mail.reply_to.name')       // CVSU Support
        )
        ->subject('🔐 Your Password Reset OTP Code')
        // ... rest of message
}
```

**Environment Variables** (.env):
```env
MAIL_MAILER=sendgrid
SENDGRID_API_KEY=SG.your-api-key
MAIL_FROM_ADDRESS=main.gabrielian.deleon@cvsu.edu.ph
MAIL_FROM_NAME="Event Management System"
MAIL_REPLY_TO_ADDRESS=support@cvsu.edu.ph
MAIL_REPLY_TO_NAME="CVSU Support"
```

---

### 2. **Enhanced Error Handling in requestOtp()**

#### Before
- No try-catch block for email sending failures
- Silent failures if SendGrid API fails
- No logging of OTP requests

#### After
- Comprehensive try-catch error handling
- Detailed logging of successful and failed OTP requests
- User-friendly error messages
- Proper HTTP status codes (500 for server errors)

**Code Change**:
```php
public function requestOtp(Request $request)
{
    // ... validation ...
    
    try {
        // Generate OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Delete existing unused OTPs
        DB::table('password_reset_otps')
            ->where('email', $email)
            ->where('used', false)
            ->delete();
        
        // Store OTP
        DB::table('password_reset_otps')->insert([
            'email' => $email,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(10),
            'used' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Send via SendGrid
        $user->notify(new OtpPasswordResetNotification($otp));
        
        \Log::info('OTP sent successfully', [
            'email' => $email,
            'timestamp' => now(),
        ]);
        
        return response()->json([
            'message' => 'OTP sent to your email. Please check your inbox.',
        ]);
    } catch (\Exception $e) {
        \Log::error('Failed to send OTP', [
            'email' => $email,
            'error' => $e->getMessage(),
        ]);
        
        return response()->json([
            'message' => 'Failed to send OTP. Please try again later.',
        ], 500);
    }
}
```

---

### 3. **Improved Email Normalization**

#### Before
- Email addresses not normalized (case sensitivity issues)
- Could create duplicate OTP records for same email

#### After
- All emails converted to lowercase for consistency
- Prevents duplicate OTP records
- Ensures reliable email lookups

**Code Change**:
```php
$email = strtolower($request->email);
$user = User::where('email', $email)->first();
```

---

### 4. **Enhanced OTP Verification (verifyOtp())**

#### Before
- Basic validation without detailed error messages
- No logging of verification attempts
- No reset token expiration tracking

#### After
- Detailed OTP validation with regex check
- Logging of invalid attempts for security monitoring
- Reset token with 30-minute expiration
- Better error messages for user guidance

**Code Change**:
```php
public function verifyOtp(Request $request)
{
    $request->validate([
        'email' => [
            'required',
            'email',
            'regex:/^main\.[a-zA-Z]+\.[a-zA-Z]+@cvsu\.edu\.ph$/i'
        ],
        'otp' => 'required|string|size:6|regex:/^\d{6}$/',  // NEW: Regex validation
    ], [
        'email.regex' => 'Email must be in format main.(firstname).(lastname)@cvsu.edu.ph',
        'otp.regex' => 'OTP must be a 6-digit number.',      // NEW: Better error message
    ]);

    $email = strtolower($request->email);

    $otpRecord = DB::table('password_reset_otps')
        ->where('email', $email)
        ->where('otp', $request->otp)
        ->where('used', false)
        ->where('expires_at', '>', now())
        ->first();

    if (!$otpRecord) {
        \Log::warning('Invalid OTP attempt', [           // NEW: Security logging
            'email' => $email,
            'timestamp' => now(),
        ]);

        return response()->json([
            'message' => 'Invalid or expired OTP. Please request a new one.',
        ], 400);
    }

    try {
        // Mark OTP as used
        DB::table('password_reset_otps')
            ->where('id', $otpRecord->id)
            ->update(['used' => true]);

        // Generate reset token with expiration
        $resetToken = bin2hex(random_bytes(32));
        DB::table('password_reset_otps')
            ->where('id', $otpRecord->id)
            ->update([
                'reset_token' => $resetToken,
                'reset_token_expires_at' => now()->addMinutes(30),  // NEW: Token expiration
            ]);

        \Log::info('OTP verified successfully', [
            'email' => $email,
            'timestamp' => now(),
        ]);

        return response()->json([
            'message' => 'OTP verified successfully.',
            'reset_token' => $resetToken,
        ]);
    } catch (\Exception $e) {
        \Log::error('Failed to verify OTP', [
            'email' => $email,
            'error' => $e->getMessage(),
        ]);

        return response()->json([
            'message' => 'An error occurred. Please try again.',
        ], 500);
    }
}
```

---

### 5. **Improved Password Reset (resetPasswordWithOtp())**

#### Before
- Minimal validation
- No token expiration checking
- No confirmation email sent
- Limited error handling

#### After
- Comprehensive validation with helpful messages
- Reset token expiration validation (30 minutes)
- Sends confirmation email after successful reset
- Detailed logging for audit trail
- Better error messages

**Code Change**:
```php
public function resetPasswordWithOtp(Request $request)
{
    $request->validate([
        'email' => [
            'required',
            'email',
            'regex:/^main\.[a-zA-Z]+\.[a-zA-Z]+@cvsu\.edu\.ph$/i'
        ],
        'reset_token' => 'required|string',
        'password' => 'required|string|min:8|confirmed',
    ], [
        'email.regex' => 'Email must be in format main.(firstname).(lastname)@cvsu.edu.ph',
        'password.confirmed' => 'Passwords do not match.',
        'password.min' => 'Password must be at least 8 characters.',
    ]);

    $email = strtolower($request->email);

    $otpRecord = DB::table('password_reset_otps')
        ->where('email', $email)
        ->where('reset_token', $request->reset_token)
        ->where('used', true)
        ->first();

    if (!$otpRecord) {
        \Log::warning('Invalid reset token attempt', [
            'email' => $email,
            'timestamp' => now(),
        ]);

        return response()->json([
            'message' => 'Invalid or expired reset token. Please request a new OTP.',
        ], 400);
    }

    // NEW: Check if reset token has expired
    if ($otpRecord->reset_token_expires_at && now()->isAfter($otpRecord->reset_token_expires_at)) {
        \Log::warning('Reset token expired', [
            'email' => $email,
            'timestamp' => now(),
        ]);

        return response()->json([
            'message' => 'Reset token has expired. Please request a new OTP.',
        ], 400);
    }

    $user = User::where('email', $email)->first();

    if (!$user) {
        \Log::error('User not found during password reset', [
            'email' => $email,
            'timestamp' => now(),
        ]);

        return response()->json([
            'message' => 'User not found.',
        ], 404);
    }

    try {
        // Update password
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // NEW: Send confirmation email
        $user->notify(new ResetPasswordNotification());

        // Clean up OTP record
        DB::table('password_reset_otps')
            ->where('email', $email)
            ->delete();

        \Log::info('Password reset successfully', [
            'email' => $email,
            'timestamp' => now(),
        ]);

        return response()->json([
            'message' => 'Password reset successfully. You can now log in with your new password.',
        ]);
    } catch (\Exception $e) {
        \Log::error('Failed to reset password', [
            'email' => $email,
            'error' => $e->getMessage(),
        ]);

        return response()->json([
            'message' => 'An error occurred while resetting your password. Please try again.',
        ], 500);
    }
}
```

---

### 6. **Database Schema Improvements**

#### Before
```php
Schema::create('password_reset_otps', function (Blueprint $table) {
    $table->id();
    $table->string('email')->index();
    $table->string('otp', 6);
    $table->timestamp('expires_at');
    $table->boolean('used')->default(false);
    $table->timestamps();
});
```

#### After
```php
Schema::create('password_reset_otps', function (Blueprint $table) {
    $table->id();
    $table->string('email')->index();
    $table->string('otp', 6);
    $table->timestamp('expires_at');
    $table->boolean('used')->default(false)->index();        // NEW: Index for faster queries
    $table->string('reset_token')->nullable()->unique();     // NEW: Reset token storage
    $table->timestamp('reset_token_expires_at')->nullable(); // NEW: Token expiration
    $table->timestamps();
});
```

**Improvements**:
- Added index on `used` column for faster queries
- Added `reset_token` column with unique constraint
- Added `reset_token_expires_at` for token expiration tracking

---

## OTP Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│ 1. User clicks "Forgot Password" on frontend                │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│ 2. Frontend sends POST /api/request-otp with email          │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│ 3. Backend validates email format                           │
│    - Checks if user exists                                  │
│    - Generates 6-digit OTP                                  │
│    - Stores OTP in DB (10-min expiration)                   │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│ 4. Backend sends OTP via SendGrid                           │
│    - Uses verified sender: main.gabrielian.deleon@cvsu...   │
│    - Reply-To: support@cvsu.edu.ph                          │
│    - Logs success/failure                                   │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│ 5. User receives OTP email in inbox                         │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│ 6. User enters OTP on frontend                              │
│    Frontend sends POST /api/verify-otp                      │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│ 7. Backend verifies OTP                                     │
│    - Checks OTP is valid and not expired                    │
│    - Marks OTP as used                                      │
│    - Generates reset token (30-min expiration)              │
│    - Logs verification attempt                              │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│ 8. Backend returns reset_token to frontend                  │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│ 9. User enters new password on frontend                     │
│    Frontend sends POST /api/reset-password-otp              │
│    with email, reset_token, and new password               │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│ 10. Backend validates reset token                           │
│     - Checks token is valid and not expired                 │
│     - Updates user password                                 │
│     - Sends confirmation email                              │
│     - Cleans up OTP record                                  │
│     - Logs password reset                                   │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│ 11. User receives confirmation email                        │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│ 12. User can now log in with new password                   │
└─────────────────────────────────────────────────────────────┘
```

---

## Security Improvements

### 1. **Email Normalization**
- Prevents duplicate OTP records for same email (case variations)
- Ensures consistent email lookups

### 2. **Token Expiration**
- OTP expires in 10 minutes
- Reset token expires in 30 minutes
- Prevents long-lived tokens from being exploited

### 3. **Logging & Monitoring**
- Logs all OTP requests (success and failure)
- Logs invalid OTP attempts (security monitoring)
- Logs password reset attempts (audit trail)
- Helps detect brute force attacks

### 4. **Validation**
- OTP must be exactly 6 digits
- Email must match institutional format
- Password must be at least 8 characters
- Password confirmation must match

### 5. **Error Messages**
- Generic messages for non-existent users (doesn't reveal if email exists)
- Specific messages for invalid/expired tokens
- Helps prevent user enumeration attacks

---

## Testing the Improved Flow

### 1. Request OTP
```bash
curl -X POST http://localhost:8000/api/request-otp \
  -H "Content-Type: application/json" \
  -d '{"email":"main.gabrielian.deleon@cvsu.edu.ph"}'
```

**Expected Response**:
```json
{
  "message": "OTP sent to your email. Please check your inbox."
}
```

### 2. Verify OTP
```bash
curl -X POST http://localhost:8000/api/verify-otp \
  -H "Content-Type: application/json" \
  -d '{
    "email":"main.gabrielian.deleon@cvsu.edu.ph",
    "otp":"123456"
  }'
```

**Expected Response**:
```json
{
  "message": "OTP verified successfully.",
  "reset_token": "abc123def456..."
}
```

### 3. Reset Password
```bash
curl -X POST http://localhost:8000/api/reset-password-otp \
  -H "Content-Type: application/json" \
  -d '{
    "email":"main.gabrielian.deleon@cvsu.edu.ph",
    "reset_token":"abc123def456...",
    "password":"newpassword123",
    "password_confirmation":"newpassword123"
  }'
```

**Expected Response**:
```json
{
  "message": "Password reset successfully. You can now log in with your new password."
}
```

---

## Monitoring & Debugging

### Check SendGrid Activity
1. Log in to SendGrid dashboard
2. Go to **Mail Activity**
3. Search for recipient email
4. Verify delivery status

### Check Laravel Logs
```bash
tail -f backend/storage/logs/laravel.log
```

Look for entries like:
```
[2026-02-18 10:30:45] local.INFO: OTP sent successfully {"email":"main.gabrielian.deleon@cvsu.edu.ph","timestamp":"2026-02-18T10:30:45.000000Z"}
[2026-02-18 10:31:20] local.INFO: OTP verified successfully {"email":"main.gabrielian.deleon@cvsu.edu.ph","timestamp":"2026-02-18T10:31:20.000000Z"}
[2026-02-18 10:32:00] local.INFO: Password reset successfully {"email":"main.gabrielian.deleon@cvsu.edu.ph","timestamp":"2026-02-18T10:32:00.000000Z"}
```

---

## Migration Instructions

If you have an existing database, run the migration to add new columns:

```bash
cd backend
php artisan migrate
```

This will add:
- Index on `used` column
- `reset_token` column
- `reset_token_expires_at` column

---

## Summary of Changes

| Component | Change | Benefit |
|-----------|--------|---------|
| **OtpPasswordResetNotification** | Explicit SendGrid sender config | Ensures verified sender is used |
| **requestOtp()** | Try-catch, logging, email normalization | Better error handling & monitoring |
| **verifyOtp()** | Token expiration, detailed logging | Enhanced security |
| **resetPasswordWithOtp()** | Token validation, confirmation email | Better UX & security |
| **Database Schema** | Added indexes and token columns | Better performance & tracking |

All improvements maintain backward compatibility while significantly enhancing security, reliability, and user experience.

