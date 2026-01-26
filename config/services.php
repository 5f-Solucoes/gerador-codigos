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

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'watchguard' => [
        'access_id' => env('WATCHGUARD_ACCESS_ID'),
        'client_secret' => env('WATCHGUARD_CLIENT_SECRET'),
        'auth_url' => env('WATCHGUARD_AUTH_URL'),
        'api_base' => env('WATCHGUARD_API_BASE'),
        'api_key' => env('WATCHGUARD_API_KEY'),
        'account_id' => env('WATCHGUARD_ACCOUNT_ID'),
        'resource_id' => env('WATCHGUARD_RESOURCE_ID'),
    ],

];
