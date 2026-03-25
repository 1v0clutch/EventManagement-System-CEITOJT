/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      screens: {
<<<<<<< HEAD
        'xs': '475px',
=======
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
        'laptop': '1440px',
      },
      colors: {
        primary: {
          50: '#f0fdf4',
          100: '#dcfce7',
          200: '#bbf7d0',
          300: '#86efac',
          400: '#4ade80',
          500: '#22c55e',
          600: '#16a34a',
          700: '#15803d',
          800: '#166534',
          900: '#14532d',
          950: '#052e16',
        },
      },
      fontFamily: {
        'display': ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
      },
      animation: {
        'fadeIn': 'fadeIn 0.2s ease-in-out',
        'scaleIn': 'scaleIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1)',
        'fade-in-up': 'fadeInUp 0.3s ease-out',
<<<<<<< HEAD
        'fade-in': 'fadeIn 0.2s ease-in-out',
        'scale-in': 'scaleIn 0.2s ease-out',
        'shake': 'shake 0.5s ease-in-out',
=======
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
      },
      keyframes: {
        fadeIn: {
          '0%': { opacity: '0', transform: 'scale(0.95)' },
          '100%': { opacity: '1', transform: 'scale(1)' },
        },
        scaleIn: {
          '0%': { opacity: '0', transform: 'scale(0.9) translateY(-10px)' },
          '100%': { opacity: '1', transform: 'scale(1) translateY(0)' },
        },
        fadeInUp: {
          '0%': { opacity: '0', transform: 'translateY(10px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
<<<<<<< HEAD
        shake: {
          '0%, 100%': { transform: 'translateX(0)' },
          '10%, 30%, 50%, 70%, 90%': { transform: 'translateX(-4px)' },
          '20%, 40%, 60%, 80%': { transform: 'translateX(4px)' },
        },
=======
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
      },
    },
  },
  plugins: [],
}
