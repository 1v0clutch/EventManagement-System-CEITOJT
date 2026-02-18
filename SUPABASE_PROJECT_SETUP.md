# 🚀 How to Create a Supabase Project

This guide walks you through creating a Supabase project for your email migration.

---

## Step 1: Create Supabase Account

### 1.1 Go to Supabase Website
- Visit: https://supabase.com
- Click **"Start your project"** or **"Sign In"**

### 1.2 Sign Up Options
Choose one of these methods:
- **GitHub** (Recommended - fastest)
- **Google**
- **Email/Password**

### 1.3 Complete Registration
- Follow the prompts to verify your account
- You'll be redirected to the Supabase dashboard

---

## Step 2: Create New Project

### 2.1 Access Dashboard
- After login, you'll see the Supabase dashboard
- Click **"New Project"** button (green button, top right)

### 2.2 Create Organization (First Time Only)
If this is your first project:
- Click **"New organization"**
- Enter organization name (e.g., "My Company" or your name)
- Choose plan: **Free** (perfect for this use case)
- Click **"Create organization"**

### 2.3 Configure New Project
Fill in the project details:

**Project Name:**
```
event-management-email
```
(or any name you prefer)

**Database Password:**
- Generate a strong password (click the generate button)
- **IMPORTANT**: Save this password securely!
- You'll need it if you want to access the database directly

**Region:**
- Choose the region closest to your users
- Examples:
  - **Southeast Asia (Singapore)** - For Philippines/Asia
  - **East US (North Virginia)** - For US East Coast
  - **West US (Oregon)** - For US West Coast
  - **Europe (Frankfurt)** - For Europe

**Pricing Plan:**
- Select **"Free"** (includes everything you need)

### 2.4 Create Project
- Click **"Create new project"**
- Wait 1-2 minutes for project setup
- You'll see a progress indicator

---

## Step 3: Get Your Credentials

Once your project is ready:

### 3.1 Navigate to API Settings
- In the left sidebar, click **"Settings"** (gear icon at bottom)
- Click **"API"** in the settings menu

### 3.2 Copy Project URL
Look for **"Project URL"** section:
```
https://xxxxxxxxxxxxx.supabase.co
```
- Click the copy icon
- Save this - you'll need it for `SUPABASE_URL`

### 3.3 Copy API Keys
Look for **"Project API keys"** section:

**anon/public key:**
```
eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```
- Click the copy icon next to `anon` `public`
- Save this - you'll need it for `SUPABASE_ANON_KEY`

**service_role key:**
```
eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```
- Click the eye icon to reveal the key
- Click the copy icon
- **IMPORTANT**: Keep this secret! Never commit to git!
- Save this - you'll need it for `SUPABASE_SERVICE_ROLE_KEY`

