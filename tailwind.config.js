import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Livewire/**/*.php',
        './app/Enums/**/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Instrument Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            },
            colors: {
                brand: {
                    DEFAULT: '#bc002d',
                    hover: '#a30028',
                },
                ink: {
                    DEFAULT: '#000000',
                    alt: '#16161d',
                },
                surface: '#d9d9d9',
                canvas: '#ffffff',
                status: {
                    'pending-bg': '#fff3cd',
                    'pending-fg': '#856404',
                    'paid-bg': '#cfe2ff',
                    'paid-fg': '#084298',
                    'shipped-bg': '#d1e7dd',
                    'shipped-fg': '#0f5132',
                },
            },
            borderRadius: {
                pill: '100px',
            },
            container: {
                center: true,
                padding: '1.25rem',
                screens: {
                    '2xl': '1280px',
                },
            },
        },
    },

    plugins: [forms],
};
