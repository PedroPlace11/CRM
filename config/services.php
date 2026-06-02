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

    // OpenAI (gpt-5-nano) — used by App\Services\AI\CrmAiAssistant.
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'organization' => env('OPENAI_ORGANIZATION'),
        'model'   => env('OPENAI_MODEL', 'gpt-5-nano'),
    ],

    // Groq (OpenAI-compatible) — used by App\Services\AI\GroqChatService.
    'groq' => [
        'api_key' => env('GROQ_API_KEY'),
        'model' => env('GROQ_MODEL', 'openai/gpt-oss-120b'),
        'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
    ],

    // hCaptcha verification for public lead forms.
    'hcaptcha' => [
        'secret'  => env('HCAPTCHA_SECRET'),
        'sitekey' => env('HCAPTCHA_SITEKEY'),
    ],

    // Google Calendar OAuth.
    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI'),
    ],

    // Google Calendar Service Account.
    'google_service_account' => [
        'json' => env('GOOGLE_SERVICE_ACCOUNT_JSON'),
        'calendar_id' => env('GOOGLE_SERVICE_ACCOUNT_CALENDAR_ID', 'primary'),
        'scopes' => env('GOOGLE_SERVICE_ACCOUNT_SCOPES', 'https://www.googleapis.com/auth/calendar'),
    ],

];
