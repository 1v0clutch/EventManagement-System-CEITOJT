import { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export default function Login() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [rememberMe, setRememberMe] = useState(false);
  const [error, setError] = useState('');
  const [lockoutInfo, setLockoutInfo] = useState(null);
  const [failedAttempts, setFailedAttempts] = useState(0);
  const [loading, setLoading] = useState(false);
  const { login } = useAuth();
  const navigate = useNavigate();

  // Check lockout status when email changes
  useEffect(() => {
    if (!email) {
      setLockoutInfo(null);
      setFailedAttempts(0);
      return;
    }
    
    checkLockoutStatus(email);
  }, [email]);

  useEffect(() => {
    const savedEmail = localStorage.getItem('rememberedEmail');
    if (savedEmail) {
      setEmail(savedEmail);
      setRememberMe(true);
    }
  }, []);

  const checkLockoutStatus = (emailAddress) => {
    const lockoutKey = `loginLockout_${emailAddress}`;
    const storedLockout = localStorage.getItem(lockoutKey);
    
    if (storedLockout) {
      const lockoutData = JSON.parse(storedLockout);
      const now = Math.floor(Date.now() / 1000);
      
      if (lockoutData.lockedUntil > now) {
        // Still locked
        const remainingSeconds = lockoutData.lockedUntil - now;
        setLockoutInfo({
          message: 'This account is locked due to too many failed attempts.',
          remainingSeconds: remainingSeconds,
        });
        setFailedAttempts(3);
        
        // Start countdown
        const interval = setInterval(() => {
          setLockoutInfo(prev => {
            if (!prev || prev.remainingSeconds <= 1) {
              clearInterval(interval);
              localStorage.removeItem(lockoutKey);
              setFailedAttempts(0);
              return null;
            }
            return {
              ...prev,
              remainingSeconds: prev.remainingSeconds - 1,
            };
          });
        }, 1000);
      } else {
        // Lockout expired
        localStorage.removeItem(lockoutKey);
        setLockoutInfo(null);
        setFailedAttempts(0);
      }
    } else {
      // No lockout for this email
      setLockoutInfo(null);
      setFailedAttempts(0);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    const lockoutKey = `loginLockout_${email}`;
    const attemptsKey = `loginAttempts_${email}`;

    try {
      await login(email, password);
      
      // Successful login - clear attempts for this email
      setFailedAttempts(0);
      localStorage.removeItem(lockoutKey);
      localStorage.removeItem(attemptsKey);
      
      if (rememberMe) {
        localStorage.setItem('rememberedEmail', email);
      } else {
        localStorage.removeItem('rememberedEmail');
      }
      
      navigate('/dashboard');
    } catch (err) {
      const response = err.response?.data;
      
      // Check if it's a lockout response (429 status)
      if (err.response?.status === 429 && response?.remaining_seconds) {
        const lockedUntil = Math.floor(Date.now() / 1000) + response.remaining_seconds;
        
        // Store lockout for this specific email
        localStorage.setItem(lockoutKey, JSON.stringify({
          lockedUntil: lockedUntil,
          email: email,
        }));
        
        setLockoutInfo({
          message: response.message,
          remainingSeconds: response.remaining_seconds,
        });
        setFailedAttempts(3);
        
        // Start countdown timer
        const interval = setInterval(() => {
          setLockoutInfo(prev => {
            if (!prev || prev.remainingSeconds <= 1) {
              clearInterval(interval);
              localStorage.removeItem(lockoutKey);
              localStorage.removeItem(attemptsKey);
              setFailedAttempts(0);
              return null;
            }
            return {
              ...prev,
              remainingSeconds: prev.remainingSeconds - 1,
            };
          });
        }, 1000);
      } else {
        // Failed login - get attempts for this specific email
        const storedAttempts = localStorage.getItem(attemptsKey);
        const currentAttempts = storedAttempts ? parseInt(storedAttempts) : 0;
        const newAttempts = currentAttempts + 1;
        
        // Store attempts for this email
        localStorage.setItem(attemptsKey, newAttempts.toString());
        setFailedAttempts(newAttempts);
        
        // Check if this is the 3rd attempt
        if (newAttempts >= 3) {
          // Lock immediately on frontend for this email
          const lockedUntil = Math.floor(Date.now() / 1000) + 300; // 5 minutes
          
          localStorage.setItem(lockoutKey, JSON.stringify({
            lockedUntil: lockedUntil,
            email: email,
          }));
          
          setLockoutInfo({
            message: 'Too many failed attempts. This account has been locked for 5 minutes.',
            remainingSeconds: 300,
          });
          
          // Start countdown
          const interval = setInterval(() => {
            setLockoutInfo(prev => {
              if (!prev || prev.remainingSeconds <= 1) {
                clearInterval(interval);
                localStorage.removeItem(lockoutKey);
                localStorage.removeItem(attemptsKey);
                setFailedAttempts(0);
                return null;
              }
              return {
                ...prev,
                remainingSeconds: prev.remainingSeconds - 1,
              };
            });
          }, 1000);
        } else {
          // Show error with remaining attempts
          const remainingAttempts = 3 - newAttempts;
          setError(response?.message || response?.errors?.email?.[0] || `Invalid email or password. ${remainingAttempts} attempt(s) remaining.`);
        }
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-md w-full space-y-8">
        <div>
          <h2 className="mt-6 text-center text-3xl font-extrabold text-gray-900">
            Event Management System
          </h2>
          <p className="mt-2 text-center text-sm text-gray-600">
            Sign in to your account
          </p>
        </div>
        <form className="mt-8 space-y-6" onSubmit={handleSubmit}>
          {lockoutInfo && (
            <div className="rounded-md bg-red-50 border-2 border-red-400 p-4">
              <div className="flex items-start">
                <div className="flex-shrink-0">
                  <svg className="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
                  </svg>
                </div>
                <div className="ml-3 flex-1">
                  <h3 className="text-sm font-bold text-red-800">Account Locked</h3>
                  <p className="text-sm text-red-700 mt-1">{lockoutInfo.message}</p>
                  <div className="mt-2 bg-red-100 rounded px-3 py-2">
                    <p className="text-sm font-mono text-red-900">
                      Time remaining: {Math.floor(lockoutInfo.remainingSeconds / 60)}:{String(lockoutInfo.remainingSeconds % 60).padStart(2, '0')}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          )}
          
          {error && !lockoutInfo && (
            <div className="rounded-md bg-red-50 p-4">
              <div className="flex items-start">
                <div className="flex-shrink-0">
                  <svg className="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
                  </svg>
                </div>
                <div className="ml-3 flex-1">
                  <p className="text-sm text-red-800">{error}</p>
                  {failedAttempts > 0 && failedAttempts < 3 && (
                    <div className="mt-2">
                      <div className="flex items-center">
                        <div className="flex-1 bg-red-200 rounded-full h-2">
                          <div 
                            className="bg-red-600 h-2 rounded-full transition-all duration-300"
                            style={{ width: `${(failedAttempts / 3) * 100}%` }}
                          ></div>
                        </div>
                        <span className="ml-2 text-xs font-medium text-red-700">
                          {failedAttempts}/3
                        </span>
                      </div>
                    </div>
                  )}
                </div>
              </div>
            </div>
          )}
          <div className="rounded-md shadow-sm -space-y-px">
            <div>
              <label htmlFor="email" className="sr-only">Email address</label>
              <input
                id="email"
                name="email"
                type="email"
                required
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                className="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                placeholder="main.firstname.lastname@cvsu.edu.ph"
              />
            </div>
            <div>
              <label htmlFor="password" className="sr-only">Password</label>
              <input
                id="password"
                name="password"
                type="password"
                required
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                className="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                placeholder="Password"
              />
            </div>
          </div>

          <div className="flex items-center justify-between">
            <div className="flex items-center">
              <input
                id="rememberMe"
                name="rememberMe"
                type="checkbox"
                checked={rememberMe}
                onChange={(e) => setRememberMe(e.target.checked)}
                className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label htmlFor="rememberMe" className="ml-2 block text-sm text-gray-900">
                Remember me
              </label>
            </div>
            <Link to="/forgot-password" className="text-sm font-medium text-blue-600 hover:text-blue-500">
              Forgot password?
            </Link>
          </div>

          <div>
            <button
              type="submit"
              disabled={loading || lockoutInfo || failedAttempts >= 3}
              className="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {lockoutInfo || failedAttempts >= 3 ? (
                <span className="flex items-center">
                  <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                  Account Locked
                </span>
              ) : loading ? (
                'Signing in...'
              ) : (
                'Sign in'
              )}
            </button>
          </div>

          <div className="text-center">
            <Link to="/register" className="font-medium text-blue-600 hover:text-blue-500">
              Don't have an account? Register
            </Link>
          </div>
        </form>
      </div>
    </div>
  );
}
