<?php

namespace App\Domains\LLM\Providers;

use App\Domains\LLM\Interfaces\LLMProviderInterface;
use Illuminate\Support\Facades\Http;

class GeminiProvider implements LLMProviderInterface
{
    protected string $apiKey;
    protected string $model;

    public function __construct(string $apiKey, string $model = 'gemini-1.5-pro')
    {
        $this->apiKey = $apiKey;
        $this->model = $model;
    }

    public function generateText(string $prompt, array $options = []): string
    {
        $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}", [
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ],
            'generationConfig' => $options,
        ]);

        if ($response->failed()) {
            throw new \Exception("Gemini API request failed: " . $response->body());
        }

        return $response->json('candidates.0.content.parts.0.text');
    }

    public function generateChat(array $messages, array $options = []): string
    {
        // Gemini chat implementation
        $contents = array_map(function($msg) {
            return [
                'role' => $msg['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $msg['content']]]
            ];
        }, $messages);

        $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}", [
            'contents' => $contents,
            'generationConfig' => $options,
        ]);

        if ($response->failed()) {
            throw new \Exception("Gemini API request failed: " . $response->body());
        }

        return $response->json('candidates.0.content.parts.0.text');
    }

    public function getName(): string
    {
        return 'Google Gemini';
    }
}
