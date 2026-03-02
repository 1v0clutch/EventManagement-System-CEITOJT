<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BrevoMailService;

class TestEmail extends Command
{
    protected $signature = 'email:test {email}';
    protected $description = 'Send a test email to verify email configuration';

    public function handle(BrevoMailService $mailService)
    {
        $email = $this->argument('email');
        
        $this->info("Sending test email to: {$email}");
        
        $result = $mailService->sendRegistrationOtp(
            $email,
            '123456',
            'Test User'
        );
        
        if ($result) {
            $this->info('✅ Email sent successfully!');
            $this->info('Check your inbox (and spam folder)');
            $this->info('Also check Laravel logs: storage/logs/laravel.log');
        } else {
            $this->error('❌ Failed to send email');
            $this->error('Check Laravel logs for details: storage/logs/laravel.log');
        }
        
        return $result ? 0 : 1;
    }
}
