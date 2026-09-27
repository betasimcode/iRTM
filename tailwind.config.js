import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        "./resources/**/*.js",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    safelist: [
        'bg-red-500',
        'bg-orange-500',
        'bg-orange-500/10',
        'bg-yellow-500',
        'bg-green-500',
        'bg-blue-500',
        'bg-gray-200',

        'border-[var(--D)]',
        'border-[var(--C)]',
        'border-[var(--A)]',
        'border-[var(--B)]',
        'border-[var(--ROOKIE)]',

        'text-[var(--D)]',
        'text-[var(--C)]',
        'text-[var(--A)]',
        'text-[var(--B)]',
        'text-[var(--ROOKIE)]',

        'bg-[var(--D)]',
        'bg-[var(--C)]',
        'bg-[var(--A)]',
        'bg-[var(--B)]',
        'bg-[var(--ROOKIE)]',

        'text-white',
        'text-gray-900',

        'border-red-500',
        'border-orange-500',
        'border-yellow-500',
        'border-green-500',
        'border-blue-500',

        // Patrón para capturar cualquier variable CSS en text-[var(--...)]
        {
        pattern: /^(border|bg|text)-\[var\(--[a-zA-Z0-9_-]+\)\]$/,
        },
    ],

    plugins: [forms],
};


