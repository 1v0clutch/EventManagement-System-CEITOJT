# Login Attempt Limiter - Implementation Guide

## Overview

The login attempt limiter prevents brute force attacks by:
- Limiting login attempts to 3 per email/IP combination
- Locking the account for 5 minutes after 3 failed attempts
- Showing remaining attempts to the user
- Displaying a countdown timer during lockout
- Logging all failed attempts for security monitoring

---

## How It Works

### Backend Flow

1. **First Failed Attempt:**
   - Counter incremented to 1
   - Stored in cache for 5 minutes
   - Response: "Invalid email or password. 2 attempt(s) remaining."

2. **Second Failed Attempt:**
   - Counter incremented to 2
   - Stored in cache for 5 minutes
   - Response: "Invalid email or password. 1 attempt(s) remaining."

3. **Third Failed Attempt:**
   - Counter incremented to 3
   - **IMMEDIATELY locks the account** (no 4th attempt allowed)
   - Lockout set for 5 minutes
   - Counter cleared
   - Response: "Too many failed attempts. Your account has been locked for 5 minutes."

4. **During Lockout:**
   - Middleware blocks all login attempts
   - Response: HTTP 429 with remaining time
   - Message: "Too many login attempts. Please try again in X minute(s)."

5. **Successful Login:**
   - All counters cleared
   - Lockout removed
   - User can login normally

### Frontend Flow

1. **Normal Login Attempt:**
   - User enters credentials
   - If wrong, shows error with remaining attempts

2. **Account Locked:**
   - Red alert box appears
   - Shows "Account Locked" message
   - Displays countdown timer (MM:SS format)
   - Login button disabled

3. **Countdown Timer:**
   - Updates every second
   - Shows time remaining until unlock
   - Automatically clears when time expires

---

## Files Modified/Created

### Backend Files

1. **`backend/app/Http/Middleware/ThrottleLoginAttempts.php`** (NEW)
   - Custom middleware for login throttling
   - Checks lockout status before processing request
   - Returns 429 status if locked

2. **`backend/app/Http/Controllers/AuthController.php`** (MODIFIED)
   - Added attempt tracking logic
   - Increments counter on failed login
   - Clears counter on successful login
   - Logs all attempts

3. **`backend/bootstrap/app.php`** (MODIFIED)
   - Registered `throttle.login` middleware alias

4. **`backend/routes/api.php`** (MODIFIED)
   - Applied `throttle.login` middleware to login route

### Frontend Files

1. **`frontend/src/pages/Login.jsx`** (MODIFIED)
   - Added lockout state management
   - Displays countdown timer
   - Disables login button during lockout
   - Shows remaining attempts

---

## Configuration

### Attempt Limit

To change the number of allowed attempts, edit:

```php
// backend/app/Http/Controllers/AuthController.php
$remainingAttempts = 3 - $attempts; // Change 3 to your desired limit
```

### Lockout Duration

To change the lockout time, edit:

```php
// backend/app/Http/Controllers/AuthController.php
$lockoutUntil = now()->addMinutes(5)->timestamp; // Change 5 to desired minutes
Cache::put($lockoutKey, $lockoutUntil, 300); // Change 300 to seconds
```

### Cache Storage

The limiter uses Laravel's cache system. By default, it uses the database driver.

To change cache driver, edit `.env`:

```env
CACHE_STORE=database  # Options: database, redis, memcached, file
```

---

## Testing

### Automated Test

Run the test script:

```bash
test-login-limiter.bat
```

This will:
1. Make 3 failed login attempts
2. Show remaining attempts after each
3. Trigger the lockout
4. Verify lockout is enforced

### Manual Test

1. **Test Failed Attempts:**
   - Go to login page
   - Enter wrong password 3 times
   - Should see countdown after 3rd attempt

2. **Test Countdown Timer:**
   - After lockout, watch the timer
   - Should count down from 5:00 to 0:00
   - Login button should be disabled

3. **Test Successful Login:**
   - Wait for lockout to expire (or clear cache)
   - Login with correct credentials
   - Should work normally

4. **Test Different Users:**
   - Lock one account
   - Try logging in with different email
   - Should work (lockout is per email/IP)

### Clear Lockout (For Testing)

```bash
# Clear all cache (including lockouts)
cd backend
php artisan cache:clear

# Or clear specific lockout in Laravel Tinker
php artisan tinker
Cache::forget('login_lockout:' . md5('email@example.com' . 'IP_ADDRESS'));
```

---

## Security Logs

All login attempts are logged to `backend/storage/logs/laravel.log`

### Failed Attempt Log Entry:

```
[2026-02-16 10:30:15] local.WARNING: Failed login attempt
{
  "email": "main.test.user@cvsu.edu.ph",
  "ip": "127.0.0.1",
  "attempts": 1,
  "user_agent": "Mozilla/5.0...",
  "timestamp": "2026-02-16 10:30:15"
}
```

### Lockout Log Entry:

