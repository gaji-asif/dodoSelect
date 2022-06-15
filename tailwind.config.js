const defaultTheme = require('tailwindcss/defaultTheme');

module.exports = {
    mode: 'jit',
    important: true,
    purge: {
        content: [
            './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
            './app/Http/Controllers/**/*.php',
            './storage/framework/views/*.php',
            './resources/views/**/*.blade.php'
        ],
        options: {
            safelist: ['bg-blue-500', 'bg-green-500']
        }
    },

    theme: {
        extend: {
            fontFamily: {
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
            },
            fontSize: {
                'xxs': '.5rem'
            },
            boxShadow: {
                'md-top': '0 -4px 6px -1px rgba(0, 0, 0, 0.1), 0 -2px 4px -1px rgba(0, 0, 0, 0.06)'
            }
        },
    },

    variants: {
        extend: {
            opacity: ['disabled'],
        },
    },

    plugins: [require('@tailwindcss/forms')],
};
