# Supabase Email Migration Checklist

Use this checklist to ensure a smooth migration from SendGrid to Supabase.

## Pre-Migration

- [ ] Read `SUPABASE_MIGRATION_README.md`
- [ ] Read `SUPABASE_EMAIL_MIGRATION_GUIDE.md`
- [ ] Backup current `.env` file
- [ ] Backup database (if needed)
- [ ] Note current SendGrid usage/stats

## Account Setup

### Resend Account
- [ ] Create Resend account at https://resend.com
- [ ] Verify your domain (or use test domain for development)
- [ ] Generate API key
- [ ] Save API key securely

### Supabase Account
- [ ] Create/access Supabase project at https://supabase.com
- [ ] Copy Project URL from Settings → API
- [ ] Copy anon/public key from Settings → API
- [ ] Copy service_role key from Settings → API
- [ ] Save credentials securely

## Edge Function Deployment

Choose one method and complete all steps:

### Method A: Supabase CLI
- [ ] Install Supabase CLI: `npm install -g supabase`
- [ ] Login: `supabase login`
- [ ] Link project: `supabase link --project-ref YOUR_REF`
- [ ] Create function directory: `mkdir -p supabase/functions/send-email`
- [ ] Copy function code to `supabase/functions/send-email/index.ts`
- [ ] Set Resend secret: `supabase secrets set RESEND_API_KEY=your-key`
- [ ] Deploy: `supabase functions deploy send-email`
- [ ] Verify deployment in Supabase dashboard

### Method B: Supabase Dashboard
- [ ] Go to Edge Functions in Supabase dashboard
- [ ] Create new function named `send-email`
- [ ] Copy code from `supabase-edge-function-send-email.ts`
- [ ] Go to Settings → Edge Functions → Secrets
- [ ] Add secret: `RESEND_API_KEY` with your Resend API key
- [ ] Deploy the function
- [ ] Verify function is active

## Laravel Configuration

### Automated Setup (Recommended)
- [ ] Run setup script:
  - Windows: `setup-supabase-email.bat`
  - Linux/Mac: `./setup-supabase-email.sh`
- [ ] Verify `.env` was updated correctly

### Manual Setup (Alternative)
- [ ] Open `backend/.env`
- [ ] Comment out: `MAIL_MAILER=sendgrid`
- [ ] Comment out: `SENDGRID_API_KEY=...`
- [ ] Add: `MAIL_MAILER=log`
- [ ] Add: `SUPABASE_URL=https://your-project.supabase.co`
- [ ] Add: `SUPABASE_ANON_KEY=your-anon-key`
- [ ] Add: `SUPABASE_SERVICE_ROLE_KEY=your-service-role-key`
- [ ] Update: `MAIL_FROM_ADDRESS=your-verified-email@domain.com`
- [ ] Update: `MAIL_FROM_NAME="Your App Name"`

### Cache Clearing
- [ ] Run: `cd backend`
- [ ] Run: `php artisan config:clear`
- [ ] Run: `php artisan cache:clear`
- [ ] Run: `php artisan route:clear`
- [ ] Run: `php artisan view:clear`

## Testing

### Test Script
- [ ] Run: `php test-supabase-email.php`
- [ ] Enter test email address
- [ ] Check email inbox
- [ ] Verify OTP code received
- [ ] Verify email formatting looks good

### API Testing
- [ ] Start Laravel server: `cd backend && php artisan serve`
- [ ] Test OTP request:
  ```bash
  curl -X POST http://localhost:8000/api/request-otp \
    -H "Content-Type: application/json" \
    -d '{"email": "main.test.user@cvsu.edu.ph"}'
  ```
- [ ] Check response is successful
- [ ] Check email received
- [ ] Verify OTP code in email

### Full Flow Testing
- [ ] Open frontend application
- [ ] Go to "Forgot Password" page
- [ ] Enter valid email address
- [ ] Click "Send OTP"
- [ ] Check email inbox
- [ ] Copy OTP code
- [ ] Enter OTP in verification page
- [ ] Set new password
- [ ] Verify password reset confirmation email received
- [ ] Login with new password

## Monitoring Setup

### Resend Dashboard
- [ ] Access Resend dashboard
- [ ] Check "Emails" section shows sent emails
- [ ] Verify delivery status
- [ ] Set up email notifications for bounces (optional)

### Supabase Dashboard
- [ ] Go to Edge Functions → send-email
- [ ] Check "Logs" tab
- [ ] Verify function invocations appear
- [ ] Check for any errors

