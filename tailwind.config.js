import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
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
            colors: {
                // Your Custom Palette (Light Mode)
                'custom-light-gray': '#e3e4e5', // (227,228,229)
                'custom-slate-blue': '#9297ae', // (146,151,174)
                'custom-mid-blue': '#405685',   // (64,86,133)
                'custom-periwinkle': '#b1bcdb', // (177,188,219)
                'custom-dark-blue': '#162a72',  // (22,42,114)

                // Dark Mode Inversions (example values, adjust as needed)
                'dark-custom-light-gray': '#2c2c2c',
                'dark-custom-slate-blue': '#6d7280',
                'dark-custom-mid-blue': '#2a3b5c',
                'dark-custom-periwinkle': '#8e99b8',
                'dark-custom-dark-blue': '#0e1e4a',
            },
        },
    },

    plugins: [forms],
};
