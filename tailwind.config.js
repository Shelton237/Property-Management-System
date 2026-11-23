import defaultTheme from 'tailwindcss/defaultTheme';

/*
 * Brand palette, built around the two official navies: #082b55 (bleu marine)
 * and #0d3768 (bleu profond). This object is the single place to change the
 * product colour, every component refers to it through the `brand-*` classes.
 */
const brand = {
    50: '#f0f5fc',
    100: '#dde8f6',
    200: '#bcd1ec',
    300: '#8fb2df',
    400: '#5a8bcd',
    500: '#2f66b4',
    600: '#134a8a',
    700: '#0d3768',
    800: '#082b55',
    900: '#061f3e',
    950: '#04142a',
    DEFAULT: '#082b55',
};

/*
 * Gold palette, built around the official #f2ae18. Named `gold` rather than
 * `accent`: the shadcn preset already owns an `accent` key, and reusing it
 * would silently break every component that depends on it.
 */
const gold = {
    50: '#fef8e8',
    100: '#fdedc3',
    200: '#fbdd8b',
    300: '#f8c94e',
    400: '#f5ba2a',
    500: '#f2ae18',
    600: '#d2900c',
    700: '#a9700d',
    800: '#8a5a12',
    900: '#724a12',
    DEFAULT: '#f2ae18',
};

/* Neutral surfaces and secondary text, from the same official palette. */
const page = '#f6f9fd';
const field = '#eef3fa';
const ink = '#526b8c';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: ['class'],
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.{vue,js,ts,jsx,tsx}',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Montserrat', ...defaultTheme.fontFamily.sans],
            },
            borderRadius: {
                lg: 'var(--radius)',
                md: 'calc(var(--radius) - 2px)',
                sm: 'calc(var(--radius) - 4px)',
            },
            colors: {
                brand,
                gold,
                page,
                field,
                ink,
                background: 'hsl(var(--background))',
                foreground: 'hsl(var(--foreground))',
                card: {
                    DEFAULT: 'hsl(var(--card))',
                    foreground: 'hsl(var(--card-foreground))',
                },
                popover: {
                    DEFAULT: 'hsl(var(--popover))',
                    foreground: 'hsl(var(--popover-foreground))',
                },
                primary: {
                    DEFAULT: 'hsl(var(--primary))',
                    foreground: 'hsl(var(--primary-foreground))',
                },
                secondary: {
                    DEFAULT: 'hsl(var(--secondary))',
                    foreground: 'hsl(var(--secondary-foreground))',
                },
                muted: {
                    DEFAULT: 'hsl(var(--muted))',
                    foreground: 'hsl(var(--muted-foreground))',
                },
                accent: {
                    DEFAULT: 'hsl(var(--accent))',
                    foreground: 'hsl(var(--accent-foreground))',
                },
                destructive: {
                    DEFAULT: 'hsl(var(--destructive))',
                    foreground: 'hsl(var(--destructive-foreground))',
                },
                border: 'hsl(var(--border))',
                input: 'hsl(var(--input))',
                ring: 'hsl(var(--ring))',
                chart: {
                    1: 'hsl(var(--chart-1))',
                    2: 'hsl(var(--chart-2))',
                    3: 'hsl(var(--chart-3))',
                    4: 'hsl(var(--chart-4))',
                    5: 'hsl(var(--chart-5))',
                },
                sidebar: {
                    DEFAULT: 'hsl(var(--sidebar-background))',
                    foreground: 'hsl(var(--sidebar-foreground))',
                    primary: 'hsl(var(--sidebar-primary))',
                    'primary-foreground': 'hsl(var(--sidebar-primary-foreground))',
                    accent: 'hsl(var(--sidebar-accent))',
                    'accent-foreground': 'hsl(var(--sidebar-accent-foreground))',
                    border: 'hsl(var(--sidebar-border))',
                    ring: 'hsl(var(--sidebar-ring))',
                },
            },
        },
    },
    plugins: [require('tailwindcss-animate')],
};