### Laravel Logs
- [ ] Run: `tail -f backend/storage/logs/laravel.log`
- [ ] Trigger OTP request
- [ ] Verify log shows: "OTP sent successfully via Supabase"
- [ ] Check for any errors

## Production Deployment

### Environment Variables
- [ ] Update production `.env` with Supabase credentials
- [ ] Verify `MAIL_FROM_ADDRESS` is verified in Resend
- [ ] Set `MAIL_MAILER=log` or remove (uses default)
- [ ] Clear production cache

### Verification
- [ ] Test OTP flow in production
- [ ] Monitor logs for first 24 hours
- [ ] Check Resend dashboard for delivery stats
- [ ] Verify no SendGrid API calls in logs

## Post-Migration

### Immediate (Day 1)
- [ ] Monitor email delivery rate
- [ ] Check for any error reports from users
- [ ] Verify all OTP emails are being delivered
- [ ] Check Resend dashboard for bounces/complaints

### Short-term (Week 1)
- [ ] Review Supabase Edge Function logs
- [ ] Check Resend usage stats
- [ ] Confirm no SendGrid charges
- [ ] Document any issues encountered

### Long-term (Month 1)
- [ ] Review monthly email volume
- [ ] Confirm staying within free tier limits
- [ ] Plan for scaling if needed
- [ ] Remove SendGrid-related files (see below)

## Cleanup (After Successful Migration)

### Files to Remove
- [ ] `backend/app/Mail/SendGridTransport.php`
- [ ] `backend/app/Notifications/OtpPasswordResetNotification.php`
- [ ] `backend/app/Notifications/ResetPasswordNotification.php`
- [ ] Any SendGrid documentation files (optional)

### Environment Variables to Remove
- [ ] Remove `SENDGRID_API_KEY` from `.env`
- [ ] Remove SendGrid config from `backend/config/services.php` (optional)

### Dependencies to Remove (Optional)
- [ ] Check `composer.json` for SendGrid package
- [ ] Run: `composer remove sendgrid/sendgrid` (if installed)

## Rollback Plan (If Needed)

If something goes wrong:

- [ ] Restore `.env` backup
- [ ] Set `MAIL_MAILER=sendgrid`
- [ ] Set `SENDGRID_API_KEY=your-key`
- [ ] Revert `AuthController.php` changes: `git checkout backend/app/Http/Controllers/AuthController.php`
- [ ] Revert `AppServiceProvider.php` changes: `git checkout backend/app/Providers/AppServiceProvider.php`
- [ ] Clear cache: `php artisan config:clear`
- [ ] Test SendGrid flow

## Troubleshooting Reference

### Issue: Email not sending
- [ ] Check Edge Function logs in Supabase
- [ ] Verify Resend API key is set correctly
- [ ] Check Laravel logs for errors
- [ ] Verify `.env` configuration

### Issue: Email not arriving
- [ ] Check spam/junk folder
- [ ] Verify sender email in Resend
- [ ] Check Resend dashboard for delivery status
- [ ] Verify rate limits not exceeded

### Issue: CORS errors
- [ ] Verify Edge Function includes CORS headers
- [ ] Check frontend URL configuration
- [ ] Review Edge Function logs

### Issue: Configuration errors
- [ ] Clear all Laravel caches
- [ ] Verify `.env` syntax
- [ ] Check for typos in credentials
- [ ] Restart Laravel server

## Success Criteria

Migration is successful when:

- [ ] OTP emails are being sent via Supabase
- [ ] Users can receive OTP codes
- [ ] Password reset flow works end-to-end
- [ ] Confirmation emails are delivered
- [ ] No SendGrid API calls in logs
- [ ] No errors in Supabase Edge Function logs
- [ ] Email delivery rate is 100% (or close)
- [ ] Users report no issues

## Notes

Use this space to document any custom configurations or issues:

```
Date: _______________
Notes:
_____________________
_____________________
_____________________
```

## Support

If you encounter issues:

1. Check `SUPABASE_EMAIL_MIGRATION_GUIDE.md` for detailed troubleshooting
2. Review Supabase documentation: https://supabase.com/docs
3. Review Resend documentation: https://resend.com/docs
4. Check Laravel logs: `backend/storage/logs/laravel.log`
5. Check Edge Function logs in Supabase dashboard

---

**Migration Date**: _______________  
**Completed By**: _______________  
**Status**: ⬜ Not Started | ⬜ In Progress | ⬜ Completed | ⬜ Rolled Back
