<?php

namespace App\Domains\LLM\Providers;

use App\Domains\LLM\Interfaces\LLMProviderInterface;
use Illuminate\Support\Facades\Http;

class HuggingFaceProvider implements LLMProviderInterface
{
    protected string $apiKey;
    protected string $model;

    public function __construct(string $apiKey, string $model = 'mistralai/Mistral-7B-Instruct-v0.2')
    {
        $this->apiKey = $apiKey;
        $this->model = $model;
    }

    public function generateText(string $prompt, array $options = []): string
    {
        $response = Http::withToken($this->apiKey)
            ->post("https://api-inference.huggingface.co/models/{$this->model}", [
                'inputs' => $prompt,
                'parameters' => $options,
            ]);

        if ($response->failed()) {
            throw new \Exception("HuggingFace API request failed: " . $response->body());
        }

        // HF returns array of results
        return $response->json('0.generated_text') ?? $response->json('generated_text');
    }

    public function generateChat(array $messages, array $options = []): string
    {
        // Simplistic conversion for HF inference API which often expects raw strings
        $prompt = "";
        foreach ($messages as $msg) {
            $prompt .= "{$msg['role']}: {$msg['content']}\n";
        }
        $prompt .= "assistant: ";

        return $this->generateText($prompt, $options);
    }

    public function getName(): string
    {
        return 'Hugging Face';
    }
}
