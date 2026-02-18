# Email Flow Diagrams

## Current Flow (After Migration)

### Password Reset OTP Request Flow

```
┌─────────────────────────────────────────────────────────────────────┐
│                         User Requests OTP                            │
└─────────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
┌─────────────────────────────────────────────────────────────────────┐
│  Frontend: POST /api/request-otp                                     │
│  Body: { "email": "main.john.doe@cvsu.edu.ph" }                     │
└─────────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
┌─────────────────────────────────────────────────────────────────────┐
│  AuthController::requestOtp()                                        │
│  1. Validate email format                                            │
│  2. Check if user exists                                             │
│  3. Generate 6-digit OTP                                             │
│  4. Store OTP in database (expires in 10 min)                        │
└─────────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
┌─────────────────────────────────────────────────────────────────────┐
│  SupabaseEmailService::sendPasswordResetOtp()                        │
│  1. Build HTML email template                                        │
│  2. Build plain text email                                           │
│  3. Call sendCustomEmail()                                           │
└─────────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
┌─────────────────────────────────────────────────────────────────────┐
│  HTTP POST to Supabase Edge Function                                 │
│  URL: https://xxxxx.supabase.co/functions/v1/send-email             │
│  Headers: Authorization: Bearer [SUPABASE_KEY]                       │
│  Body: { to, from, subject, html, text }                            │
└─────────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
┌─────────────────────────────────────────────────────────────────────┐
│  Supabase Edge Function (send-email)                                 │
│  1. Receive request                                                   │
│  2. Validate payload                                                  │
│  3. Call Resend API                                                   │
└─────────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
┌─────────────────────────────────────────────────────────────────────┐
│  Resend API                                                           │
│  POST https://api.resend.com/emails                                  │
│  Headers: Authorization: Bearer [RESEND_API_KEY]                     │
│  Body: { from, to, subject, html, text }                            │
└─────────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
┌─────────────────────────────────────────────────────────────────────┐
│  Email Delivery                                                       │
│  User receives email with OTP code                                   │
└─────────────────────────────────────────────────────────────────────┘
```

### Password Reset Confirmation Flow

```
┌─────────────────────────────────────────────────────────────────────┐
│  User Resets Password Successfully                                   │
└─────────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
┌─────────────────────────────────────────────────────────────────────┐
│  AuthController::resetPasswordWithOtp()                              │
│  1. Verify reset token                                               │
│  2. Update user password                                             │
│  3. Call SupabaseEmailService                                        │
└─────────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
┌─────────────────────────────────────────────────────────────────────┐
│  SupabaseEmailService::sendPasswordResetConfirmation()               │
│  1. Build confirmation email HTML                                    │
│  2. Build plain text version                                         │
│  3. Send via Supabase Edge Function                                  │
└─────────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
┌─────────────────────────────────────────────────────────────────────┐
│  User receives confirmation email                                    │
└─────────────────────────────────────────────────────────────────────┘
```

## Old Flow (SendGrid - Deprecated)

```
┌─────────────────────────────────────────────────────────────────────┐
│  User Requests OTP                                                   │
└─────────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
┌─────────────────────────────────────────────────────────────────────┐
│  AuthController::requestOtp()                                        │
│  1. Generate OTP                                                     │
│  2. Store in database                                                │
│  3. Call $user->notify(new OtpPasswordResetNotification($otp))      │
└─────────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
┌─────────────────────────────────────────────────────────────────────┐
│  Laravel Notification System                                         │
│  1. Build MailMessage                                                │
│  2. Queue or send immediately                                        │
└─────────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
┌─────────────────────────────────────────────────────────────────────┐
│  SendGridTransport                                                   │
│  1. Convert Symfony Email to SendGrid format                         │
│  2. Call SendGrid API                                                │
└─────────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
┌─────────────────────────────────────────────────────────────────────┐
│  SendGrid API                                                        │
│  ❌ Expires April 18, 2026                                          │
└─────────────────────────────────────────────────────────────────────┘
```

## Component Interaction Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│                         Laravel Application                          │
│                                                                       │
│  ┌──────────────────┐         ┌─────────────────────────┐          │
│  │ AuthController   │────────▶│ SupabaseEmailService    │          │
│  │                  │         │                         │          │
│  │ - requestOtp()   │         │ - sendPasswordResetOtp()│          │
│  │ - verifyOtp()    │         │ - sendConfirmation()    │          │
│  │ - resetPassword()│         │ - sendCustomEmail()     │          │
│  └──────────────────┘         └─────────────────────────┘          │
│                                           │                          │
└───────────────────────────────────────────┼──────────────────────────┘
                                            │
                                            │ HTTP Request
                                            │
                                            ▼
┌─────────────────────────────────────────────────────────────────────┐
│                      Supabase Infrastructure                         │
│                                                                       │
│  ┌──────────────────────────────────────────────────────┐           │
│  │  Edge Function: send-email                           │           │
│  │                                                       │           │
│  │  - Receives email request                            │           │
│  │  - Validates payload                                 │           │
│  │  - Calls Resend API                                  │           │
│  │  - Returns response                                  │           │
│  └──────────────────────────────────────────────────────┘           │
│                              │                                       │
└──────────────────────────────┼───────────────────────────────────────┘
                               │
                               │ HTTP Request
                               │
                               ▼
