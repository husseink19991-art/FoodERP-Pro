<?php

namespace App\Domains\LLM\Services;

use App\Domains\LLM\Interfaces\LLMProviderInterface;
use Illuminate\Support\Facades\Log;

class LLMFailoverService
{
    /** @var LLMProviderInterface[] */
    protected array $providers = [];

    public function __construct(array $providers)
    {
        $this->providers = $providers;
    }

    /**
     * Generate text with failover logic
     */
    public function generateText(string $prompt, array $options = []): string
    {
        $errors = [];

        foreach ($this->providers as $provider) {
            try {
                return $provider->generateText($prompt, $options);
            } catch (\Exception $e) {
                $errors[] = "{$provider->getName()}: {$e->getMessage()}";
                Log::warning("LLM Failover: {$provider->getName()} failed. Trying next provider. Error: " . $e->getMessage());
            }
        }

        throw new \Exception("All LLM providers failed: " . implode('; ', $errors));
    }

    /**
     * Generate chat with failover logic
     */
    public function generateChat(array $messages, array $options = []): string
    {
        $errors = [];

        foreach ($this->providers as $provider) {
            try {
                return $provider->generateChat($messages, $options);
            } catch (\Exception $e) {
                $errors[] = "{$provider->getName()}: {$e->getMessage()}";
                Log::warning("LLM Failover: {$provider->getName()} failed. Trying next provider. Error: " . $e->getMessage());
            }
        }

        throw new \Exception("All LLM providers failed: " . implode('; ', $errors));
    }
}