### 3.4 Save Your Credentials
Create a temporary file to store these (don't commit to git):

```
SUPABASE_URL=https://xxxxxxxxxxxxx.supabase.co
SUPABASE_ANON_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
SUPABASE_SERVICE_ROLE_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

---

## Step 4: Set Up Resend Account

You also need a Resend account for sending emails:

### 4.1 Go to Resend Website
- Visit: https://resend.com
- Click **"Sign Up"** or **"Get Started"**

### 4.2 Sign Up
- Enter your email address
- Verify your email
- Complete registration

### 4.3 Verify Your Domain (Optional but Recommended)

**For Production:**
1. Go to **"Domains"** in Resend dashboard
2. Click **"Add Domain"**
3. Enter your domain (e.g., `yourdomain.com`)
4. Add the DNS records shown to your domain provider
5. Wait for verification (usually 5-30 minutes)

**For Testing:**
- You can use Resend's test domain: `onboarding@resend.dev`
- Emails will only be sent to your verified email address

### 4.4 Get API Key
1. Go to **"API Keys"** in Resend dashboard
2. Click **"Create API Key"**
3. Name it: `event-management-email`
4. Click **"Create"**
5. Copy the API key (starts with `re_`)
6. **IMPORTANT**: Save it now - you can't see it again!

```
RESEND_API_KEY=re_xxxxxxxxxxxxxxxxxxxxx
```

---

## Step 5: Configure Your Laravel Project

### 5.1 Update .env File

Open `backend/.env` and add:

```env
# Supabase Configuration
SUPABASE_URL=https://xxxxxxxxxxxxx.supabase.co
SUPABASE_ANON_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
SUPABASE_SERVICE_ROLE_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...

# Email Configuration
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Event Management System"
MAIL_MAILER=log
```

**Replace:**
- `xxxxxxxxxxxxx` with your actual project ID
- `eyJhbG...` with your actual keys
- `noreply@yourdomain.com` with your verified email

### 5.2 Run Setup Script (Optional)

Or use the automated setup script:

**Windows:**
```cmd
setup-supabase-email.bat
```

**Linux/Mac:**
```bash
chmod +x setup-supabase-email.sh
./setup-supabase-email.sh
```

---

## Step 6: Deploy Edge Function

### 6.1 Install Supabase CLI

**Windows (using npm):**
```cmd
npm install -g supabase
```

**Mac (using Homebrew):**
```bash
brew install supabase/tap/supabase
```

**Linux:**
```bash
npm install -g supabase
```

### 6.2 Login to Supabase
```bash
supabase login
```
- This will open a browser window
- Click **"Authorize"** to grant access
- Return to terminal

### 6.3 Link Your Project
```bash
supabase link --project-ref your-project-ref
```

**To find your project ref:**
- Go to Supabase dashboard
- Your project URL is: `https://xxxxxxxxxxxxx.supabase.co`
- The `xxxxxxxxxxxxx` part is your project ref

Or use the interactive mode:
```bash
supabase link
```
- Select your organization
- Select your project

### 6.4 Create Edge Function Directory
```bash
mkdir -p supabase/functions/send-email
```

### 6.5 Copy Edge Function Code
```bash
# Windows (PowerShell)
Copy-Item supabase-edge-function-send-email.ts supabase/functions/send-email/index.ts

# Linux/Mac
cp supabase-edge-function-send-email.ts supabase/functions/send-email/index.ts
```

### 6.6 Set Resend API Key as Secret
```bash
supabase secrets set RESEND_API_KEY=re_xxxxxxxxxxxxxxxxxxxxx
```
Replace with your actual Resend API key.

### 6.7 Deploy the Function
```bash
supabase functions deploy send-email
```

You should see:
```
Deploying function send-email...
Function send-email deployed successfully!
```

### 6.8 Verify Deployment
- Go to Supabase dashboard
- Click **"Edge Functions"** in left sidebar
- You should see `send-email` function listed
- Status should be **"Active"**

---

## Step 7: Test Your Setup

### 7.1 Test Email Service
```bash
php test-supabase-email.php
```

Enter a test email address when prompted.

### 7.2 Test OTP API
```bash
curl -X POST http://localhost:8000/api/request-otp \
  -H "Content-Type: application/json" \
  -d "{\"email\": \"main.test.user@cvsu.edu.ph\"}"
```

### 7.3 Check Logs

**Laravel logs:**
```bash
tail -f backend/storage/logs/laravel.log | grep "Supabase"
```

**Supabase Edge Function logs:**
- Go to Supabase dashboard
- Click **"Edge Functions"**
- Click **"send-email"**
- Click **"Logs"** tab
- You should see invocation logs

**Resend logs:**
- Go to Resend dashboard
- Click **"Emails"**
- You should see sent emails

---

## Step 8: Verify Everything Works

### Checklist:
- [ ] Supabase project created
- [ ] Credentials copied and saved
- [ ] Resend account created
- [ ] Resend API key obtained
- [ ] Laravel .env updated
- [ ] Edge Function deployed
- [ ] Test email sent successfully
- [ ] OTP API works
- [ ] Email received in inbox

---

## 🎯 Quick Reference

### Supabase Dashboard URLs

**Main Dashboard:**
```
https://app.supabase.com
```

**Your Project:**
```
https://app.supabase.com/project/your-project-ref
```

**API Settings:**
```
https://app.supabase.com/project/your-project-ref/settings/api
```

**Edge Functions:**
```
https://app.supabase.com/project/your-project-ref/functions
```

### Resend Dashboard URLs

**Main Dashboard:**
```
https://resend.com/emails
```

**API Keys:**
```
https://resend.com/api-keys
```

**Domains:**
```
https://resend.com/domains
```

---

## 🐛 Troubleshooting

### Issue: Can't create Supabase project

**Solution:**
- Try a different browser
- Clear browser cache
- Use incognito/private mode
- Try GitHub authentication instead of email

### Issue: Edge Function deployment fails

**Solution:**
```bash
# Check if you're logged in
supabase projects list

# Re-login if needed
supabase login

# Check if project is linked
supabase status

# Re-link if needed
supabase link --project-ref your-project-ref
```

### Issue: Can't find project ref

**Solution:**
- Go to: https://app.supabase.com
- Click on your project
- Look at the URL: `https://app.supabase.com/project/xxxxxxxxxxxxx`
- The `xxxxxxxxxxxxx` is your project ref

### Issue: Resend emails not sending

**Solution:**
- Verify your domain in Resend dashboard
- Check that sender email matches verified domain
- For testing, use: `onboarding@resend.dev`
- Check Resend dashboard for error messages

---

## 💡 Tips

### For Development
- Use Resend's test domain: `onboarding@resend.dev`
- Set `MAIL_MAILER=log` in .env to avoid sending real emails
- Check Laravel logs for debugging

### For Production
- Verify your domain in Resend
- Use your own domain for sender email
- Monitor Supabase Edge Function logs
- Set up alerts in Resend dashboard

### Security
- Never commit `.env` file to git
- Keep `service_role_key` secret
- Use environment variables for all credentials
- Rotate API keys periodically

---

## 📊 Free Tier Limits

### Supabase Free Tier
- ✅ 500MB database storage
- ✅ 1GB file storage
- ✅ 2GB bandwidth
- ✅ 500K Edge Function invocations/month
- ✅ Unlimited API requests

### Resend Free Tier
- ✅ 100 emails per day
- ✅ 3,000 emails per month
- ✅ No expiration
- ✅ All features included

**Perfect for this use case!**

---

## 🎓 What You Learned

- ✅ How to create a Supabase project
- ✅ How to get Supabase credentials
- ✅ How to set up Resend account
- ✅ How to deploy Edge Functions
- ✅ How to configure Laravel with Supabase
- ✅ How to test email functionality

---

## 📞 Support

### Supabase
- Docs: https://supabase.com/docs
- Discord: https://discord.supabase.com
- GitHub: https://github.com/supabase/supabase

### Resend
- Docs: https://resend.com/docs
- Support: support@resend.com
- Status: https://status.resend.com

---

## 🎉 Next Steps

After completing this setup:

1. ✅ Run cleanup script: `cleanup-sendgrid.bat`
2. ✅ Test thoroughly: `php test-supabase-email.php`
3. ✅ Deploy to production
4. ✅ Monitor for 1 week
5. ✅ Celebrate! 🎊

---

**Setup Date**: _______________  
**Project Name**: _______________  
**Project Ref**: _______________  
**Status**: ⬜ In Progress | ⬜ Complete

---

*For more information, see [`START_HERE.md`](START_HERE.md) or [`MIGRATION_CHECKLIST.md`](MIGRATION_CHECKLIST.md)*
