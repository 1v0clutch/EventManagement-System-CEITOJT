<<<<<<< HEAD
import { createContext, useContext, useState, useEffect, useCallback } from 'react';
=======
import { createContext, useContext, useState, useEffect, useRef, useCallback } from 'react';
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
import api from '../services/api';

const AuthContext = createContext(null);

<<<<<<< HEAD
// Read auth state synchronously from storage — no useEffect delay
const initAuthState = () => {
  const token = localStorage.getItem('token');
  const savedUser = localStorage.getItem('user');

  if (!token || !savedUser) return { user: null };

=======
const SESSION_TIMEOUT_MS = 60 * 60 * 1000; // 1 hour in milliseconds
const LAST_ACTIVITY_KEY = 'lastActivity';

// Helper: determine which storage to use based on remember me flag
const getStorage = () => {
  return localStorage.getItem('rememberMe') === 'true' ? localStorage : sessionStorage;
};

// Read auth state synchronously from storage — no useEffect delay
const initAuthState = () => {
  const token = localStorage.getItem('token') || sessionStorage.getItem('token');
  const savedUser = localStorage.getItem('user') || sessionStorage.getItem('user');
  const rememberMe = localStorage.getItem('rememberMe') === 'true';

  if (!token || !savedUser) return { user: null };

  // For non-remembered sessions, check if 1hr has passed
  if (!rememberMe) {
    const lastActivity = parseInt(localStorage.getItem(LAST_ACTIVITY_KEY) || '0', 10);
    const elapsed = Date.now() - lastActivity;
    if (lastActivity && elapsed > SESSION_TIMEOUT_MS) {
      // Expired — clear storage
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      sessionStorage.removeItem('token');
      sessionStorage.removeItem('user');
      return { user: null };
    }
  }

>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
  try {
    return { user: JSON.parse(savedUser) };
  } catch {
    return { user: null };
  }
};

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(() => initAuthState().user);
<<<<<<< HEAD
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    setLoading(false);
  }, []);

  const performLogout = useCallback(async () => {
    try {
      await api.post('/logout');
    } catch {
=======
  const [loading] = useState(false); // No longer needed for initial auth
  const sessionTimerRef = useRef(null);

  const clearSessionTimer = () => {
    if (sessionTimerRef.current) {
      clearTimeout(sessionTimerRef.current);
      sessionTimerRef.current = null;
    }
  };

  const performLogout = useCallback(async () => {
    clearSessionTimer();
    try {
      await api.post('/logout');
    } catch (error) {
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
      // Silently fail — token may already be invalid
    } finally {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      localStorage.removeItem('rememberMe');
<<<<<<< HEAD
=======
      localStorage.removeItem(LAST_ACTIVITY_KEY);
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
      sessionStorage.removeItem('token');
      sessionStorage.removeItem('user');
      setUser(null);
    }
  }, []);

<<<<<<< HEAD
=======
  // Start a 1-hour inactivity timer for non-remembered sessions
  const startSessionTimer = useCallback(() => {
    if (localStorage.getItem('rememberMe') === 'true') return;

    clearSessionTimer();
    sessionTimerRef.current = setTimeout(() => {
      performLogout();
    }, SESSION_TIMEOUT_MS);
  }, [performLogout]);

  // Reset the timer on user activity
  const resetActivity = useCallback(() => {
    if (localStorage.getItem('rememberMe') === 'true') return;
    localStorage.setItem(LAST_ACTIVITY_KEY, Date.now().toString());
    startSessionTimer();
  }, [startSessionTimer]);

  useEffect(() => {
    // Start session timer if user is already logged in (non-remembered)
    if (user && localStorage.getItem('rememberMe') !== 'true') {
      startSessionTimer();
    }
  }, []);

  // Attach activity listeners for non-remembered sessions
  useEffect(() => {
    if (!user || localStorage.getItem('rememberMe') === 'true') return;

    const events = ['mousedown', 'keydown', 'scroll', 'touchstart'];
    events.forEach(e => window.addEventListener(e, resetActivity));
    startSessionTimer();

    return () => {
      events.forEach(e => window.removeEventListener(e, resetActivity));
      clearSessionTimer();
    };
  }, [user, resetActivity, startSessionTimer]);

>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
  const login = async (email, password, rememberMe = false, userFromOtp = null, tokenFromOtp = null) => {
    // If called from OTP verification, use provided user and token
    if (userFromOtp && tokenFromOtp) {
      localStorage.setItem('token', tokenFromOtp);
      localStorage.setItem('user', JSON.stringify(userFromOtp));
<<<<<<< HEAD
=======
      localStorage.setItem('rememberMe', 'true');
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
      setUser(userFromOtp);
      return { user: userFromOtp, token: tokenFromOtp };
    }

    // Normal login flow
    const response = await api.post('/login', { email, password });

    if (response.data.requires_verification) return response.data;
    if (response.data.requires_otp) return response.data;

    const { user, token } = response.data;

<<<<<<< HEAD
    // Always persist in localStorage so the session survives browser restarts
    localStorage.setItem('token', token);
    localStorage.setItem('user', JSON.stringify(user));
    localStorage.setItem('rememberMe', rememberMe ? 'true' : 'false');
=======
    if (rememberMe) {
      // Persist across browser sessions
      localStorage.setItem('token', token);
      localStorage.setItem('user', JSON.stringify(user));
      localStorage.setItem('rememberMe', 'true');
      localStorage.removeItem(LAST_ACTIVITY_KEY);
    } else {
      // Only lives for this browser session + 1hr inactivity limit
      sessionStorage.setItem('token', token);
      sessionStorage.setItem('user', JSON.stringify(user));
      localStorage.setItem('rememberMe', 'false');
      localStorage.setItem(LAST_ACTIVITY_KEY, Date.now().toString());
    }
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d

    setUser(user);
    return response.data;
  };

  const register = async (username, email, password, department) => {
    const response = await api.post('/register', { username, email, password, department });
    return response.data;
  };

  const logout = performLogout;

  const forgotPassword = async (email) => {
    const response = await api.post('/forgot-password', { email });
    return response.data;
  };

  const resetPassword = async (email, token, password, passwordConfirmation) => {
    const response = await api.post('/reset-password', {
      email,
      token,
      password,
      password_confirmation: passwordConfirmation,
    });
    return response.data;
  };

  const updateUser = (updatedUserData) => {
    const updatedUser = { ...user, ...updatedUserData };
<<<<<<< HEAD
    localStorage.setItem('user', JSON.stringify(updatedUser));
=======
    const storage = getStorage();
    storage.setItem('user', JSON.stringify(updatedUser));
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
    setUser(updatedUser);
  };

  return (
    <AuthContext.Provider value={{ user, login, register, logout, updateUser, forgotPassword, resetPassword, loading }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within AuthProvider');
  }
  return context;
};
