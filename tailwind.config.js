import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Plus Jakarta Sans', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'electric-blue': '#3B82F6',
                'pulse-orange': '#F97316',
            },
            animation: {
                'slide-in-snap': 'slideInSnap 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)',
                'urgent-heartbeat': 'urgentHeartbeat 2s infinite',
                'dash': 'dash 5s linear forwards',
            },
            keyframes: {
                slideInSnap: {
                    '0%': { opacity: '0', transform: 'translateY(20px) scale(0.95)' },
                    '60%': { opacity: '1', transform: 'translateY(-5px) scale(1.02)' },
                    '100%': { transform: 'translateY(0) scale(1)' },
                },
                urgentHeartbeat: {
                    '0%': { boxShadow: '0 0 0 0 rgba(249, 115, 22, 0.4)' },
                    '70%': { boxShadow: '0 0 0 10px rgba(249, 115, 22, 0)' },
                    '100%': { boxShadow: '0 0 0 0 rgba(249, 115, 22, 0)' },
                },
                dash: {
                    '0%': { strokeDasharray: '100, 100' },
                    '100%': { strokeDasharray: '0, 100' },
                },
            },
        },
    },
    plugins: [],
};
