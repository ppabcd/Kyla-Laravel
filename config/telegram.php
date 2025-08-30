<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Telegram Bot Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the Telegram Bot integration.
    |
    */

    // Bot Token from BotFather
    'bot_token' => env('TELEGRAM_BOT_TOKEN'),

    // Webhook URL
    'webhook_url' => env('TELEGRAM_WEBHOOK_URL'),

    // Admin User IDs (Telegram User IDs)
    'admin_ids' => array_map('intval', explode(',', env('TELEGRAM_ADMIN_IDS', ''))),

    // Bot Username
    'bot_username' => env('TELEGRAM_BOT_USERNAME'),

    // Default Language
    'default_language' => env('TELEGRAM_DEFAULT_LANGUAGE', 'en'),

    // Webhook Secret Token (optional)
    'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET'),

    // Rate Limiting
    'rate_limit' => [
        'enabled' => env('TELEGRAM_RATE_LIMIT_ENABLED', true),
        'burst_enabled' => env('TELEGRAM_RATE_LIMIT_BURST_ENABLED', true),
        'per_second_enabled' => env('TELEGRAM_RATE_LIMIT_PER_SECOND_ENABLED', true),
        'max_requests_per_minute' => env('TELEGRAM_RATE_LIMIT_MAX_REQUESTS', 30),
    ],

    // Logging
    'logging' => [
        'enabled' => env('TELEGRAM_LOGGING_ENABLED', true),
        'level' => env('TELEGRAM_LOG_LEVEL', 'info'),
    ],

    // Media Settings
    'media' => [
        'max_file_size' => env('TELEGRAM_MAX_FILE_SIZE', 20971520), // 20MB
        'allowed_types' => ['photo', 'video', 'document', 'audio'],
        'auto_moderation' => env('TELEGRAM_AUTO_MODERATION', true),
    ],

    // Payment Settings
    'payment' => [
        'provider' => env('TELEGRAM_PAYMENT_PROVIDER', 'stripe'),
        'currency' => env('TELEGRAM_PAYMENT_CURRENCY', 'IDR'),
        'donation_amounts' => [
            10000 => 5000,
            25000 => 10000,
            50000 => 20000,
            100000 => 40000,
            250000 => 100000,
            500000 => 200000,
            1000000 => 400000,
        ],
    ],

    // Matching Settings
    'matching' => [
        'max_distance_km' => env('TELEGRAM_MAX_DISTANCE_KM', 50),
        'min_age' => env('TELEGRAM_MIN_AGE', 18),
        'max_age' => env('TELEGRAM_MAX_AGE', 65),
        'auto_match' => env('TELEGRAM_AUTO_MATCH', true),
    ],

    // Security Settings
    'security' => [
        'captcha_enabled' => env('TELEGRAM_CAPTCHA_ENABLED', true),
        'max_login_attempts' => env('TELEGRAM_MAX_LOGIN_ATTEMPTS', 3),
        'session_timeout' => env('TELEGRAM_SESSION_TIMEOUT', 3600), // 1 hour
    ],

    /*
    |--------------------------------------------------------------------------
    | Bot Settings
    |--------------------------------------------------------------------------
    */
    'bot_name' => env('TELEGRAM_BOT_NAME', 'Kyla Bot'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Settings
    |--------------------------------------------------------------------------
    */
    'webhook' => [
        'enabled' => env('TELEGRAM_WEBHOOK_ENABLED', true),
        'max_connections' => env('TELEGRAM_WEBHOOK_MAX_CONNECTIONS', 40),
        'allowed_updates' => [
            'message',
            'callback_query',
            'inline_query',
            'chosen_inline_result',
            'pre_checkout_query',
            'shipping_query',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto Registration
    |--------------------------------------------------------------------------
    */
    'auto_registration' => [
        'commands' => [
            'enabled' => true, // Re-enabled
            'namespace' => 'App\\Telegram\\Commands',
            'path' => app_path('Telegram/Commands'),
        ],
        'callbacks' => [
            'enabled' => true, // Re-enabled
            'namespace' => 'App\\Telegram\\Callbacks',
            'path' => app_path('Telegram/Callbacks'),
        ],
        'listeners' => [
            'enabled' => false, // Keep disabled for now
            'namespace' => 'App\\Telegram\\Listeners',
            'path' => app_path('Telegram/Listeners'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    */
    'middleware' => [
        'global' => [
            \App\Telegram\Middleware\LoggingMiddleware::class,
            \App\Telegram\Middleware\RateLimitMiddleware::class,
        ],
        'commands' => [
            \App\Telegram\Middleware\CheckUserMiddleware::class,
            \App\Telegram\Middleware\CheckLanguageMiddleware::class,
        ],
        'callbacks' => [
            \App\Telegram\Middleware\CheckUserMiddleware::class,
        ],
        'per_command' => [
            'start' => [
                \App\Telegram\Middleware\CheckGenderMiddleware::class,
                \App\Telegram\Middleware\CheckInterestMiddleware::class,
                \App\Telegram\Middleware\CheckBannedUserMiddleware::class,
                \App\Telegram\Middleware\CheckCaptchaMiddleware::class,
            ],
            'search' => [
                \App\Telegram\Middleware\CheckGenderMiddleware::class,
                \App\Telegram\Middleware\CheckInterestMiddleware::class,
                \App\Telegram\Middleware\CheckBannedUserMiddleware::class,
                \App\Telegram\Middleware\CheckCaptchaMiddleware::class,
            ],
            'next' => [
                \App\Telegram\Middleware\CheckGenderMiddleware::class,
                \App\Telegram\Middleware\CheckInterestMiddleware::class,
                \App\Telegram\Middleware\CheckBannedUserMiddleware::class,
                \App\Telegram\Middleware\CheckCaptchaMiddleware::class,
            ],
            'stop' => [
                \App\Telegram\Middleware\CheckBannedUserMiddleware::class,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue
    |--------------------------------------------------------------------------
    |
    | Configure whether Telegram webhook updates should be processed via
    | a queued job. When enabled, incoming updates are dispatched to the
    | specified queue and handled asynchronously.
    */
    'queue' => [
        'enabled' => env('TELEGRAM_QUEUE_ENABLED', false),
        'name' => env('TELEGRAM_QUEUE_NAME', 'telegram'),
        'tries' => env('TELEGRAM_QUEUE_TRIES', 3),
        'timeout' => env('TELEGRAM_QUEUE_TIMEOUT', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Tables
    |--------------------------------------------------------------------------
    */
    'tables' => [
        'users' => 'telegram_users',
        'chats' => 'telegram_chats',
        'messages' => 'telegram_messages',
        'sessions' => 'telegram_sessions',
        'pairs' => 'telegram_pairs',
    ],
];
