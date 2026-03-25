import { createContext, useContext, useState, useEffect, useCallback } from 'react';
import api from '../services/api';

const AuthContext = createContext(null);

const SESSION_TIMEOUT_MS = 60 * 60 * 1000; // 1 hour

// Read auth state synchronously from storage — no useEffect delay
const initAuthState = () => {
  // Check localStorage first (remember me)
  const persistedToken = localStorage.getItem('token');
  const persistedUser = localStorage.getItem('user');
  if (persistedToken && persistedUser) {
    try {
      return { user: JSON.parse(persistedUser), storage: 'local' };
    } catch {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
    }
  }

  // Check sessionStorage (no remember me — 1hr session)
  const sessionToken = sessionStorage.getItem('token');
  const sessionUser = sessionStorage.getItem('user');
  const sessionExpiry = sessionStorage.getItem('sessionExpiry');

  if (sessionToken && sessionUser && sessionExpiry) {
    if (Date.now() < parseInt(sessionExpiry, 10)) {
      try {
        return { user: JSON.parse(sessionUser), storage: 'session' };
      } catch {
        // fall through to null
      }
    }
    // Expired — clear it
    sessionStorage.removeItem('token');
    sessionStorage.removeItem('user');
    sessionStorage.removeItem('sessionExpiry');
  }

  return { user: null };
};

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(() => initAuthState().user);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    setLoading(false);
  }, []);

  const performLogout = useCallback(async () => {
    try {
      await api.post('/logout');
    } catch {
      // Silently fail — token may already be invalid
    } finally {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      localStorage.removeItem('rememberMe');
      sessionStorage.removeItem('token');
      sessionStorage.removeItem('user');
      sessionStorage.removeItem('sessionExpiry');
      setUser(null);
    }
  }, []);

  // Check session expiry every minute (only applies to non-remember-me sessions)
  useEffect(() => {
    const interval = setInterval(() => {
      const expiry = sessionStorage.getItem('sessionExpiry');
      if (expiry && Date.now() >= parseInt(expiry, 10)) {
        performLogout();
      }
    }, 60 * 1000);
    return () => clearInterval(interval);
  }, [performLogout]);

  const login = async (email, password, rememberMe = false, userFromOtp = null, tokenFromOtp = null) => {
    // If called from OTP verification, use provided user and token
    if (userFromOtp && tokenFromOtp) {
      // Default to session storage (no remember me context from OTP flow)
      sessionStorage.setItem('token', tokenFromOtp);
      sessionStorage.setItem('user', JSON.stringify(userFromOtp));
      sessionStorage.setItem('sessionExpiry', String(Date.now() + SESSION_TIMEOUT_MS));
      setUser(userFromOtp);
      return { user: userFromOtp, token: tokenFromOtp };
    }

    // Normal login flow
    const response = await api.post('/login', { email, password });

    if (response.data.requires_verification) return response.data;
    if (response.data.requires_otp) return response.data;

    const { user, token } = response.data;

    if (rememberMe) {
      // Persist across browser restarts — no expiry
      localStorage.setItem('token', token);
      localStorage.setItem('user', JSON.stringify(user));
      // Clear any leftover session storage
      sessionStorage.removeItem('token');
      sessionStorage.removeItem('user');
      sessionStorage.removeItem('sessionExpiry');
    } else {
      // Session only — expires in 1 hour
      sessionStorage.setItem('token', token);
      sessionStorage.setItem('user', JSON.stringify(user));
      sessionStorage.setItem('sessionExpiry', String(Date.now() + SESSION_TIMEOUT_MS));
      // Clear any leftover local storage
      localStorage.removeItem('token');
      localStorage.removeItem('user');
    }

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
    localStorage.setItem('user', JSON.stringify(updatedUser));
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
