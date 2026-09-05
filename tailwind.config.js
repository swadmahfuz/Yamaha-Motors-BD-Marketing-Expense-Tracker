import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/livewire/livewire/dist/**/*.js',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Segoe UI', 'system-ui', ...defaultTheme.fontFamily.sans],
                display: ['Segoe UI', 'system-ui', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                yamaha: {
                    red: '#E60012',
                    black: '#111111',
                    silver: '#C8C8C8',
                },
            },
        },
    },

    plugins: [forms],
};
