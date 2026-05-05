<?php

return [
    'default_provider' => env('AI_DEFAULT_PROVIDER', 'deepseek'),
    'max_messages_per_hour' => (int) env('AI_MAX_MESSAGES_PER_HOUR', 20),
    'max_conversations_per_day' => (int) env('AI_MAX_CONVERSATIONS_PER_DAY', 20),
    'max_tokens_per_response' => (int) env('AI_MAX_TOKENS_PER_RESPONSE', 800),
    'context_enabled' => (bool) env('AI_CONTEXT_ENABLED', true),
    'deepseek' => [
        'api_key' => env('DEEPSEEK_API_KEY'),
        'base_url' => env('DEEPSEEK_BASE_URL', 'https://api.deepseek.com'),
        'model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
    ],
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com'),
        'model' => env('GEMINI_MODEL', 'gemini-1.5-flash'),
    ],
];
