<?php

declare(strict_types=1);

return [

    'page_title' => 'Sign in',

    'title' => 'Welcome back',
    'subtitle' => 'Sign in to your hotel management workspace',

    'email_label' => 'Email address',
    'email_placeholder' => 'you@your-hotel.com',

    'password_label' => 'Password',
    'password_placeholder' => 'Your password',
    'password_show' => 'Show password',
    'password_hide' => 'Hide password',

    'remember' => 'Keep me signed in',
    'forgot' => 'Forgot your password?',

    'submit' => 'Sign in',
    'submitting' => 'Signing in…',

    'error_title' => 'Unable to sign in',

    'trial_question' => 'New to :brand?',
    'trial_cta' => 'Start your free 14 day trial',

    // Not rendered by the current minimal layout. Kept so the
    // presentation panel can be restored without rewriting the copy.
    'panel' => [
        'headline_before' => 'Run your front desk,',
        'headline_highlight' => 'restaurant and accounting',
        'headline_after' => 'from a single screen',
        'intro' => 'A complete, modern and intuitive solution for running a hotel more efficiently.',
        'stats' => [
            'occupancy_label' => 'Occupancy rate',
            'occupancy_value' => '78%',
            'rate_label' => 'Average nightly rate',
            'rate_value' => 'XAF 42,500',
            'movements_label' => 'Today’s arrivals / departures',
            'movements_value' => '24 / 19',
        ],
        'features' => [
            'planning' => 'Live room planning',
            'outlets' => 'Restaurant, laundry, banquets and more',
            'accounting' => 'Accounting with supporting documents',
            'payments' => 'Payment by card, Orange Money or MTN MoMo',
        ],
    ],

];
