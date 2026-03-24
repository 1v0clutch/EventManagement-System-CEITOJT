import { createContext, useContext, useState, useEffect, useCallback } from 'react';
import api from '../services/api';

const AuthContext = createContext(null);

// Read auth state synchronously from storage — no useEffect delay
const initAuthState = () => {
  const token = localStorage.getItem('token');
  const savedUser = localStorage.getItem('user');

  if (!token || !savedUser) return { user: null };

  try {
    return { user: JSON.parse(savedUser) };
  } catch {
    return { user: null };
  }
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
      setUser(null);
    }
  }, []);

  const login = async (email, password, rememberMe = false, userFromOtp = null, tokenFromOtp = null) => {
    // If called from OTP verification, use provided user and token
    if (userFromOtp && tokenFromOtp) {
      localStorage.setItem('token', tokenFromOtp);
      localStorage.setItem('user', JSON.stringify(userFromOtp));
      setUser(userFromOtp);
      return { user: userFromOtp, token: tokenFromOtp };
    }

    // Normal login flow
    const response = await api.post('/login', { email, password });

    if (response.data.requires_verification) return response.data;
    if (response.data.requires_otp) return response.data;

    const { user, token } = response.data;

    // Always persist in localStorage so the session survives browser restarts
    localStorage.setItem('token', token);
    localStorage.setItem('user', JSON.stringify(user));
    localStorage.setItem('rememberMe', rememberMe ? 'true' : 'false');

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
