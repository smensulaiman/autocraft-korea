<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'carapis' => [
        'url' => env('CARAPIS_API_URL', 'https://api.carapis.com'),
        'key' => env('CARAPIS_API_KEY'),
    ],

    'pricing' => [
        'markup' => (float) env('PRICE_MARKUP_PERCENT', 10),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'sales' => [
        // Comma-separated list of recipient addresses.
        'agent_emails' => array_filter(array_map('trim', explode(',', (string) env('SALES_AGENT_EMAIL')))),
        // Comma-separated list of addresses CC'd on every inquiry.
        'cc_emails' => array_filter(array_map('trim', explode(',', (string) env('SALES_CC_EMAIL')))),
    ],

];
