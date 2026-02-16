# Security Enhancements Guide

## Current Security Status ✅

Your application already has:
- Laravel Sanctum authentication
- Password hashing (bcrypt)
- CSRF protection
- Email validation
- Parameterized queries (SQL injection protection)
- Authorization checks

---

## Recommended Security Improvements

### 1. RATE LIMITING (High Priority)

**Why:** Prevents brute force attacks, API abuse, and DDoS

**Implementation:**

```php
// backend/app/Http/Kernel.php
protected $middlewareGroups = [
    'api' => [
        \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
        // ... other middleware
    ],
];
```

```php
// backend/routes/api.php
// Add rate limiting to sensitive endpoints
Route::middleware('throttle:5,1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
});

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    // Protected routes with 60 requests per minute
});
```

**Testing:**
```bash
# Test rate limiting with curl
for i in {1..10}; do
  curl -X POST http://localhost:8000/api/login \
    -H "Content-Type: application/json" \
    -d '{"email":"test@test.com","password":"wrong"}'
  echo "Request $i"
done
```

Expected: After 5 requests, should return `429 Too Many Requests`

---

### 2. TOKEN EXPIRATION (High Priority)

**Why:** Limits damage if tokens are stolen

**Implementation:**

```php
// backend/config/sanctum.php
'expiration' => 60 * 24, // 24 hours in minutes
```

**Testing:**
1. Login and save token
2. Change system time forward 25 hours (or modify expiration to 1 minute for testing)
3. Try accessing protected endpoint
4. Should return 401 Unauthorized

---

### 3. INPUT SANITIZATION & XSS PROTECTION (High Priority)

**Why:** Prevents XSS attacks

**Implementation:**

```php
// backend/app/Http/Controllers/EventController.php
use Illuminate\Support\Str;

public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'required|string|max:1000',
        // ... other fields
    ]);

    // Sanitize inputs
    $validated['title'] = strip_tags($validated['title']);
    $validated['description'] = strip_tags($validated['description']);
    
    // Or use htmlspecialchars
    $validated['title'] = htmlspecialchars($validated['title'], ENT_QUOTES, 'UTF-8');
    
    // ... create event
}
```

**Frontend Protection:**

```jsx
// frontend/src/utils/sanitize.js
export const sanitizeHTML = (str) => {
  const temp = document.createElement('div');
  temp.textContent = str;
  return temp.innerHTML;
};

// Use in components
<h3>{sanitizeHTML(event.title)}</h3>
```

**Testing:**
1. Create event with title: `<script>alert('XSS')</script>`
2. Create event with title: `<img src=x onerror=alert('XSS')>`
3. View event - script should NOT execute

---

### 4. FILE UPLOAD SECURITY (High Priority)

**Why:** Prevents malicious file uploads

**Implementation:**

```php
// backend/app/Http/Controllers/EventController.php
public function store(Request $request)
{
    $request->validate([
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
    ]);

    if ($request->hasFile('image')) {
        $file = $request->file('image');
        
        // Validate MIME type (not just extension)
        $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            return response()->json(['error' => 'Invalid file type'], 400);
        }
        
        // Generate random filename to prevent enumeration
        $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
        
        // Store in designated folder only
        $path = $file->storeAs('public/events', $filename);
    }
}
```

**Testing:**
1. Try uploading `shell.php.jpg`
2. Try uploading `.exe` file
3. Try uploading 10MB file
4. All should be rejected

---

### 5. CORS CONFIGURATION (Medium Priority)

**Why:** Prevents unauthorized domains from accessing your API

**Implementation:**

```php
// backend/config/cors.php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:5173',
        'http://localhost:3000',
        // Add production domain when deployed
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

**Testing:**
```bash
# Should work
curl -H "Origin: http://localhost:5173" \
  -H "Access-Control-Request-Method: GET" \
  -X OPTIONS http://localhost:8000/api/events

# Should be blocked
curl -H "Origin: http://evil.com" \
  -H "Access-Control-Request-Method: GET" \
  -X OPTIONS http://localhost:8000/api/events
```

---

### 6. PASSWORD STRENGTH REQUIREMENTS (Medium Priority)

**Why:** Prevents weak passwords

**Implementation:**

```php
// backend/app/Http/Controllers/AuthController.php
use Illuminate\Validation\Rules\Password;

public function register(Request $request)
{
    $request->validate([
        'password' => [
            'required',
            'confirmed',
            Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised()
        ],
    ]);
}
```

**Testing:**
Try registering with:
- `password` - Should fail (too simple)
- `Password1` - Should fail (no symbol)
- `Password1!` - Should pass

---

### 7. SECURITY HEADERS (Medium Priority)

**Why:** Adds multiple layers of browser-side protection

**Implementation:**

```php
// backend/app/Http/Middleware/SecurityHeaders.php
<?php

namespace App\Http\Middleware;

use Closure;

class SecurityHeaders
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        
        return $response;
    }
}
```

```php
// backend/app/Http/Kernel.php
protected $middleware = [
    // ... other middleware
    \App\Http\Middleware\SecurityHeaders::class,
];
```

**Testing:**
```bash
curl -I http://localhost:8000/api/events
# Check response headers
```

---

### 8. LOGGING & MONITORING (Medium Priority)

**Why:** Detect and respond to security incidents

**Implementation:**

```php
// backend/app/Http/Controllers/AuthController.php
use Illuminate\Support\Facades\Log;