┌─────────────────────────────────────────────────────────────────────┐
│                         Resend API                                   │
│                                                                       │
│  - Processes email                                                   │
│  - Handles delivery                                                  │
│  - Provides delivery status                                          │
│  - Manages bounces/complaints                                        │
└─────────────────────────────────────────────────────────────────────┘
                               │
                               │
                               ▼
┌─────────────────────────────────────────────────────────────────────┐
│                      Email Recipient                                 │
└─────────────────────────────────────────────────────────────────────┘
```

## Data Flow

### OTP Storage and Verification

```
┌─────────────────────────────────────────────────────────────────────┐
│  Step 1: Request OTP                                                 │
└─────────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
┌─────────────────────────────────────────────────────────────────────┐
│  Database: password_reset_otps                                       │
│  ┌────────────────────────────────────────────────────────────┐    │
│  │ email: main.john.doe@cvsu.edu.ph                           │    │
│  │ otp: 123456                                                 │    │
│  │ expires_at: 2026-02-18 10:10:00                            │    │
│  │ used: false                                                 │    │
│  │ reset_token: null                                           │    │
│  │ reset_token_expires_at: null                               │    │
│  └────────────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
┌─────────────────────────────────────────────────────────────────────┐
│  Step 2: Verify OTP                                                  │
└─────────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
┌─────────────────────────────────────────────────────────────────────┐
│  Database: password_reset_otps (Updated)                             │
│  ┌────────────────────────────────────────────────────────────┐    │
│  │ email: main.john.doe@cvsu.edu.ph                           │    │
│  │ otp: 123456                                                 │    │
│  │ expires_at: 2026-02-18 10:10:00                            │    │
│  │ used: true ✓                                                │    │
│  │ reset_token: abc123def456... ✓                             │    │
│  │ reset_token_expires_at: 2026-02-18 10:30:00 ✓             │    │
│  └────────────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
┌─────────────────────────────────────────────────────────────────────┐
│  Step 3: Reset Password                                              │
└─────────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
┌─────────────────────────────────────────────────────────────────────┐
│  Database: password_reset_otps (Deleted)                             │
│  Record removed after successful password reset                      │
└─────────────────────────────────────────────────────────────────────┘
```

## Error Handling Flow

```
┌─────────────────────────────────────────────────────────────────────┐
│  Email Send Request                                                  │
└─────────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
                    ┌─────────────────────────┐
                    │  Try Send Email         │
                    └─────────────────────────┘
                                  │
                    ┌─────────────┴─────────────┐
                    │                           │
                    ▼                           ▼
        ┌───────────────────┐       ┌───────────────────┐
        │  Success          │       │  Failure          │
        └───────────────────┘       └───────────────────┘
                    │                           │
                    │                           ▼
                    │               ┌───────────────────┐
                    │               │  Log Error        │
                    │               │  - Laravel Log    │
                    │               │  - Edge Func Log  │
                    │               └───────────────────┘
                    │                           │
                    │                           ▼
                    │               ┌───────────────────┐
                    │               │  Return Error     │
                    │               │  Response to User │
                    │               └───────────────────┘
                    │                           │
                    ▼                           ▼
        ┌───────────────────────────────────────────────┐
        │  User Receives Feedback                       │
        │  - Success: "OTP sent to your email"          │
        │  - Failure: "Failed to send OTP"              │
        └───────────────────────────────────────────────┘
```

## Monitoring Points

```
┌─────────────────────────────────────────────────────────────────────┐
│  Monitoring Layer 1: Laravel Application                             │
│  - Log: backend/storage/logs/laravel.log                            │
│  - Metrics: OTP requests, success/failure rate                       │
└─────────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
┌─────────────────────────────────────────────────────────────────────┐
│  Monitoring Layer 2: Supabase Edge Functions                         │
│  - Dashboard: Supabase → Edge Functions → send-email → Logs         │
│  - Metrics: Invocations, errors, latency                            │
└─────────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
┌─────────────────────────────────────────────────────────────────────┐
│  Monitoring Layer 3: Resend                                          │
│  - Dashboard: Resend → Emails                                        │
│  - Metrics: Sent, delivered, bounced, complained                     │
└─────────────────────────────────────────────────────────────────────┘
```

## Security Flow

```
┌─────────────────────────────────────────────────────────────────────┐
│  Security Layer 1: Email Validation                                  │
│  - Regex: main.[firstname].[lastname]@cvsu.edu.ph                   │
│  - Prevents spam and unauthorized access                             │
└─────────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
┌─────────────────────────────────────────────────────────────────────┐
│  Security Layer 2: OTP Expiration                                    │
│  - OTP expires in 10 minutes                                         │
│  - Prevents replay attacks                                           │
└─────────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
┌─────────────────────────────────────────────────────────────────────┐
│  Security Layer 3: One-Time Use                                      │
│  - OTP marked as 'used' after verification                           │
│  - Cannot be reused                                                  │
└─────────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
┌─────────────────────────────────────────────────────────────────────┐
│  Security Layer 4: Reset Token                                       │
│  - Temporary token for password reset (30 min)                       │
│  - Separate from OTP for additional security                         │
└─────────────────────────────────────────────────────────────────────┘
                                  │
                                  ▼
┌─────────────────────────────────────────────────────────────────────┐
│  Security Layer 5: API Authentication                                │
│  - Supabase: Bearer token authentication                             │
│  - Resend: API key authentication                                    │
│  - Environment variables (never committed)                           │
└─────────────────────────────────────────────────────────────────────┘
```

---

**Note**: These diagrams provide a visual representation of the email flow after migration to Supabase. For implementation details, refer to the code and documentation files.
