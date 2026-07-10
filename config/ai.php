<?php

return [
    'provider' => env('AI_PROVIDER', 'openrouter'),
    'base_url' => env('AI_BASE_URL', 'https://openrouter.ai/api/v1'),
    'model' => env('AI_MODEL', 'google/gemini-2.5-flash-lite'),
    'api_key' => env('AI_API_KEY'),
    'max_tokens' => (int) env('AI_MAX_TOKENS', 4096),

    'timeout' => (int) env('AI_TIMEOUT', 60),
    'connect_timeout' => (int) env('AI_CONNECT_TIMEOUT', 10),
    'preview_length' => (int) env('AI_LOG_PREVIEW_LENGTH', 500),

    'openrouter' => [
        'timeout' => (int) env('AI_OPENROUTER_TIMEOUT', 120),
        'response_format' => ['type' => 'json_object'],
        'referer' => env('AI_OPENROUTER_REFERER', env('APP_URL')),
        'title' => env('AI_OPENROUTER_TITLE', env('APP_NAME')),
    ],

    'lmstudio' => [
        'timeout' => (int) env('AI_LMSTUDIO_TIMEOUT', 300),
        'response_format' => ['type' => 'text'],
    ],

    'rag' => [
        'enabled' => (bool) env('RAG_ENABLED', false),
        'service_url' => env('RAG_SERVICE_URL', 'http://rag-service:8000'),
    ],
];
