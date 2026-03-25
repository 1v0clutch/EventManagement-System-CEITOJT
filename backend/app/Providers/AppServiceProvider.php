<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
<<<<<<< HEAD
use Illuminate\Support\Facades\DB;
=======
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
<<<<<<< HEAD
    //
=======
        //
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Email delivery handled by Brevo SMTP (configured in .env)
<<<<<<< HEAD

=======
        
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
        // Register User observer for bootstrap admin cleanup
        User::observe(UserObserver::class);
    }
}
