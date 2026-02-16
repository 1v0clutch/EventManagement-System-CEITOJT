# Quick Security Implementation Guide

This guide shows you how to implement the most critical security features step-by-step.

---

## Step 1: Add Rate Limiting (5 minutes)

### Update routes/api.php

```php
// Add rate limiting to authentication endpoints
Route::middleware('throttle:5,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
});

// Update protected routes with rate limiting
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    // All your protected routes here
});
```

### Test it:
```bash
# Run this command
test-security.bat

# Or manually:
# Try logging in 10 times rapidly - should get blocked after 5 attempts
```

---

## Step 2: Add Token Expiration (2 minutes)

### Update config/sanctum.php

```php
'expiration' => 60 * 24, // 24 hours (1440 minutes)
```

### Test it:
1. Login and save your token
2. For quick testing, change to `'expiration' => 1` (1 minute)
3. Wait 2 minutes
4. Try accessing dashboard - should redirect to login

---

## Step 3: Add Security Headers (5 minutes)

### Register the middleware in app/Http/Kernel.php

```php
protected $middleware = [
    // ... existing middleware
    \App\Http\Middleware\SecurityHeaders::class,
];
```

### Test it:
```bash
curl -I http://localhost:8000/api/events
```

Look for these headers in response:
- X-Content-Type-Options: nosniff
- X-Frame-Options: DENY
- X-XSS-Protection: 1; mode=block

---

## Step 4: Add Input Sanitization (10 minutes)

### Update EventController.php

Add this method:

```php
private function sanitizeInput($data)
{
    return [
        'title' => strip_tags($data['title']),
        'description' => strip_tags($data['description']),
        'location' => strip_tags($data['location']),
        'date' => $data['date'],
        'time' => $data['time'],
        'members' => $data['members'] ?? [],
    ];
}
```

Update store() and update() methods:

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'required|string|max:1000',
        'location' => 'required|string|max:255',
        'date' => 'required|date',
        'time' => 'required',
        'members' => 'array',
        'members.*' => 'exists:users,id',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    // Sanitize inputs
    $sanitized = $this->sanitizeInput($validated);
    
    // ... rest of your code using $sanitized instead of $validated
}
```

### Test it:
1. Create event with title: `<script>alert('XSS')</script>`
2. View the event
3. Should display as plain text, not execute script

---

## Step 5: Improve File Upload Security (10 minutes)

### Update EventController.php store() method

```php
if ($request->hasFile('image')) {
    $file = $request->file('image');
    
    // Validate MIME type
    $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
    if (!in_array($file->getMimeType(), $allowedMimes)) {
        return response()->json(['error' => 'Invalid file type'], 400);
    }
    
    // Check file size (2MB max)
    if ($file->getSize() > 2048 * 1024) {
        return response()->json(['error' => 'File too large'], 400);
    }
    
    // Generate random filename
    $filename = \Illuminate\Support\Str::random(40) . '.' . $file->getClientOriginalExtension();
    
    // Store securely
    $path = $file->storeAs('public/events', $filename);
    $sanitized['image'] = $filename;
}
```

### Test it:
1. Try uploading a `.php` file renamed to `.jpg`
2. Try uploading a 10MB file
3. Both should be rejected

---

## Step 6: Add Security Logging (10 minutes)

### Update AuthController.php

```php
use Illuminate\Support\Facades\Log;

public function login(Request $request)
{
    $request->validate([
        'email' => [
            'required',
            'email',
            'regex:/^main\.[a-zA-Z]+\.[a-zA-Z]+@cvsu\.edu\.ph$/i'
        ],
        'password' => 'required',
    ], [
        'email.regex' => 'Email must be in format main.(firstname).(lastname)@cvsu.edu.ph'
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        // Log failed attempt
        Log::warning('Failed login attempt', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now(),
        ]);
        
        throw ValidationException::withMessages([
            'email' => ['Invalid email or password'],
        ]);
    }

    // Log successful login
    Log::info('Successful login', [
        'user_id' => $user->id,
        'email' => $user->email,
        'ip' => $request->ip(),
        'timestamp' => now(),
    ]);

    $token = $user->createToken('auth-token')->plainTextToken;

    return response()->json([
        'message' => 'Logged in successfully',
        'user' => [
            'id' => $user->id,
            'username' => $user->name,
            'email' => $user->email,
            'department' => $user->department,
        ],
        'token' => $token,
    ]);
}
```

### Test it:
1. Try failed login
2. Check `backend/storage/logs/laravel.log`
3. Should see warning entry with IP and timestamp

---

## Step 7: Strengthen Password Requirements (5 minutes)

### Update AuthController.php register() method

```php
use Illuminate\Validation\Rules\Password;

