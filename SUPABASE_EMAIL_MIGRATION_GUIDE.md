# Supabase Email Migration Guide

This guide will help you migrate from SendGrid to Supabase for sending password reset OTP emails.

## Why Migrate to Supabase?

- SendGrid free tier expires after 2 months (April 18, 2026)
- Supabase provides a sustainable long-term solution
- Uses Resend API (100 emails/day, 3000/month free tier)
- No expiration on free tier

## Prerequisites

1. A Supabase account (sign up at https://supabase.com)
2. A Resend account (sign up at https://resend.com)
3. Supabase CLI installed (optional, for deploying Edge Functions)

## Step 1: Set Up Resend

1. Go to https://resend.com and create an account
2. Verify your domain (or use their testing domain for development)
3. Get your API key from the Resend dashboard
4. Keep this API key handy for Step 3

## Step 2: Get Supabase Credentials

1. Log in to your Supabase dashboard
2. Select your project (or create a new one)
3. Go to **Settings** → **API**
4. Copy the following:
   - **Project URL** (e.g., `https://xxxxx.supabase.co`)
   - **anon/public key**
   - **service_role key** (keep this secret!)

## Step 3: Deploy Supabase Edge Function

### Option A: Using Supabase CLI (Recommended)

1. Install Supabase CLI:
   ```bash
   npm install -g supabase
   ```

2. Login to Supabase:
   ```bash
   supabase login
   ```

3. Link your project:
   ```bash
   supabase link --project-ref your-project-ref
   ```

4. Create the Edge Function:
   ```bash
   supabase functions new send-email
   ```

5. Copy the content from `supabase-edge-function-send-email.ts` to:
   ```
   supabase/functions/send-email/index.ts
   ```

6. Set the Resend API key as a secret:
   ```bash
   supabase secrets set RESEND_API_KEY=your-resend-api-key
   ```

7. Deploy the function:
   ```bash
   supabase functions deploy send-email
   ```

### Option B: Using Supabase Dashboard

1. Go to your Supabase dashboard
2. Navigate to **Edge Functions**
3. Click **Create a new function**
4. Name it `send-email`
5. Copy the content from `supabase-edge-function-send-email.ts`
6. Go to **Settings** → **Edge Functions** → **Secrets**
7. Add secret: `RESEND_API_KEY` with your Resend API key
8. Deploy the function

## Step 4: Update Laravel Environment Variables

Update your `backend/.env` file:

```env
# Remove or comment out SendGrid configuration
# MAIL_MAILER=sendgrid
# SENDGRID_API_KEY=your-sendgrid-api-key

# Set mail mailer to log for local development
MAIL_MAILER=log

# Add Supabase configuration
SUPABASE_URL=https://your-project.supabase.co
SUPABASE_ANON_KEY=your-supabase-anon-key
SUPABASE_SERVICE_ROLE_KEY=your-supabase-service-role-key

# Update email from address (must be verified in Resend)
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Event Management System"
```

## Step 5: Clear Laravel Cache

```bash
cd backend
php artisan config:clear
php artisan cache:clear
```

## Step 6: Test the Integration

### Test OTP Request

```bash
curl -X POST http://localhost:8000/api/request-otp \
  -H "Content-Type: application/json" \
  -d '{
    "email": "main.john.doe@cvsu.edu.ph"
  }'
```

### Check Logs

```bash
tail -f backend/storage/logs/laravel.log
```

You should see:
- "OTP sent successfully via Supabase"
- No SendGrid-related errors

## Step 7: Verify Email Delivery

1. Check the recipient's email inbox
2. The email should have:
   - Subject: "🔐 Your Password Reset OTP Code"
   - A 6-digit OTP code
   - Professional HTML formatting
   - 10-minute expiration notice

## Troubleshooting

### Issue: "Failed to send OTP email via Supabase"

**Solution:**
- Check that your Supabase Edge Function is deployed
- Verify the function URL in Supabase dashboard
- Check Edge Function logs in Supabase dashboard

### Issue: "Resend API error"

**Solution:**
- Verify your Resend API key is correct
- Check that your sender email is verified in Resend
- Ensure you haven't exceeded Resend's rate limits

### Issue: "CORS error"

**Solution:**
- Ensure the Edge Function includes CORS headers
- Check that your frontend URL is allowed

### Issue: Emails not arriving

**Solution:**
- Check spam/junk folder
- Verify sender email is verified in Resend
- Check Resend dashboard for delivery logs
- Verify Edge Function logs in Supabase

## Cost Comparison

### SendGrid
- Free tier: 100 emails/day for 2 months only
- After April 18, 2026: Requires paid plan

### Supabase + Resend
- Resend free tier: 100 emails/day, 3000/month
- No expiration on free tier
- Supabase Edge Functions: Free tier includes 500K invocations/month

## What Was Changed

### Files Modified:
1. `backend/app/Http/Controllers/AuthController.php`
   - Added SupabaseEmailService dependency injection
   - Replaced SendGrid notification with Supabase email service
   - Updated confirmation email to use Supabase

2. `backend/config/services.php`
   - Added Supabase configuration

3. `backend/.env.example`
   - Added Supabase environment variables
   - Deprecated SendGrid variables

### Files Created:
1. `backend/app/Services/SupabaseEmailService.php`
   - New service for sending emails via Supabase
   - Includes HTML email templates
   - Handles OTP and confirmation emails

2. `supabase-edge-function-send-email.ts`
   - Edge Function for sending emails via Resend

### Files No Longer Used (Can be deleted):
1. `backend/app/Mail/SendGridTransport.php`
2. `backend/app/Notifications/OtpPasswordResetNotification.php`
3. `backend/app/Notifications/ResetPasswordNotification.php`

## Rollback Plan

If you need to rollback to SendGrid:

1. Restore `.env` variables:
   ```env
   MAIL_MAILER=sendgrid
   SENDGRID_API_KEY=your-sendgrid-api-key
   ```

2. Revert `AuthController.php` changes
3. Clear cache: `php artisan config:clear`

## Next Steps

1. Monitor email delivery for the first few days
2. Set up email delivery monitoring in Resend dashboard
3. Consider upgrading Resend plan if you exceed 100 emails/day
4. Remove SendGrid-related files once migration is confirmed successful

## Support

- Supabase Docs: https://supabase.com/docs
- Resend Docs: https://resend.com/docs
- Edge Functions: https://supabase.com/docs/guides/functions
