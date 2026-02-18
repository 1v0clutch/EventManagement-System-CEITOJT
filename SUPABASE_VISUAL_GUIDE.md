# 📸 Supabase Setup - Visual Guide

This guide provides detailed visual descriptions of each step to create your Supabase project.

---

## 🎯 Overview

**Time Required**: 10-15 minutes  
**Cost**: Free  
**Prerequisites**: Email address or GitHub account

---

## Step 1: Sign Up for Supabase

### 1.1 Visit Supabase Website

**URL**: https://supabase.com

**What you'll see:**
- Green "Start your project" button in the center
- "Sign In" button in top right corner
- Hero section with "Build in a weekend, Scale to millions"

**Action**: Click **"Start your project"** or **"Sign In"**

---

### 1.2 Choose Sign-Up Method

**What you'll see:**
A login page with three options:
- **GitHub** button (black with GitHub logo)
- **Google** button (white with Google logo)  
- **Email** input field with "Continue with Email" button

**Recommended**: Click **"Continue with GitHub"** (fastest)

**Alternative**: Enter your email and click "Continue with Email"

---

### 1.3 Authorize (if using GitHub)

**What you'll see:**
- GitHub authorization page
- "Authorize Supabase" button
- List of permissions requested

**Action**: Click **"Authorize Supabase"**

---

## Step 2: Create Organization

### 2.1 Organization Setup (First Time Only)

**What you'll see:**
- "Create a new organization" dialog
- Organization name input field
- Plan selection (Free/Pro/Team)

**What to do:**
1. Enter organization name: `My Company` or your name
2. Select plan: **Free** (already selected)
3. Click **"Create organization"**

**Note**: If you already have an organization, you'll skip this step.

---

## Step 3: Create New Project

### 3.1 New Project Button

**What you'll see:**
- Supabase dashboard with sidebar on left
- "New Project" button (green, top right)
- List of existing projects (if any)

**Action**: Click **"New Project"** button

---

### 3.2 Project Configuration Form

**What you'll see:**
A form with these fields:

**1. Organization**
- Dropdown showing your organization name
- (Already selected if you only have one)

**2. Name your project**
- Text input field
- Placeholder: "My awesome project"

**What to enter:**
```
event-management-email
```
(or any name you prefer)

**3. Database Password**
- Password input field
- "Generate a password" button (circular arrow icon)
- Password strength indicator

**What to do:**
- Click the **"Generate a password"** button
- A strong password will be auto-generated
- **IMPORTANT**: Click the copy icon and save this password!
- You'll need it for direct database access

**4. Region**
- Dropdown menu with regions
- Shows latency to each region

**What to select:**
- For Philippines/Asia: **Southeast Asia (Singapore)**
- For US East: **East US (North Virginia)**
- For US West: **West US (Oregon)**
- For Europe: **Europe (Frankfurt)**

**5. Pricing Plan**
- Free plan (already selected)
- Shows: "500MB Database • 1GB Storage • 2GB Bandwidth"

**Action**: Click **"Create new project"** button (green, bottom right)

---

### 3.3 Project Creation Progress

**What you'll see:**
- "Setting up project..." message
- Progress indicator/spinner
- Estimated time: 1-2 minutes

**What's happening:**
- Creating database
- Setting up API
- Configuring Edge Functions
- Initializing storage

**Wait**: Don't close the browser - let it complete

---

### 3.4 Project Ready

**What you'll see:**
- Dashboard with your project name at top
- Left sidebar with menu items:
  - Home
  - Table Editor
  - SQL Editor
  - Database
  - Authentication
  - Storage
  - Edge Functions
  - Logs
  - Settings (gear icon at bottom)

**Success!** Your project is ready.

---

## Step 4: Get Your Credentials

### 4.1 Navigate to API Settings

**What to do:**
1. Look at left sidebar
2. Scroll to bottom
3. Click **"Settings"** (gear icon)
4. In the settings menu, click **"API"**

---

### 4.2 API Settings Page

**What you'll see:**

**Section 1: Configuration**
- Project URL
- GraphQL URL
- API Docs link

**Section 2: Project API keys**
- `anon` `public` key (visible)
- `service_role` key (hidden with eye icon)

**Section 3: JWT Settings**
- JWT Secret (hidden)

---

### 4.3 Copy Project URL

**What you'll see:**
```
Project URL
https://xxxxxxxxxxxxx.supabase.co
```

**What to do:**
1. Find the **"Project URL"** section
2. Click the **copy icon** (two overlapping squares) next to the URL
3. Save it in a text file as:
   ```
   SUPABASE_URL=https://xxxxxxxxxxxxx.supabase.co
   ```

---

### 4.4 Copy Anon Key

**What you'll see:**
```
anon public
eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6...
[Copy icon]
```

