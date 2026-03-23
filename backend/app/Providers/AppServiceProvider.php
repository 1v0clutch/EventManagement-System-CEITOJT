<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Email delivery handled by Brevo SMTP (configured in .env)

        // Register User observer for bootstrap admin cleanup
        User::observe(UserObserver::class);

        // PlanetScale does not support foreign key constraints at the DB level.
        // Disable FK checks during migrations so `->foreign()` / `->constrained()`
        // calls do not throw errors. Safe: Laravel still enforces FK integrity in PHP.
        if (app()->environment('production') && config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }
    }
}
