/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            colors: {
                primary: {
                    500: '#0f766e',
                    DEFAULT: '#0d9488',
                    light: '#5eead4',
                },
                danger: {
                    500: '#dc2626',
                    DEFAULT: '#b91c1c',
                    light: '#fca5a5',
                },
                warning: {
                    500: '#f59e0b',
                    DEFAULT: '#d97706',
                    light: '#fcd34d',
                },
                success: {
                    500: '#16a34a',
                    DEFAULT: '#15803d',
                    light: '#86efac',
                },
                gray: {
                    500: '#6b7280',
                },
                blue: {
                    500: '#3b82f6',
                },
                sky: {
                    500: '#0ea5e9',
                },
            },
        },
    },
    plugins: [
        require('daisyui')
    ],
    daisyui: {
        themes: [
            {
                jitcustom: {
                    primary: '#0d9488',
                    secondary: '#0f172a',
                    accent: '#f59e0b',
                    neutral: '#111827',
                    'base-100': '#f8fafc',
                    info: '#0284c7',
                    success: '#16a34a',
                    warning: '#d97706',
                    error: '#dc2626',
                }
            },
            'light', 'cupcake', 'retro', 'corporate',
        ],
    },
    safelist: [
        'bg-primary', 'text-primary', 'hover:bg-primary-light', 'bg-primary-light',
        'bg-success', 'text-success', 'hover:bg-success-light', 'bg-success-light',
        'bg-danger', 'text-danger', 'hover:bg-danger-light', 'bg-danger-light',
        'bg-warning', 'text-warning', 'hover:bg-warning-light', 'bg-warning-light',

        'bg-green-100', 'text-green-800', 'text-green-700',
        'bg-green-600', 'hover:bg-green-700', 'text-white',

        'bg-red-100', 'text-red-800', 'text-red-700',
        'bg-red-500', 'bg-red-600', 'hover:bg-red-600', 'hover:bg-red-700',

        'bg-yellow-100', 'text-yellow-800', 'text-yellow-700',
        'bg-yellow-300', 'bg-yellow-400', 'text-yellow-900',
        'bg-yellow-50', 'bg-yellow-200',

        'bg-blue-100', 'text-blue-800', 'text-blue-700',
        'bg-blue-600', 'text-blue-900', 'bg-blue-50', 'bg-blue-300',

        'bg-gray-50', 'bg-gray-100', 'bg-gray-200',
        'text-gray-800', 'text-gray-600', 'text-gray-900', 'text-gray-700',

        'bg-primary-500', 'bg-danger-500', 'bg-warning-500', 'bg-success-500',
        'bg-blue-500', 'bg-gray-500', 'bg-sky-500',
        'text-white', 'text-black',
    ],
};
