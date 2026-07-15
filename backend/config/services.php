<?php

return [
    // ... existing services

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
    ],

    'groq' => [
        'key' => env('GROQ_API_KEY'),
    ],

    'openrouter' => [
        'key' => env('OPENROUTER_API_KEY'),
    ],

    'huggingface' => [
        'key' => env('HUGGINGFACE_API_KEY'),
    ],
];
