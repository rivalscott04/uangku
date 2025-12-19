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

    /*
    |--------------------------------------------------------------------------
    | Telegram Bot
    |--------------------------------------------------------------------------
    |
    | Konfigurasi sederhana untuk Bot Telegram. Kamu cukup mengisi
    | TELEGRAM_BOT_TOKEN di file .env. URL webhook akan diatur lewat
    | BotFather atau API setWebhook Telegram.
    |
    */

    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'api_url' => env('TELEGRAM_API_URL', 'https://api.telegram.org'),
        // Username bot, tanpa awalan @ (dipakai untuk deep-linking dari halaman register).
        'bot_username' => env('TELEGRAM_BOT_USERNAME'),
        // URL webhook (optional). Jika tidak diisi, akan pakai APP_URL dari config/app.php
        'webhook_url' => env('TELEGRAM_WEBHOOK_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenAI (LLM & Vision)
    |--------------------------------------------------------------------------
    |
    | Service client untuk memanggil OpenAI. Kamu cukup mengisi OPENAI_API_KEY
    | (dan optional OPENAI_MODEL / OPENAI_BASE_URL) di .env, lalu gunakan
    | App\Services\OpenAiClient di layer lain (budget coach, receipt OCR, dll).
    |
    */

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com'),
        // Model default untuk teks. Bisa diganti di .env tanpa ubah kode.
        'default_model' => env('OPENAI_MODEL', 'gpt-4.1-mini'),
    ],

];
