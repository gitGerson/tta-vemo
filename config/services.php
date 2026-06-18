<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'product_esb' => [
        'url' => env('PRODUCT_ESB_URL'),
        'token' => env('PRODUCT_ESB_TOKEN'),
    ],

    'jpayroll' => [
        'url' => env('JPAYROLL_URL'),
        'token' => env('JPAYROLL_TOKEN'),
    ],

    // Token consumed by laravel-notification-channels/telegram when sending messages.
    'telegram-bot-api' => [
        'token' => env('TELEGRAM_BOT_TOKEN'),
    ],

    // Used to build the deep link (t.me/<username>?start=<token>) the user taps to
    // link their account; the /start message is received via long-polling.
    'telegram_bot' => [
        'username' => env('TELEGRAM_BOT_USERNAME'),
    ],

];
