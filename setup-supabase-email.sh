#!/bin/bash

# Supabase Email Setup Script
# This script helps you set up Supabase email integration

echo "=========================================="
echo "Supabase Email Migration Setup"
echo "=========================================="
echo ""

# Check if .env file exists
if [ ! -f "backend/.env" ]; then
    echo "❌ Error: backend/.env file not found"
    echo "Please copy backend/.env.example to backend/.env first"
    exit 1
fi

echo "This script will help you configure Supabase for email sending."
echo ""

# Prompt for Supabase URL
read -p "Enter your Supabase Project URL (e.g., https://xxxxx.supabase.co): " SUPABASE_URL

# Prompt for Supabase Anon Key
read -p "Enter your Supabase Anon Key: " SUPABASE_ANON_KEY

# Prompt for Supabase Service Role Key
read -sp "Enter your Supabase Service Role Key (hidden): " SUPABASE_SERVICE_ROLE_KEY
echo ""

# Prompt for email from address
read -p "Enter your FROM email address (must be verified in Resend): " MAIL_FROM_ADDRESS

# Prompt for email from name
read -p "Enter your FROM name (e.g., Event Management System): " MAIL_FROM_NAME

echo ""
echo "Updating backend/.env file..."

# Update or add Supabase configuration
if grep -q "SUPABASE_URL=" backend/.env; then
    sed -i.bak "s|SUPABASE_URL=.*|SUPABASE_URL=$SUPABASE_URL|" backend/.env
else
    echo "SUPABASE_URL=$SUPABASE_URL" >> backend/.env
fi

if grep -q "SUPABASE_ANON_KEY=" backend/.env; then
    sed -i.bak "s|SUPABASE_ANON_KEY=.*|SUPABASE_ANON_KEY=$SUPABASE_ANON_KEY|" backend/.env
else
    echo "SUPABASE_ANON_KEY=$SUPABASE_ANON_KEY" >> backend/.env
fi

if grep -q "SUPABASE_SERVICE_ROLE_KEY=" backend/.env; then
    sed -i.bak "s|SUPABASE_SERVICE_ROLE_KEY=.*|SUPABASE_SERVICE_ROLE_KEY=$SUPABASE_SERVICE_ROLE_KEY|" backend/.env
else
    echo "SUPABASE_SERVICE_ROLE_KEY=$SUPABASE_SERVICE_ROLE_KEY" >> backend/.env
fi

if grep -q "MAIL_FROM_ADDRESS=" backend/.env; then
    sed -i.bak "s|MAIL_FROM_ADDRESS=.*|MAIL_FROM_ADDRESS=$MAIL_FROM_ADDRESS|" backend/.env
else
    echo "MAIL_FROM_ADDRESS=$MAIL_FROM_ADDRESS" >> backend/.env
fi

if grep -q "MAIL_FROM_NAME=" backend/.env; then
    sed -i.bak "s|MAIL_FROM_NAME=.*|MAIL_FROM_NAME=\"$MAIL_FROM_NAME\"|" backend/.env
else
    echo "MAIL_FROM_NAME=\"$MAIL_FROM_NAME\"" >> backend/.env
fi

# Comment out SendGrid configuration
sed -i.bak "s|^MAIL_MAILER=sendgrid|# MAIL_MAILER=sendgrid|" backend/.env
sed -i.bak "s|^SENDGRID_API_KEY=|# SENDGRID_API_KEY=|" backend/.env

# Set mail mailer to log for local development
if grep -q "^MAIL_MAILER=" backend/.env; then
    sed -i.bak "s|^MAIL_MAILER=.*|MAIL_MAILER=log|" backend/.env
else
    echo "MAIL_MAILER=log" >> backend/.env
fi

# Clean up backup files
rm -f backend/.env.bak

echo "✅ Configuration updated successfully!"
echo ""
echo "Next steps:"
echo "1. Deploy the Supabase Edge Function (see SUPABASE_EMAIL_MIGRATION_GUIDE.md)"
echo "2. Clear Laravel cache: cd backend && php artisan config:clear"
echo "3. Test the integration by requesting an OTP"
echo ""
echo "For detailed instructions, see: SUPABASE_EMAIL_MIGRATION_GUIDE.md"
