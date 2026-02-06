import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
                nunito: ['Nunito', 'sans-serif'],
            },
            colors: {
                // Primary (Duolingo Blue)
                primary: {
                    DEFAULT: '#1CB0F6',
                    dark: '#1899D6',
                    light: '#DBEAFE',
                },
                // Secondary
                secondary: {
                    DEFAULT: '#3B82F6',
                    dark: '#2563EB',
                },
                // Success (Green)
                success: {
                    DEFAULT: '#58CC02',
                    dark: '#46A302',
                    light: '#D7FFB8',
                },
                // Warning
                warning: {
                    DEFAULT: '#F59E0B',
                    dark: '#D97706',
                },
                // Error
                error: {
                    DEFAULT: '#EF4444',
                    dark: '#DC2626',
                },
                // Backgrounds
                bg: {
                    light: '#F8F9FA',
                    sheet: '#F5F5F5',
                    'light-gray': '#F3F4F6',
                },
                // Borders
                border: {
                    gray: '#E5E5E5',
                    dark: '#DEE2E6',
                },
                // Text
                text: {
                    primary: '#1F2937',
                    secondary: '#6B7280',
                    hint: '#9CA3AF',
                },
            },
            borderRadius: {
                '2xl': '1rem',
                '3xl': '1.5rem',
                'duo': '22px', // Duolingo button radius
            },
            boxShadow: {
                'duo': '0 4px 0 0 var(--tw-shadow-color)',
                'duo-sm': '0 2px 0 0 var(--tw-shadow-color)',
            },
        },
    },

    plugins: [forms],
};
