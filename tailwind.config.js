const defaultTheme = require('tailwindcss/defaultTheme');

module.exports = {
    purge: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Open Sans', ...defaultTheme.fontFamily.sans],
            },
        },
        screens: {
            'xs': '480px',    // Extra small devices
            'sm': '640px',    // Small devices
            'md': '768px',    // Medium devices
            'lg': '1024px',   // Large devices
            'xl': '1280px',   // Extra large devices
            '2xl': '1536px',  // 2X large devices
        },
    },

    variants: {
        extend: {
            opacity: ['disabled'],
        },
    },

    plugins: [require('@tailwindcss/forms')],
};