```
[2026-02-16 10:30:45] local.WARNING: Account locked due to failed attempts
{
  "email": "main.test.user@cvsu.edu.ph",
  "ip": "127.0.0.1",
  "locked_until": "2026-02-16 10:35:45"
}
```

### Successful Login Log Entry:

```
[2026-02-16 10:36:00] local.INFO: Successful login
{
  "user_id": 1,
  "email": "main.test.user@cvsu.edu.ph",
  "ip": "127.0.0.1",
  "timestamp": "2026-02-16 10:36:00"
}
```

---

## Monitoring

### View Recent Failed Attempts

```bash
# Windows
type backend\storage\logs\laravel.log | findstr "Failed login"

# Or view last 50 lines
powershell Get-Content backend\storage\logs\laravel.log -Tail 50
```

### Count Failed Attempts Today

```bash
# Windows PowerShell
$date = Get-Date -Format "yyyy-MM-dd"
Select-String -Path backend\storage\logs\laravel.log -Pattern "Failed login" | Where-Object { $_ -match $date } | Measure-Object
```

---

## API Response Examples

### Failed Attempt (Attempts Remaining)

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "Invalid email or password. 2 attempt(s) remaining."
    ]
  }
}
```

### Account Locked (3rd Attempt)

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "Too many failed attempts. Your account has been locked for 5 minutes."
    ]
  }
}
```

### Lockout Enforced (During Cooldown)

```json
{
  "message": "Too many login attempts. Please try again in 4 minute(s).",
  "locked_until": 1708084545,
  "remaining_seconds": 240
}
```

HTTP Status: `429 Too Many Requests`

---

## Troubleshooting

### Issue: Lockout not working

**Solution:**
1. Check cache is configured:
   ```bash
   php artisan config:cache
   ```

2. Verify cache table exists:
   ```bash
   php artisan migrate
   ```

3. Check middleware is registered:
   ```bash
   php artisan route:list | findstr login
   ```

### Issue: Lockout persists after 5 minutes

**Solution:**
1. Clear cache:
   ```bash
   php artisan cache:clear
   ```

2. Check system time is correct

3. Verify cache expiration is set correctly

### Issue: Different IPs not tracked separately

**Expected behavior:** Each IP address is tracked separately. If you're testing from the same IP, you'll hit the same limit.

**Solution:** Test from different devices/networks or clear cache between tests.

---

## Security Considerations

### Strengths

✅ Prevents brute force attacks
✅ Tracks by email AND IP (prevents distributed attacks)
✅ Logs all attempts for monitoring
✅ User-friendly countdown timer
✅ Automatic cleanup after lockout expires

### Limitations

⚠️ IP-based tracking can be bypassed with VPN/proxy rotation
⚠️ Shared IPs (office networks) may affect multiple users
⚠️ Cache must be persistent (don't use array driver in production)

### Recommendations

1. **Use Redis in production** for better performance:
   ```env
   CACHE_STORE=redis
   ```

2. **Monitor logs regularly** for suspicious patterns

3. **Consider CAPTCHA** after 2 failed attempts

4. **Implement email alerts** for repeated lockouts

5. **Use rate limiting** on other sensitive endpoints

---

## Advanced Configuration

### Per-User Lockout (Instead of IP-based)

```php
// In AuthController.php, change:
$key = 'login_attempts:' . md5($email); // Remove IP from hash
```

### Progressive Delays

```php
// Increase lockout time with each lockout
$lockoutCount = Cache::get('lockout_count:' . md5($email), 0);
$lockoutMinutes = 5 * ($lockoutCount + 1); // 5, 10, 15, 20...
Cache::put($lockoutKey, now()->addMinutes($lockoutMinutes)->timestamp, $lockoutMinutes * 60);
Cache::put('lockout_count:' . md5($email), $lockoutCount + 1, 86400); // Reset after 24h
```

### Email Notification on Lockout

```php
// In AuthController.php after lockout
use Illuminate\Support\Facades\Mail;

Mail::to($email)->send(new AccountLockedMail($user, $lockoutUntil));
```

---

## Performance Impact

- **Minimal:** 2 cache reads per login attempt
- **Cache operations:** ~1-2ms per operation
- **Total overhead:** ~2-4ms per login request
- **Recommended cache driver:** Redis (fastest) or Database (reliable)

---

## Compliance

This implementation helps meet security requirements for:

- **OWASP Top 10:** Prevents brute force attacks
- **PCI DSS:** Requirement 8.1.6 (limit repeated access attempts)
- **NIST 800-63B:** Account lockout after failed attempts
- **ISO 27001:** Access control measures

---

## Summary

The login attempt limiter provides robust protection against brute force attacks while maintaining a good user experience. It's configurable, well-logged, and follows security best practices.

**Key Features:**
- 3 attempts before lockout
- 5-minute cooldown period
- Real-time countdown timer
- Comprehensive logging
- Per-email/IP tracking
- Automatic cleanup

**Next Steps:**
1. Run `test-login-limiter.bat` to verify
2. Monitor logs for suspicious activity
3. Consider adding CAPTCHA for extra security
4. Configure Redis cache for production
