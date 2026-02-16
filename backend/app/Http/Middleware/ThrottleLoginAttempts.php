<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottleLoginAttempts
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $email = $request->input('email');
        $ip = $request->ip();
        
        // Create unique key for this email/IP combination
        $key = 'login_attempts:' . md5($email . $ip);
        $lockoutKey = 'login_lockout:' . md5($email . $ip);
        
        // Check if user is currently locked out
        if (Cache::has($lockoutKey)) {
            $remainingTime = Cache::get($lockoutKey) - now()->timestamp;
            $minutes = ceil($remainingTime / 60);
            
            return response()->json([
                'message' => "Too many login attempts. Please try again in {$minutes} minute(s).",
                'locked_until' => Cache::get($lockoutKey),
                'remaining_seconds' => $remainingTime,
            ], 429);
        }
        
        // Check current attempts
        $attempts = Cache::get($key, 0);
        
        if ($attempts >= 3) {
            // Lock out for 5 minutes (300 seconds)
            $lockoutUntil = now()->addMinutes(5)->timestamp;
            Cache::put($lockoutKey, $lockoutUntil, 300);
            Cache::forget($key); // Reset attempts counter
            
            return response()->json([
                'message' => 'Too many login attempts. Your account has been locked for 5 minutes.',
                'locked_until' => $lockoutUntil,
                'remaining_seconds' => 300,
            ], 429);
        }
        
        return $next($request);
    }
}