**What to do:**
1. Find the **"anon public"** key
2. Click the **copy icon** next to it
3. Save it as:
   ```
   SUPABASE_ANON_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
   ```

---

### 4.5 Copy Service Role Key

**What you'll see:**
```
service_role secret
••••••••••••••••••••••••••••••••••••••••
[Eye icon] [Copy icon]
```

**What to do:**
1. Find the **"service_role secret"** key
2. Click the **eye icon** to reveal the key
3. Click the **copy icon** to copy it
4. Save it as:
   ```
   SUPABASE_SERVICE_ROLE_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
   ```

**⚠️ IMPORTANT**: This is a secret key! Never share it or commit it to git!

---

## Step 5: Set Up Resend

### 5.1 Visit Resend Website

**URL**: https://resend.com

**What you'll see:**
- Hero section: "Email for developers"
- "Get Started" button
- "Sign In" link in top right

**Action**: Click **"Get Started"** or **"Sign In"**

---

### 5.2 Sign Up

**What you'll see:**
- Email input field
- "Continue" button

**What to do:**
1. Enter your email address
2. Click **"Continue"**
3. Check your email for verification link
4. Click the verification link
5. Complete your profile

---

### 5.3 Resend Dashboard

**What you'll see:**
- Left sidebar with:
  - Emails
  - API Keys
  - Domains
  - Webhooks
  - Settings
- Main area showing email statistics

---

### 5.4 Create API Key

**What to do:**
1. Click **"API Keys"** in left sidebar
2. Click **"Create API Key"** button (top right)

**What you'll see:**
- "Create API Key" dialog
- Name input field
- Permission dropdown (Full Access/Sending Access)

**What to enter:**
1. Name: `event-management-email`
2. Permission: **Sending Access** (recommended)
3. Click **"Create"**

---

### 5.5 Copy API Key

**What you'll see:**
- Success message
- Your API key displayed (starts with `re_`)
- Copy button
- **Warning**: "Make sure to copy your API key now. You won't be able to see it again!"

**What to do:**
1. Click **"Copy"** button
2. Save it immediately as:
   ```
   RESEND_API_KEY=re_xxxxxxxxxxxxxxxxxxxxx
   ```

**⚠️ IMPORTANT**: You can't see this key again after closing the dialog!

---

### 5.6 Verify Domain (Optional for Production)

**For Testing**: Skip this step - use `onboarding@resend.dev`

**For Production**:

**What to do:**
1. Click **"Domains"** in left sidebar
2. Click **"Add Domain"** button

**What you'll see:**
- Domain input field
- "Add Domain" button

**What to enter:**
1. Your domain: `yourdomain.com`
2. Click **"Add Domain"**

**What you'll see next:**
- DNS records to add:
  - TXT record for verification
  - MX records for receiving
  - DKIM records for authentication

**What to do:**
1. Copy each DNS record
2. Add them to your domain provider (GoDaddy, Namecheap, etc.)
3. Wait 5-30 minutes for verification
4. Refresh the page - status should show "Verified"

---

## Step 6: Configure Laravel

### 6.1 Open .env File

**Location**: `backend/.env`

**What to do:**
1. Open the file in your text editor
2. Scroll to the mail configuration section

---

### 6.2 Add Supabase Configuration

**What to add:**
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

**Replace**:
- `xxxxxxxxxxxxx` with your actual values
- `eyJhbG...` with your actual keys
- `noreply@yourdomain.com` with your verified email

---

### 6.3 Save and Close

**What to do:**
1. Save the file (Ctrl+S or Cmd+S)
2. Close the editor

---

## Step 7: Deploy Edge Function

### 7.1 Open Terminal/Command Prompt

**Windows**: Press `Win + R`, type `cmd`, press Enter

**Mac**: Press `Cmd + Space`, type `terminal`, press Enter

**Linux**: Press `Ctrl + Alt + T`

---

### 7.2 Install Supabase CLI

**What to type:**
```bash
npm install -g supabase
```

**What you'll see:**
- Download progress
- Installation messages
- "added 1 package" message

**Wait**: Let it complete (1-2 minutes)

---

### 7.3 Login to Supabase

**What to type:**
```bash
supabase login
```

**What you'll see:**
- "Opening browser to authenticate..."
- Browser window opens automatically

**In browser:**
- Supabase authorization page
- "Authorize Supabase CLI" button

**What to do:**
1. Click **"Authorize Supabase CLI"**
2. Return to terminal

**In terminal:**
- "Logged in successfully" message

---

### 7.4 Link Your Project

**What to type:**
```bash
supabase link
```

**What you'll see:**
- List of your organizations
- Prompt to select organization

**What to do:**
1. Use arrow keys to select your organization
2. Press Enter
3. Select your project from the list
4. Press Enter

