# SendGrid to Supabase Email Migration

## Overview

This migration replaces SendGrid with Supabase + Resend for sending password reset OTP emails. This change ensures long-term sustainability as SendGrid's free tier expires on April 18, 2026.

## What Changed?

### Architecture Change

**Before (SendGrid):**
```
Laravel App → SendGrid API → Email Delivery
```

**After (Supabase + Resend):**
```
Laravel App → Supabase Edge Function → Resend API → Email Delivery
```

### Benefits

1. **No Expiration**: Resend's free tier doesn't expire
2. **Better Limits**: 100 emails/day, 3000/month (vs SendGrid's 100/day for 2 months)
3. **Modern Stack**: Leverages Supabase's serverless infrastructure
4. **Cost Effective**: Free tier is sufficient for most use cases
5. **Easy Scaling**: Simple upgrade path if you need more emails

## Quick Start

### 1. Prerequisites

- [ ] Supabase account (https://supabase.com)
- [ ] Resend account (https://resend.com)
- [ ] Domain verified in Resend (or use their test domain)

### 2. Get Your Credentials

#### Supabase:
1. Go to your Supabase project dashboard
2. Navigate to **Settings** → **API**
3. Copy:
   - Project URL
   - anon/public key
   - service_role key

#### Resend:
1. Go to Resend dashboard
2. Navigate to **API Keys**
3. Create a new API key
4. Copy the API key

### 3. Deploy Edge Function

Choose one method:

#### Method A: Supabase CLI (Recommended)

```bash
# Install CLI
npm install -g supabase

# Login
supabase login

# Link project
supabase link --project-ref your-project-ref

# Create function directory
mkdir -p supabase/functions/send-email

# Copy the function code
cp supabase-edge-function-send-email.ts supabase/functions/send-email/index.ts

# Set Resend API key
supabase secrets set RESEND_API_KEY=your-resend-api-key

# Deploy
supabase functions deploy send-email
```

#### Method B: Supabase Dashboard

1. Go to **Edge Functions** in your Supabase dashboard
2. Click **Create a new function**
3. Name: `send-email`
4. Copy content from `supabase-edge-function-send-email.ts`
5. Add secret `RESEND_API_KEY` in **Settings** → **Edge Functions** → **Secrets**
6. Deploy

### 4. Configure Laravel

#### Option A: Automated Setup (Recommended)

**Windows:**
```cmd
setup-supabase-email.bat
```

**Linux/Mac:**
```bash
chmod +x setup-supabase-email.sh
./setup-supabase-email.sh
```

#### Option B: Manual Setup

Edit `backend/.env`:

```env
# Comment out SendGrid
# MAIL_MAILER=sendgrid
# SENDGRID_API_KEY=your-sendgrid-api-key

# Set to log for local development
MAIL_MAILER=log

# Add Supabase
SUPABASE_URL=https://your-project.supabase.co
SUPABASE_ANON_KEY=your-supabase-anon-key
SUPABASE_SERVICE_ROLE_KEY=your-supabase-service-role-key

# Update email settings
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Event Management System"
```

### 5. Clear Cache

```bash
cd backend
php artisan config:clear
php artisan cache:clear
```

### 6. Test Integration

#### Option A: Test Script

```bash
php test-supabase-email.php
```

#### Option B: API Test

```bash
curl -X POST http://localhost:8000/api/request-otp \
  -H "Content-Type: application/json" \
  -d '{"email": "main.john.doe@cvsu.edu.ph"}'
```

Check your email inbox for the OTP code.

## Files Reference

### New Files Created

| File | Purpose |
|------|---------|
| `backend/app/Services/SupabaseEmailService.php` | Main email service using Supabase |
| `supabase-edge-function-send-email.ts` | Edge function for sending emails via Resend |
| `SUPABASE_EMAIL_MIGRATION_GUIDE.md` | Detailed migration guide |
| `SUPABASE_MIGRATION_README.md` | This file - quick reference |
| `setup-supabase-email.sh` | Automated setup script (Linux/Mac) |
| `setup-supabase-email.bat` | Automated setup script (Windows) |
| `test-supabase-email.php` | Test script for email integration |

### Modified Files

| File | Changes |
|------|---------|
| `backend/app/Http/Controllers/AuthController.php` | Uses SupabaseEmailService instead of notifications |
| `backend/config/services.php` | Added Supabase configuration |
| `backend/.env.example` | Added Supabase variables, deprecated SendGrid |

### Deprecated Files (Can be deleted after testing)

| File | Reason |
|------|--------|
| ~~`backend/app/Mail/SendGridTransport.php`~~ | ✅ Deleted |
| ~~`backend/app/Notifications/OtpPasswordResetNotification.php`~~ | ✅ Deleted |
| ~~`backend/app/Notifications/ResetPasswordNotification.php`~~ | ✅ Deleted |

**Status**: All SendGrid files have been removed. See `SENDGRID_CLEANUP_COMPLETE.md` for details.

## Troubleshooting

### Email Not Sending

1. **Check Edge Function Logs**
   - Go to Supabase Dashboard → Edge Functions → send-email → Logs
   - Look for errors

2. **Verify Resend API Key**
   ```bash
   supabase secrets list
   ```

3. **Check Laravel Logs**
   ```bash
   tail -f backend/storage/logs/laravel.log
   ```

### Email Not Arriving

1. Check spam/junk folder
2. Verify sender email is verified in Resend
3. Check Resend dashboard for delivery logs
4. Ensure you haven't exceeded rate limits (100/day)

### CORS Errors

- Ensure Edge Function includes CORS headers (already included in template)
- Check that your frontend URL is correct

### Configuration Errors

```bash
# Clear all caches
cd backend
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

## Rate Limits

### Resend Free Tier
- 100 emails per day
- 3,000 emails per month
- No expiration

### Supabase Free Tier
- 500,000 Edge Function invocations per month
- 2GB database storage
- 50GB bandwidth

## Monitoring

### Check Email Delivery

1. **Resend Dashboard**
   - View all sent emails
   - Check delivery status
   - See bounce/complaint rates

2. **Supabase Logs**
   - Monitor Edge Function invocations
   - Check for errors
   - View performance metrics

3. **Laravel Logs**
   ```bash
   tail -f backend/storage/logs/laravel.log | grep "OTP"
   ```

## Upgrading

If you exceed free tier limits:

### Resend Pricing
- **Pro**: $20/month for 50,000 emails
- **Business**: $80/month for 200,000 emails

### Supabase Pricing
- **Pro**: $25/month for 2M Edge Function invocations
- Unlikely to need upgrade for email use case

## Rollback Plan

If you need to revert to SendGrid:

1. Restore `.env`:
   ```env
   MAIL_MAILER=sendgrid
   SENDGRID_API_KEY=your-sendgrid-api-key
   ```

2. Revert `AuthController.php` changes (use git)
3. Clear cache: `php artisan config:clear`

## Support Resources

- **Supabase Docs**: https://supabase.com/docs
- **Resend Docs**: https://resend.com/docs
- **Edge Functions Guide**: https://supabase.com/docs/guides/functions
- **Migration Guide**: See `SUPABASE_EMAIL_MIGRATION_GUIDE.md`

## Security Notes

1. **Never commit** `.env` file to git
2. **Keep secret** your service_role_key
3. **Use environment variables** for all sensitive data
4. **Verify sender domain** in Resend to prevent spoofing
5. **Monitor logs** for suspicious activity

## Next Steps After Migration

- [ ] Test OTP flow end-to-end
- [ ] Monitor email delivery for 1 week
- [ ] Set up alerts in Resend for bounces
- [ ] Document any custom configurations
- [ ] Remove SendGrid files after confirming success
- [ ] Update team documentation

## Questions?

See the detailed guide: `SUPABASE_EMAIL_MIGRATION_GUIDE.md`
