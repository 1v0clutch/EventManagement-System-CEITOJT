# Per-Email Lockout Testing Guide

## Feature Overview

The login lockout is now **per-email address**, meaning:
- Each email has its own attempt counter
- Each email has its own lockout timer
- Locking one account doesn't affect others
- You can switch emails and try different accounts

---

## Quick Test (3 minutes)

### Test 1: Lock One Account

1. **Go to login page**
   ```
   http://localhost:5173
   ```

2. **Enter first email:**
   ```
   Email: main.user1.test@cvsu.edu.ph
   Password: wrongpassword
   ```

3. **Make 3 failed attempts:**
   - Attempt 1: "2 attempts remaining" ✅
   - Attempt 2: "1 attempt remaining" ✅
   - Attempt 3: Account locked, button disabled ✅

4. **Verify lockout:**
   - Button shows "Account Locked" ✅
   - Countdown timer appears ✅
   - Button is disabled ✅

### Test 2: Try Different Account

5. **Change email to different account:**
   ```
   Email: main.user2.test@cvsu.edu.ph
   Password: wrongpassword
   ```

6. **Verify independent tracking:**
   - Lockout message disappears ✅
   - Button becomes enabled ✅
   - Can attempt login ✅
   - Progress bar resets to 0/3 ✅

7. **Make attempts with second account:**
   - Attempt 1: "2 attempts remaining" ✅
   - Attempt 2: "1 attempt remaining" ✅
   - Each account tracked separately ✅

### Test 3: Switch Back to Locked Account

8. **Change email back to first account:**
   ```
   Email: main.user1.test@cvsu.edu.ph
   ```

9. **Verify lockout persists:**
   - Lockout message reappears ✅
   - Countdown continues from where it left off ✅
   - Button disabled again ✅
   - First account still locked ✅

### Test 4: Refresh Page

10. **Refresh the page (F5)**

11. **Enter locked email:**
    ```
    Email: main.user1.test@cvsu.edu.ph
    ```

12. **Verify persistence:**
    - Lockout still active ✅
    - Countdown continues ✅
    - Button disabled ✅

13. **Change to unlocked email:**
    ```
    Email: main.user2.test@cvsu.edu.ph
    ```

14. **Verify unlocked:**
    - Can attempt login ✅
    - Button enabled ✅

---

## Detailed Test Scenarios

### Scenario 1: Multiple Locked Accounts

1. Lock account 1 (3 failed attempts)
2. Switch to account 2
3. Lock account 2 (3 failed attempts)
4. Switch to account 3
5. Should be able to attempt login ✅
6. Switch back to account 1
7. Should still be locked ✅
8. Switch to account 2
9. Should still be locked ✅

**Result:** Each account maintains its own lockout state.

---

### Scenario 2: Partial Attempts

1. Enter email: user1@cvsu.edu.ph
2. Make 2 failed attempts (1/3, 2/3)
3. Switch to email: user2@cvsu.edu.ph
4. Make 1 failed attempt (1/3)
5. Switch back to: user1@cvsu.edu.ph
6. Progress bar should show 2/3 ✅
7. Make 1 more attempt → Locked ✅

**Result:** Attempt counters are preserved per email.

---

### Scenario 3: Successful Login Clears Attempts

1. Enter email: user1@cvsu.edu.ph
2. Make 2 failed attempts (2/3)
3. Enter correct password
4. Login successful ✅
5. Logout
6. Try wrong password again
7. Should start from 1/3 (counter reset) ✅

**Result:** Successful login clears that email's attempts.

---

### Scenario 4: Lockout Expiration

1. Lock account 1 (3 failed attempts)
2. Wait 5 minutes (or clear cache)
3. Try account 1 again
4. Should be unlocked ✅
5. Can attempt login ✅

**Result:** Lockout expires after 5 minutes.

---

## localStorage Inspection

### View All Lockouts

Open browser console (F12) and run:

```javascript
// View all login-related localStorage items
Object.keys(localStorage).forEach(key => {
  if (key.startsWith('loginLockout_') || key.startsWith('loginAttempts_')) {
    console.log(key, localStorage.getItem(key));
  }
});
```

