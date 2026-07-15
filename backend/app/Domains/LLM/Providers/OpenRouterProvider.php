<?php

namespace App\Domains\LLM\Providers;

use App\Domains\LLM\Interfaces\LLMProviderInterface;
use Illuminate\Support\Facades\Http;

class OpenRouterProvider implements LLMProviderInterface
{
    protected string $apiKey;
    protected string $model;

    public function __construct(string $apiKey, string $model = 'google/gemini-pro-1.5-exp')
    {
        $this->apiKey = $apiKey;
        $this->model = $model;
    }

    public function generateText(string $prompt, array $options = []): string
    {
        return $this->generateChat([['role' => 'user', 'content' => $prompt]], $options);
    }

    public function generateChat(array $messages, array $options = []): string
    {
        $response = Http::withToken($this->apiKey)
            ->withHeaders([
                'HTTP-Referer' => config('app.url'),
                'X-Title' => 'FoodERP Pro',
            ])
            ->post("https://openrouter.ai/api/v1/chat/completions", [
                'model' => $this->model,
                'messages' => $messages,
            ]);

        if ($response->failed()) {
            throw new \Exception("OpenRouter API request failed: " . $response->body());
        }

        return $response->json('choices.0.message.content');
    }

    public function getName(): string
    {
        return 'OpenRouter';
    }
}
