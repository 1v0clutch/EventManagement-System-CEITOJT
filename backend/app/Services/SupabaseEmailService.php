<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupabaseEmailService
{
    protected $supabaseUrl;
    protected $supabaseKey;
    protected $fromEmail;
    protected $fromName;

    public function __construct()
    {
        $this->supabaseUrl = config('services.supabase.url');
        $this->supabaseKey = config('services.supabase.key');
        $this->fromEmail = config('mail.from.address');
        $this->fromName = config('mail.from.name');
    }

    /**
     * Send OTP email using Supabase Auth
     */
    public function sendOtpEmail(string $email, string $otp, string $userName): bool
    {
        try {
            // Use Supabase's built-in email template system
            $response = Http::withHeaders([
                'apikey' => $this->supabaseKey,
                'Authorization' => 'Bearer ' . $this->supabaseKey,
                'Content-Type' => 'application/json',
            ])->post($this->supabaseUrl . '/auth/v1/otp', [
                'email' => $email,
                'create_user' => false,
                'data' => [
                    'otp_code' => $otp,
                    'user_name' => $userName,
                ],
            ]);

            if ($response->successful()) {
                Log::info('Supabase OTP email sent successfully', [
                    'email' => $email,
                    'timestamp' => now(),
                ]);
                return true;
            }

            Log::error('Supabase OTP email failed', [
                'email' => $email,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Supabase email service exception', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send custom email using Supabase Edge Functions
     * This requires setting up an Edge Function in your Supabase project
     */
    public function sendCustomEmail(string $to, string $subject, string $htmlContent, string $textContent = ''): bool
    {
        try {
            $edgeFunctionUrl = $this->supabaseUrl . '/functions/v1/send-email';

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->supabaseKey,
                'Content-Type' => 'application/json',
            ])->post($edgeFunctionUrl, [
                'to' => $to,
                'from' => $this->fromEmail,
                'from_name' => $this->fromName,
                'subject' => $subject,
                'html' => $htmlContent,
                'text' => $textContent ?: strip_tags($htmlContent),
            ]);

            if ($response->successful()) {
                Log::info('Supabase custom email sent successfully', [
                    'to' => $to,
                    'subject' => $subject,
                    'timestamp' => now(),
                ]);
                return true;
            }

            Log::error('Supabase custom email failed', [
                'to' => $to,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Supabase custom email exception', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send password reset OTP email with custom HTML template
     */
    public function sendPasswordResetOtp(string $email, string $otp, string $userName): bool
    {
        $subject = '🔐 Your Password Reset OTP Code';
        
        $htmlContent = $this->buildOtpEmailHtml($userName, $otp);
        $textContent = $this->buildOtpEmailText($userName, $otp);

        return $this->sendCustomEmail($email, $subject, $htmlContent, $textContent);
    }

    /**
     * Send password reset confirmation email
     */
    public function sendPasswordResetConfirmation(string $email, string $userName): bool
    {
        $subject = '✅ Password Reset Successful';
        
        $htmlContent = $this->buildConfirmationEmailHtml($userName);
        $textContent = $this->buildConfirmationEmailText($userName);

        return $this->sendCustomEmail($email, $subject, $htmlContent, $textContent);
    }

    /**
     * Build HTML email template for OTP
     */
    protected function buildOtpEmailHtml(string $userName, string $otp): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #4F46E5; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background-color: #f9fafb; padding: 30px; border-radius: 0 0 8px 8px; }
        .otp-code { background-color: #fff; border: 2px dashed #4F46E5; padding: 20px; text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 8px; margin: 20px 0; border-radius: 8px; }
        .warning { background-color: #FEF3C7; border-left: 4px solid #F59E0B; padding: 12px; margin: 20px 0; border-radius: 4px; }
        .footer { text-align: center; margin-top: 20px; color: #6B7280; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Password Reset Request</h1>
        </div>
        <div class="content">
            <p>Hello <strong>{$userName}</strong>,</p>
            <p>You requested to reset your password for your Event Management System account.</p>
            <p>Use the OTP code below to proceed with your password reset:</p>
            
            <div class="otp-code">{$otp}</div>
            
            <div class="warning">
                <p style="margin: 0;"><strong>⏱️ This code will expire in 10 minutes.</strong></p>
                <p style="margin: 8px 0 0 0;">🔒 Never share this code with anyone.</p>
            </div>
            
            <p>If you did not request a password reset, please ignore this email and your password will remain unchanged.</p>
            
            <div class="footer">
                <p>Best regards,<br><strong>Event Management System Team</strong></p>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Build plain text email for OTP
     */
    protected function buildOtpEmailText(string $userName, string $otp): string
    {
        return <<<TEXT
Hello {$userName},

You requested to reset your password for your Event Management System account.

Use the OTP code below to proceed with your password reset:

{$otp}

⏱️ This code will expire in 10 minutes.
🔒 Never share this code with anyone.

If you did not request a password reset, please ignore this email and your password will remain unchanged.

Best regards,
Event Management System Team
TEXT;
    }

    /**
     * Build HTML email template for confirmation
     */
    protected function buildConfirmationEmailHtml(string $userName): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #10B981; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background-color: #f9fafb; padding: 30px; border-radius: 0 0 8px 8px; }
        .success-icon { font-size: 48px; text-align: center; margin: 20px 0; }
        .footer { text-align: center; margin-top: 20px; color: #6B7280; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ Password Reset Successful</h1>
        </div>
        <div class="content">
            <div class="success-icon">🎉</div>
            <p>Hello <strong>{$userName}</strong>,</p>
            <p>Your password has been successfully reset.</p>
            <p>You can now log in to your Event Management System account using your new password.</p>
            <p>If you did not make this change, please contact support immediately.</p>
            
            <div class="footer">
                <p>Best regards,<br><strong>Event Management System Team</strong></p>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Build plain text email for confirmation
     */
    protected function buildConfirmationEmailText(string $userName): string
    {
        return <<<TEXT
Hello {$userName},

Your password has been successfully reset.

You can now log in to your Event Management System account using your new password.

If you did not make this change, please contact support immediately.

Best regards,
Event Management System Team
TEXT;
    }
}
