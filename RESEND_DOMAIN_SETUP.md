# 📧 Resend Domain Setup Guide

## ✅ Current Configuration

Your email is now configured to use **Resend's test domain**, which works immediately without any setup!

```env
MAIL_FROM_ADDRESS=onboarding@resend.dev
MAIL_FROM_NAME="CVSU Event Management System"
```

---

## 🎯 What This Means

### Using Resend Test Domain (`onboarding@resend.dev`)

**Pros:**
- ✅ Works immediately - no setup required
- ✅ No domain verification needed
- ✅ Perfect for development and testing
- ✅ Free to use
- ✅ No DNS configuration

**Limitations:**
- ⚠️ Emails only sent to verified email addresses in Resend
- ⚠️ Need to verify recipient emails in Resend dashboard
- ⚠️ Not suitable for production with many users

---

## 🚀 How to Use Test Domain

### Step 1: Create Resend Account
1. Go to https://resend.com
2. Sign up with your email
3. Verify your email address

### Step 2: Verify Recipient Emails (Important!)

Since you're using the test domain, you need to verify each email address that will receive emails:

**In Resend Dashboard:**
1. Click **"Emails"** in sidebar
2. Click **"Verify Email"** or go to Settings
3. Add email addresses that should receive test emails:
   - `main.gabrielian.deleon@cvsu.edu.ph`
   - Any other test email addresses

**Or just use your own verified email** (the one you signed up with) for testing.

### Step 3: Create API Key
1. Go to **"API Keys"** in Resend dashboard
2. Click **"Create API Key"**
3. Name: `cvsu-event-management`
4. Permission: **Sending Access**
5. Copy the API key (starts with `re_`)

### Step 4: No Domain Setup Needed!
- Skip the "Domains" section entirely
- You're using Resend's domain, so no verification needed

---

## 🧪 Testing

### Test with Your Own Email

```bash
php test-supabase-email.php
```

When prompted, enter your email (the one you used to sign up for Resend).

**Expected result:**
- ✅ Email sent successfully
- ✅ Email received in inbox
- ✅ Sender shows: "CVSU Event Management System <onboarding@resend.dev>"

### Test OTP Flow

```bash
curl -X POST http://localhost:8000/api/request-otp \
  -H "Content-Type: application/json" \
  -d "{\"email\": \"main.gabrielian.deleon@cvsu.edu.ph\"}"
```

**Note:** This will only work if `main.gabrielian.deleon@cvsu.edu.ph` is verified in Resend, or if you change it to your verified email.

---

## 🎓 For Production (Future Options)

When you're ready to deploy to production with real users, you have these options:

### Option 1: Buy Your Own Domain (Recommended)

**Cost:** $10-15/year

**Examples:**
- `cvsu-events.com`
- `cvsuevents.com`
- `cvsueventmanagement.com`

**Where to buy:**
- Namecheap: https://www.namecheap.com
- GoDaddy: https://www.godaddy.com
- Google Domains: https://domains.google

**Setup time:** 30 minutes

**Steps:**
1. Buy domain
2. Add domain in Resend
3. Add DNS records to your domain provider
4. Wait for verification (5-30 minutes)
5. Update `.env` with your domain

### Option 2: Use Free Subdomain Services

**Examples:**
- `cvsu-events.freenom.com` (Freenom)
- `cvsu-events.tk` (Free TLD)

**Pros:**
- ✅ Free
- ✅ Works like a real domain

**Cons:**
- ⚠️ Less professional
- ⚠️ May have limitations
- ⚠️ Can be revoked

### Option 3: Keep Using Test Domain (Not Recommended for Production)

**Only if:**
- You have very few users
- You can manually verify each user's email in Resend
- It's an internal tool

---

## 💡 My Recommendation

### For Now (Development/Testing):
```env
# Use Resend test domain - works immediately!
MAIL_FROM_ADDRESS=onboarding@resend.dev
MAIL_FROM_NAME="CVSU Event Management System"
```