**Alternative** (if you know your project ref):
```bash
supabase link --project-ref xxxxxxxxxxxxx
```

**What you'll see:**
- "Linked to project xxxxxxxxxxxxx"

---

### 7.5 Create Function Directory

**What to type:**
```bash
mkdir -p supabase/functions/send-email
```

**What happens:**
- Creates folder structure
- No output if successful

---

### 7.6 Copy Function Code

**Windows (PowerShell):**
```powershell
Copy-Item supabase-edge-function-send-email.ts supabase/functions/send-email/index.ts
```

**Mac/Linux:**
```bash
cp supabase-edge-function-send-email.ts supabase/functions/send-email/index.ts
```

**What happens:**
- Copies the Edge Function code
- No output if successful

---

### 7.7 Set Resend API Key

**What to type:**
```bash
supabase secrets set RESEND_API_KEY=re_xxxxxxxxxxxxxxxxxxxxx
```

**Replace** `re_xxx...` with your actual Resend API key

**What you'll see:**
- "Setting secret RESEND_API_KEY..."
- "Secret set successfully"

---

### 7.8 Deploy Function

**What to type:**
```bash
supabase functions deploy send-email
```

**What you'll see:**
- "Deploying function send-email..."
- Upload progress
- "Function send-email deployed successfully!"
- Function URL displayed

**Success!** Your Edge Function is live.

---

## Step 8: Verify in Dashboard

### 8.1 Check Edge Functions

**What to do:**
1. Go back to Supabase dashboard
2. Click **"Edge Functions"** in left sidebar

**What you'll see:**
- List of functions
- `send-email` function listed
- Status: **Active** (green dot)
- Last deployed time

---

### 8.2 View Function Details

**What to do:**
1. Click on **"send-email"** function

**What you'll see:**
- Function details page
- Tabs: Details, Logs, Settings
- Function URL
- Invocation count

---

### 8.3 Check Logs

**What to do:**
1. Click **"Logs"** tab

**What you'll see:**
- Real-time logs
- Invocation history
- Error messages (if any)

**Note**: Logs will be empty until you send your first email

---

## Step 9: Test Your Setup

### 9.1 Run Test Script

**What to type:**
```bash
php test-supabase-email.php
```

**What you'll see:**
- "Supabase Email Integration Test"
- Prompt: "Enter test email address:"

**What to do:**
1. Enter your email address
2. Press Enter

**What you'll see:**
- "Sending test email..."
- "✅ Email sent successfully!"
- Response details

---

### 9.2 Check Your Email

**What to do:**
1. Open your email inbox
2. Look for email from your configured sender
3. Subject: "🔐 Supabase Email Test"

**What you should see:**
- Professional HTML email
- Test OTP code
- Proper formatting

**If not in inbox**: Check spam/junk folder

---

### 9.3 Check Supabase Logs

**What to do:**
1. Go to Supabase dashboard
2. Edge Functions → send-email → Logs

**What you'll see:**
- New log entry
- Status: 200 (success)
- Execution time
- Request details

---

### 9.4 Check Resend Dashboard

**What to do:**
1. Go to Resend dashboard
2. Click **"Emails"**

**What you'll see:**
- Your test email listed
- Status: **Delivered** (green)
- Timestamp
- Recipient email

---

## ✅ Success Checklist

- [ ] Supabase account created
- [ ] Supabase project created
- [ ] Project URL copied
- [ ] Anon key copied
- [ ] Service role key copied
- [ ] Resend account created
- [ ] Resend API key created and copied
- [ ] Laravel .env updated
- [ ] Supabase CLI installed
- [ ] Logged in to Supabase CLI
- [ ] Project linked
- [ ] Edge Function deployed
- [ ] Test email sent successfully
- [ ] Email received in inbox
- [ ] Logs show successful invocation

---

## 🎉 You're Done!

Your Supabase project is fully set up and ready to send emails!

**Next steps:**
1. Run cleanup script: `cleanup-sendgrid.bat`
2. Test OTP flow: See [`QUICK_REFERENCE.md`](QUICK_REFERENCE.md)
3. Deploy to production: See [`MIGRATION_CHECKLIST.md`](MIGRATION_CHECKLIST.md)

---

## 📞 Need Help?

- **Can't find something?** Check [`SUPABASE_PROJECT_SETUP.md`](SUPABASE_PROJECT_SETUP.md)
- **Having issues?** See [`QUICK_REFERENCE.md`](QUICK_REFERENCE.md) troubleshooting
- **Want more details?** Read [`SUPABASE_EMAIL_MIGRATION_GUIDE.md`](SUPABASE_EMAIL_MIGRATION_GUIDE.md)

---

*Last Updated: February 18, 2026*  
*For text-based guide, see: [`SUPABASE_PROJECT_SETUP.md`](SUPABASE_PROJECT_SETUP.md)*