public function register(Request $request)
{
    $request->validate([
        'username' => 'required|string|max:255',
        'email' => [
            'required',
            'string',
            'email',
            'unique:users',
            'regex:/^main\.[a-zA-Z]+\.[a-zA-Z]+@cvsu\.edu\.ph$/i'
        ],
        'password' => [
            'required',
            'confirmed',
            Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols()
        ],
        'department' => 'required|string',
    ], [
        'email.regex' => 'Email must be in format main.(firstname).(lastname)@cvsu.edu.ph',
        'password.min' => 'Password must be at least 8 characters',
    ]);
    
    // ... rest of code
}
```

### Update frontend Register.jsx

```jsx
<input
  type="password"
  name="password"
  value={formData.password}
  onChange={handleChange}
  required
  minLength={8}
  pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}"
  title="Password must contain at least 8 characters, including uppercase, lowercase, number and special character"
  className="..."
/>
<p className="text-xs text-gray-500 mt-1">
  Must include: uppercase, lowercase, number, and special character
</p>
```

### Test it:
Try registering with:
- `password` → Should fail
- `Password1` → Should fail (no special char)
- `Password1!` → Should pass

---

## Step 8: Configure CORS Properly (3 minutes)

### Update config/cors.php

```php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:5173',
        'http://localhost:3000',
        'http://127.0.0.1:5173',
        // Add your production domain here when deploying
        // 'https://yourdomain.com',
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

### Test it:
```bash
# Should work
curl -H "Origin: http://localhost:5173" -X OPTIONS http://localhost:8000/api/events

# Should be blocked
curl -H "Origin: http://evil.com" -X OPTIONS http://localhost:8000/api/events
```

---

## Step 9: Secure Environment Variables (2 minutes)

### Update .env

```env
APP_ENV=local  # Change to 'production' when deploying
APP_DEBUG=false  # CRITICAL: Must be false in production
APP_KEY=base64:...  # Keep this secret!

# Use strong database password
DB_PASSWORD=your_strong_password_here

# Add session security
SESSION_SECURE_COOKIE=true  # Only for HTTPS
SESSION_SAME_SITE=strict
```

### Verify .gitignore includes:

```
.env
.env.backup
.env.production
```

---

## Step 10: Run Security Tests

### Run automated tests:

```bash
# Run the security test script
test-security.bat

# Check logs
type backend\storage\logs\laravel.log
```

### Manual testing checklist:

- [ ] Try SQL injection in login
- [ ] Try XSS in event creation
- [ ] Test rate limiting (10 rapid logins)
- [ ] Test with invalid token
- [ ] Test file upload with .php file
- [ ] Check security headers with curl
- [ ] Verify logs are being written
- [ ] Test password requirements

---

## Verification Checklist

After implementing all steps:

- [ ] Rate limiting works (429 after 5 login attempts)
- [ ] Tokens expire after 24 hours
- [ ] Security headers present in responses
- [ ] XSS attempts are sanitized
- [ ] Malicious files rejected
- [ ] Failed logins are logged
- [ ] Strong passwords required
- [ ] CORS configured correctly
- [ ] .env not in git
- [ ] All tests pass

---

## Quick Test Commands

```bash
# Test rate limiting
for /L %i in (1,1,10) do curl -X POST http://localhost:8000/api/login -H "Content-Type: application/json" -d "{\"email\":\"test@test.com\",\"password\":\"wrong\"}"

# Test unauthorized access
curl http://localhost:8000/api/events

# Test invalid token
curl -H "Authorization: Bearer fake_token" http://localhost:8000/api/events

# Check security headers
curl -I http://localhost:8000/api/events

# View logs
type backend\storage\logs\laravel.log
```

---

## Time Estimate

- Step 1-3: 15 minutes
- Step 4-6: 30 minutes
- Step 7-9: 10 minutes
- Step 10: 15 minutes

**Total: ~70 minutes** to implement all critical security features

---

## Need Help?

- Check SECURITY_ENHANCEMENTS.md for detailed explanations
- Check PENTEST_GUIDE.md for comprehensive testing
- Laravel Security Docs: https://laravel.com/docs/security
- OWASP Top 10: https://owasp.org/www-project-top-ten/