### Expected Output

```
loginLockout_main.user1.test@cvsu.edu.ph {"lockedUntil":1708084545,"email":"main.user1.test@cvsu.edu.ph"}
loginAttempts_main.user1.test@cvsu.edu.ph 3
loginAttempts_main.user2.test@cvsu.edu.ph 2
```

---

## Visual Verification

### Locked Account
```
Email: main.user1.test@cvsu.edu.ph
Password: ********

🔒 Account Locked
This account is locked due to too many failed attempts.
Time remaining: 4:32

[🔒 Account Locked] ← Disabled, grayed out
```

### Unlocked Account (Different Email)
```
Email: main.user2.test@cvsu.edu.ph
Password: ********

[Sign in] ← Enabled, blue button
```

### Switching Between Accounts
```
Locked email → Disabled button + countdown
↓ Change email
Unlocked email → Enabled button + no countdown
↓ Change back
Locked email → Disabled button + countdown (continues)
```

---

## Edge Cases to Test

### Edge Case 1: Same Email, Different Case
```
user1@cvsu.edu.ph vs USER1@cvsu.edu.ph
```
Should be treated as different (case-sensitive) ✅

### Edge Case 2: Whitespace
```
"user1@cvsu.edu.ph" vs " user1@cvsu.edu.ph "
```
Should be treated as different ✅

### Edge Case 3: Empty Email
```
Email: (empty)
```
Should not check lockout, button enabled ✅

### Edge Case 4: Rapid Email Changes
1. Enter email1
2. Immediately change to email2
3. Immediately change to email3
4. Should handle gracefully ✅

---

## Clear Lockouts for Testing

### Clear Specific Email
```javascript
localStorage.removeItem('loginLockout_main.user1.test@cvsu.edu.ph');
localStorage.removeItem('loginAttempts_main.user1.test@cvsu.edu.ph');
location.reload();
```

### Clear All Lockouts
```javascript
Object.keys(localStorage).forEach(key => {
  if (key.startsWith('loginLockout_') || key.startsWith('loginAttempts_')) {
    localStorage.removeItem(key);
  }
});
location.reload();
```

### Clear Backend Cache
```bash
cd backend
php artisan cache:clear
```

---

## Expected Behavior Summary

| Action | Account 1 | Account 2 | Account 3 |
|--------|-----------|-----------|-----------|
| Lock Account 1 | 🔒 Locked | ✅ Unlocked | ✅ Unlocked |
| Lock Account 2 | 🔒 Locked | 🔒 Locked | ✅ Unlocked |
| Wait 5 min | ✅ Unlocked | 🔒 Locked | ✅ Unlocked |
| Successful login A1 | ✅ Unlocked | 🔒 Locked | ✅ Unlocked |

---

## Verification Checklist

- [ ] Each email has independent attempt counter
- [ ] Each email has independent lockout timer
- [ ] Locking one account doesn't affect others
- [ ] Switching emails updates lockout status
- [ ] Lockout persists across page refreshes
- [ ] Lockout is email-specific, not global
- [ ] Progress bar resets when changing email
- [ ] Countdown continues when switching back
- [ ] localStorage uses email-specific keys
- [ ] Successful login clears that email's attempts
- [ ] Can have multiple accounts locked simultaneously
- [ ] Each lockout expires independently

---

## Common Issues

### Issue: All accounts locked
**Cause:** Using old global lockout key
**Fix:** Clear localStorage and refresh

### Issue: Lockout not persisting
**Cause:** Email not matching exactly
**Fix:** Check email spelling and case

### Issue: Counter not resetting
**Cause:** localStorage not cleared on success
**Fix:** Check successful login clears attempts

---

## Summary

The lockout system now works per-email:
- ✅ Each email tracked independently
- ✅ Can lock multiple accounts
- ✅ Switching emails updates status
- ✅ Lockouts persist across refreshes
- ✅ Each account has own countdown
- ✅ No global lockout affecting all users

You can now lock one account and still attempt to login with other accounts!