public function login(Request $request)
{
    // ... validation
    
    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        // Log failed login attempt
        Log::warning('Failed login attempt', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        
        throw ValidationException::withMessages([
            'email' => ['Invalid email or password'],
        ]);
    }
    
    // Log successful login
    Log::info('Successful login', [
        'user_id' => $user->id,
        'ip' => $request->ip(),
    ]);
    
    // ... return token
}
```

**Testing:**
1. Try failed login
2. Check `backend/storage/logs/laravel.log`
3. Should see warning entry

---

### 9. ENVIRONMENT VARIABLE SECURITY (High Priority)

**Why:** Prevents exposure of sensitive data

**Implementation:**

```env
# backend/.env
APP_ENV=production
APP_DEBUG=false  # CRITICAL: Set to false in production
APP_KEY=base64:... # Keep secret

# Use strong database password
DB_PASSWORD=your_strong_password_here

# Add to .gitignore
.env
.env.backup
```

**Testing:**
1. Set `APP_DEBUG=true`
2. Trigger an error
3. Should see detailed error page (dangerous in production)
4. Set `APP_DEBUG=false`
5. Should see generic error page

---

### 10. HTTPS ENFORCEMENT (Critical for Production)

**Why:** Encrypts data in transit

**Implementation:**

```php
// backend/app/Providers/AppServiceProvider.php
use Illuminate\Support\Facades\URL;

public function boot()
{
    if ($this->app->environment('production')) {
        URL::forceScheme('https');
    }
}
```

**Testing:**
- In production, try accessing `http://` URL
- Should redirect to `https://`

---

## Security Testing Checklist

### Automated Testing Tools

1. **OWASP ZAP** (Free)
   ```bash
   # Download from https://www.zaproxy.org/
   # Run automated scan
   zap-cli quick-scan http://localhost:5173
   ```

2. **Nikto** (Free)
   ```bash
   nikto -h http://localhost:8000
   ```

3. **SQLMap** (SQL Injection)
   ```bash
   sqlmap -u "http://localhost:8000/api/login" --data="email=test&password=test"
   ```

### Manual Testing Checklist

- [ ] Try SQL injection in all input fields
- [ ] Try XSS in all text inputs
- [ ] Test authentication bypass
- [ ] Test authorization (access other users' data)
- [ ] Test file upload with malicious files
- [ ] Test rate limiting on login
- [ ] Test token expiration
- [ ] Test CORS with different origins
- [ ] Check for exposed sensitive data in responses
- [ ] Test password reset flow
- [ ] Check error messages (shouldn't reveal system info)
- [ ] Test with expired/invalid tokens

---

## Security Audit Report Template

```markdown
# Security Audit Report
Date: [Date]
Tester: [Name]

## Summary
- Total Tests: X
- Vulnerabilities Found: Y
- Critical: 0
- High: 0
- Medium: 0
- Low: 0

## Vulnerabilities

### 1. [Vulnerability Name]
**Severity:** Critical/High/Medium/Low
**Description:** [What is the issue]
**Impact:** [What can an attacker do]
**Steps to Reproduce:**
1. Step 1
2. Step 2

**Recommendation:** [How to fix]
**Status:** Open/Fixed

## Conclusion
[Overall security posture]
```

---

## Quick Security Test Script

Create this file to run basic security tests:

```bash
# test-security.sh
#!/bin/bash

echo "=== Security Testing ==="

echo "\n1. Testing Rate Limiting..."
for i in {1..10}; do
  curl -s -o /dev/null -w "%{http_code}\n" \
    -X POST http://localhost:8000/api/login \
    -H "Content-Type: application/json" \
    -d '{"email":"test@test.com","password":"wrong"}'
done

echo "\n2. Testing SQL Injection..."
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin'\'' OR '\''1'\''='\''1","password":"test"}'

echo "\n3. Testing Unauthorized Access..."
curl -s -o /dev/null -w "%{http_code}\n" \
  http://localhost:8000/api/events

echo "\n4. Testing Invalid Token..."
curl -s -o /dev/null -w "%{http_code}\n" \
  -H "Authorization: Bearer invalid_token_here" \
  http://localhost:8000/api/events

echo "\nTests complete!"
```

---

## Priority Implementation Order

1. **Week 1 (Critical):**
   - Rate limiting on auth endpoints
   - Token expiration
   - Input sanitization
   - File upload validation

2. **Week 2 (High):**
   - Security headers middleware
   - Password strength requirements
   - Logging failed attempts
   - CORS configuration

3. **Week 3 (Medium):**
   - Comprehensive testing
   - Security audit
   - Documentation
   - Team training

---

## Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Laravel Security Best Practices](https://laravel.com/docs/security)
- [Web Security Academy](https://portswigger.net/web-security)
- [OWASP ZAP Tutorial](https://www.zaproxy.org/getting-started/)

---

## Notes

- Always test in development environment first
- Never commit `.env` file
- Use strong passwords in production
- Keep Laravel and dependencies updated
- Regular security audits (monthly)
- Monitor logs for suspicious activity