**Perfect for:**
- ✅ Development
- ✅ Testing
- ✅ Demo/presentation
- ✅ Small internal testing group

### For Production (When Ready):

**Buy a domain** (best option):
```env
# Example with your own domain
MAIL_FROM_ADDRESS=noreply@cvsu-events.com
MAIL_FROM_NAME="CVSU Event Management System"
```

**Cost:** ~$12/year  
**Benefit:** Professional, unlimited users, better deliverability

---

## 📋 Current Setup Summary

**Your current configuration:**
```env
MAIL_FROM_ADDRESS=onboarding@resend.dev
MAIL_FROM_NAME="CVSU Event Management System"
```

**What you need to do:**
1. ✅ Create Resend account
2. ✅ Get API key
3. ✅ Verify your email in Resend (automatic when you sign up)
4. ✅ Test with your own email
5. ⏳ (Optional) Verify additional test emails in Resend

**What you DON'T need to do:**
- ❌ Buy a domain
- ❌ Configure DNS
- ❌ Verify domain in Resend
- ❌ Contact IT department

---

## 🔧 How to Change Domain Later

When you're ready to use your own domain:

### Step 1: Buy Domain
Choose a domain registrar and purchase your domain.

### Step 2: Add Domain in Resend
1. Go to Resend dashboard
2. Click **"Domains"**
3. Click **"Add Domain"**
4. Enter your domain (e.g., `cvsu-events.com`)

### Step 3: Add DNS Records
Resend will show you DNS records to add:
```
Type: TXT
Name: @
Value: resend-verify=xxxxxxxxxxxxx

Type: TXT
Name: resend._domainkey
Value: p=MIGfMA0GCSqGSIb3DQEBAQUAA4GN...

Type: MX
Name: @
Value: feedback-smtp.us-east-1.amazonses.com
Priority: 10
```

### Step 4: Add Records to Domain Provider
- Log in to your domain registrar (Namecheap, GoDaddy, etc.)
- Go to DNS settings
- Add each record shown by Resend
- Save changes

### Step 5: Wait for Verification
- Usually takes 5-30 minutes
- Check Resend dashboard for "Verified" status

### Step 6: Update .env
```env
MAIL_FROM_ADDRESS=noreply@cvsu-events.com
MAIL_FROM_NAME="CVSU Event Management System"
```

### Step 7: Clear Cache and Test
```bash
cd backend
php artisan config:clear
php artisan cache:clear
php test-supabase-email.php
```

---

## 🎯 Quick Decision Guide

**Choose Test Domain (`onboarding@resend.dev`) if:**
- ✅ You're still developing
- ✅ You're testing the system
- ✅ You have < 10 test users
- ✅ You don't want to spend money yet
- ✅ You need to start immediately

**Buy Your Own Domain if:**
- ✅ You're deploying to production
- ✅ You have many users
- ✅ You want professional emails
- ✅ You want better deliverability
- ✅ You can spend $10-15/year

---

## ✅ What's Already Done

Your `.env` file is already configured with:
```env
MAIL_FROM_ADDRESS=onboarding@resend.dev
MAIL_FROM_NAME="CVSU Event Management System"
```

**You're ready to:**
1. Create Resend account
2. Get API key
3. Deploy Edge Function
4. Start testing!

**No domain setup needed!** 🎉

---

## 📞 Need Help?

- **Resend test domain**: Already configured! ✅
- **Buy domain**: See "For Production" section above
- **DNS setup**: See "How to Change Domain Later" section
- **Testing**: See [`NEXT_STEPS_FOR_DEVELOPER.md`](NEXT_STEPS_FOR_DEVELOPER.md)

---

**Current Status**: ✅ Configured with Resend test domain - ready to use!  
**Next Step**: Create Resend account and get API key → [`SUPABASE_PROJECT_SETUP.md`](SUPABASE_PROJECT_SETUP.md)
