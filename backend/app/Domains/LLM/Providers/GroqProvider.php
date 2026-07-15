<?php

namespace App\Domains\LLM\Providers;

use App\Domains\LLM\Interfaces\LLMProviderInterface;
use Illuminate\Support\Facades\Http;

class GroqProvider implements LLMProviderInterface
{
    protected string $apiKey;
    protected string $model;

    public function __construct(string $apiKey, string $model = 'llama3-70b-8192')
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
            ->post("https://api.groq.com/openai/v1/chat/completions", [
                'model' => $this->model,
                'messages' => $messages,
                'temperature' => $options['temperature'] ?? 0.7,
            ]);

        if ($response->failed()) {
            throw new \Exception("Groq API request failed: " . $response->body());
        }

        return $response->json('choices.0.message.content');
    }

    public function getName(): string
    {
        return 'Groq Cloud';
    }
}
