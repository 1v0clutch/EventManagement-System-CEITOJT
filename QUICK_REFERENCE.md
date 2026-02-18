# Supabase Email - Quick Reference Card

## 🚀 Quick Setup (5 Minutes)

### 1. Get Credentials
- **Supabase**: Dashboard → Settings → API → Copy URL + Keys
- **Resend**: Dashboard → API Keys → Create + Copy

### 2. Deploy Edge Function
```bash
supabase functions deploy send-email
supabase secrets set RESEND_API_KEY=your-key
```

### 3. Configure Laravel
```bash
# Windows
setup-supabase-email.bat

# Linux/Mac
./setup-supabase-email.sh
```

### 4. Test
```bash
php test-supabase-email.php
```

---

## 📋 Environment Variables

```env
# Required
SUPABASE_URL=https://xxxxx.supabase.co
SUPABASE_ANON_KEY=eyJhbG...
SUPABASE_SERVICE_ROLE_KEY=eyJhbG...
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Your App"

# Optional
MAIL_MAILER=log  # For local development
```

---

## 🧪 Testing Commands

### Test Email Service
```bash
php test-supabase-email.php
```

### Test OTP API
```bash
curl -X POST http://localhost:8000/api/request-otp \
  -H "Content-Type: application/json" \
  -d '{"email": "main.test.user@cvsu.edu.ph"}'
```

### Check Logs
```bash
# Laravel logs
tail -f backend/storage/logs/laravel.log | grep "OTP"

# Supabase logs
# Go to: Dashboard → Edge Functions → send-email → Logs
```

---

## 🔧 Common Commands

### Clear Cache
```bash
cd backend
php artisan config:clear
php artisan cache:clear
```

### Deploy Edge Function
```bash
supabase functions deploy send-email
```

### Update Secrets
```bash
supabase secrets set RESEND_API_KEY=new-key
```

### List Secrets
```bash
supabase secrets list
```

---

## 📊 Rate Limits

| Service | Free Tier Limit |
|---------|----------------|
| Resend | 100 emails/day, 3000/month |
| Supabase Edge Functions | 500K invocations/month |

---

## 🐛 Quick Troubleshooting

### Email Not Sending?
1. Check Edge Function logs in Supabase dashboard
2. Verify `RESEND_API_KEY` is set: `supabase secrets list`
3. Check Laravel logs: `tail -f backend/storage/logs/laravel.log`

### Email Not Arriving?
1. Check spam folder
2. Verify sender email in Resend dashboard
3. Check Resend dashboard for delivery status

### Configuration Error?
```bash
cd backend
php artisan config:clear
php artisan cache:clear
```

---

## 📁 Key Files

| File | Purpose |
|------|---------|
| `backend/app/Services/SupabaseEmailService.php` | Email service |
| `supabase-edge-function-send-email.ts` | Edge function code |
| `backend/.env` | Configuration |
| `MIGRATION_CHECKLIST.md` | Step-by-step guide |

---

## 🔗 Useful Links

- **Supabase Dashboard**: https://app.supabase.com
- **Resend Dashboard**: https://resend.com/emails
- **Supabase Docs**: https://supabase.com/docs
- **Resend Docs**: https://resend.com/docs

---

## 💡 Pro Tips

1. **Local Development**: Set `MAIL_MAILER=log` to avoid sending real emails
2. **Monitoring**: Check Resend dashboard daily for first week
3. **Testing**: Use test domain in Resend for development
4. **Security**: Never commit `.env` file to git
5. **Scaling**: Upgrade Resend plan if you exceed 100 emails/day

---

## 🆘 Need Help?

1. Check `SUPABASE_EMAIL_MIGRATION_GUIDE.md` for detailed docs
2. Review `MIGRATION_CHECKLIST.md` for step-by-step guide
3. Check Edge Function logs in Supabase dashboard
4. Review Laravel logs: `backend/storage/logs/laravel.log`

---

## 📞 Support Resources

- Supabase Discord: https://discord.supabase.com
- Resend Support: support@resend.com
- Edge Functions Guide: https://supabase.com/docs/guides/functions

---

**Last Updated**: February 18, 2026  
**Version**: 1.0
