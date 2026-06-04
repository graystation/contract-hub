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
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    safelist: [
        'bg-gray-100',   'text-gray-800',
        'bg-green-100',  'text-green-800',
        'bg-blue-100',   'text-blue-800',
        'bg-red-100',    'text-red-800',
        'bg-yellow-100', 'text-yellow-800',
        'bg-orange-100',  'text-orange-800',
        'border-violet-500',
        'bg-emerald-600', 'hover:bg-emerald-700',
        'text-emerald-600', 'hover:text-emerald-800',
    ],

    plugins: [forms],
};
