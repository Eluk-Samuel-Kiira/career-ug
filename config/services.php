<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Main App (API + Storage)
    |--------------------------------------------------------------------------
    |
    | The main app holds the CV files, ATS data, and all API endpoints.
    | The Web app talks to it for everything.
    |
    */
    'main_app' => [
        'url'     => env('MAIN_APP_URL', 'http://127.0.0.1:8000'),
        'api_key' => env('MAIN_APP_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Web App (This instance)
    |--------------------------------------------------------------------------
    */
    'web_app' => [
        'url'     => env('WEB_APP_URL', 'http://127.0.0.1:8001'),
        'api_key' => env('WEB_APP_API_KEY'),
    ],

];