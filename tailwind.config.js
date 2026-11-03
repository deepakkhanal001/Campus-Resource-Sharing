import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                brand: {
                    50: '#eff6ff',
                    100: '#dbeafe',
                    200: '#bfdbfe',
                    300: '#93c5fd',
                    400: '#60a5fa',
                    500: '#2f6fed',
                    600: '#1f5bd7',
                    700: '#1b4bb5',
                    800: '#1b3f8f',
                    900: '#1b376f',
                    950: '#12234a',
                },
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['Manrope', ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                card: '0 1px 2px rgba(15, 23, 42, 0.04), 0 1px 3px rgba(15, 23, 42, 0.05)',
                lift: '0 10px 24px -12px rgba(15, 23, 42, 0.22), 0 4px 10px -6px rgba(15, 23, 42, 0.12)',
            },
        },
    },

    plugins: [forms],
};
