import { useState, useEffect } from 'react';
import { useNavigate, useLocation, Link } from 'react-router-dom';
import api from '../services/api';
import AuthBackground from '../components/AuthBackground';

export default function VerifyOtp() {
    const [otp, setOtp] = useState(['', '', '', '', '', '']);
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);
    const [resending, setResending] = useState(false);
    const navigate = useNavigate();
    const location = useLocation();
    const email = location.state?.email;

    useEffect(() => {
        if (!email) {
            navigate('/forgot-password');
        }
    }, [email, navigate]);

    const handleChange = (element, index) => {
        if (isNaN(element.value)) return false;

        setOtp([...otp.map((d, idx) => (idx === index ? element.value : d))]);

        // Focus next input
        if (element.nextSibling && element.value) {
            element.nextSibling.focus();
        }
    };

    const handleKeyDown = (e, index) => {
        if (e.key === 'Backspace' && !otp[index] && e.target.previousSibling) {
            e.target.previousSibling.focus();
        }
    };

    const handleSubmit = async (e) => {
        if (e) e.preventDefault();
        setError('');
        const otpCode = otp.join('');

        if (otpCode.length !== 6) {
            setError('Please enter the 6-digit code sent to your email.');
            return;
        }

        setLoading(true);
        try {
            const response = await api.post('/verify-otp', {
                email,
                otp: otpCode
            });

            // Navigate to reset password with the token
            navigate('/reset-password', {
                state: {
                    email,
                    reset_token: response.data.reset_token
                }
            });
        } catch (err) {
            setError(err.response?.data?.message || 'Invalid or expired OTP. Please try again.');
        } finally {
            setLoading(false);
        }
    };

    const handleResendOtp = async () => {
        setError('');
        setResending(true);
        try {
            await api.post('/request-otp', { email });
            // Clear OTP inputs
            setOtp(['', '', '', '', '', '']);
            // Optional: Show success message
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to resend OTP. Please try again.');
        } finally {
            setResending(false);
        }
    };

    return (
        <div className="min-h-screen flex items-center justify-center relative overflow-hidden py-12 px-4 sm:px-6 lg:px-8">
            <AuthBackground />

            <div className="max-w-md w-full space-y-8 relative z-10">
                <div className="bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl p-8">
                    <div>
                        <h2 className="mt-6 text-center text-3xl font-extrabold text-gray-900">
                            Verify OTP
                        </h2>
                        <p className="mt-2 text-center text-sm text-gray-600">
                            We've sent a 6-digit code to <br />
                            <span className="font-semibold text-gray-900">{email}</span>
                        </p>
                    </div>

                    <form className="mt-8 space-y-6" onSubmit={handleSubmit} autoComplete="off">
                        {error && (
                            <div className="rounded-md bg-red-50 p-4 border border-red-200">
                                <p className="text-sm text-red-800 text-center">{error}</p>
                            </div>
                        )}

                        <div className="flex justify-center space-x-2">
                            {otp.map((data, index) => (
                                <input
                                    key={index}
                                    type="text"
                                    maxLength="1"
                                    value={data}
                                    onChange={(e) => handleChange(e.target, index)}
                                    onKeyDown={(e) => handleKeyDown(e, index)}
                                    className="w-10 h-12 text-center border-2 border-gray-300 rounded-lg text-lg font-bold text-gray-900 focus:border-green-600 focus:outline-none focus:ring-1 focus:ring-green-600 transition-all"
                                />
                            ))}
                        </div>

                        <div>
                            <button
                                type="submit"
                                disabled={loading || otp.join('').length !== 6}
                                className="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-xl text-white bg-green-700 hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-600 disabled:opacity-50 transition-all duration-200 shadow-md hover:shadow-lg"
                            >
                                {loading ? (
                                    <svg className="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                ) : (
                                    'Verify & Continue'
                                )}
                            </button>
                        </div>

                        <div className="text-center space-y-4">
                            <p className="text-sm text-gray-600">
                                Didn't receive the code?{' '}
                                <button
                                    type="button"
                                    onClick={handleResendOtp}
                                    disabled={resending}
                                    className="font-medium text-green-700 hover:text-green-600 disabled:opacity-50"
                                >
                                    {resending ? 'Resending...' : 'Resend OTP'}
                                </button>
                            </p>
                            <Link to="/forgot-password" size="sm" className="inline-block text-gray-500 hover:text-gray-700 text-sm font-medium">
                                Back to request email
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
}
