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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Rate limit (per menit); dev/E2E sengaja longgar
    'throttle' => [
        'register'  => (int) env('THROTTLE_REGISTER', 6),
        'login'     => (int) env('THROTTLE_LOGIN', 5),
        'report'    => (int) env('THROTTLE_REPORT', 5),
        'review'    => (int) env('THROTTLE_REVIEW', 10),
        'tag'       => (int) env('THROTTLE_TAG', 10),
        'search'    => (int) env('THROTTLE_SEARCH', 30),
    ],

];
