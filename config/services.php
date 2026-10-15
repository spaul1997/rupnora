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

    'shiprocket' => [
        'base_url' => env('SHIPROCKET_BASE_URL', 'https://apiv2.shiprocket.in/v1/external'),
        'email' => env('SHIPROCKET_USER'),
        'password' => env('SHIPROCKET_PASSWORD'),
        'pickup_location' => env('SHIPROCKET_PICKUP_LOCATION', 'Home'),
        'pickup_postcode' => env('SHIPROCKET_PICKUP_POSTCODE'),
        'tracking_url' => env('SHIPROCKET_TRACKING_URL', 'https://shiprocket.co/tracking/{awb}'),
        'length' => (float) env('SHIPROCKET_PACKAGE_LENGTH', 10),
        'breadth' => (float) env('SHIPROCKET_PACKAGE_BREADTH', 15),
        'height' => (float) env('SHIPROCKET_PACKAGE_HEIGHT', 20),
        'minimum_weight' => (float) env('SHIPROCKET_MINIMUM_WEIGHT', 0.5),
        'timeout' => (int) env('SHIPROCKET_TIMEOUT', 20),
        'token_ttl_minutes' => (int) env('SHIPROCKET_TOKEN_TTL_MINUTES', 12960),
    ],

];
