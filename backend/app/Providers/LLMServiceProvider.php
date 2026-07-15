<?php

namespace App\Providers;

use App\Domains\LLM\Providers\GeminiProvider;
use App\Domains\LLM\Providers\GroqProvider;
use App\Domains\LLM\Providers\OpenRouterProvider;
use App\Domains\LLM\Providers\HuggingFaceProvider;
use App\Domains\LLM\Services\LLMFailoverService;
use Illuminate\Support\ServiceProvider;

class LLMServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LLMFailoverService::class, function ($app) {
            $providers = [];

            // Primary: Google Gemini
            if (config('services.gemini.key')) {
                $providers[] = new GeminiProvider(config('services.gemini.key'));
            }

            // Failover 1: Groq
            if (config('services.groq.key')) {
                $providers[] = new GroqProvider(config('services.groq.key'));
            }

            // Failover 2: OpenRouter
            if (config('services.openrouter.key')) {
                $providers[] = new OpenRouterProvider(config('services.openrouter.key'));
            }

            // Failover 3: Hugging Face
            if (config('services.huggingface.key')) {
                $providers[] = new HuggingFaceProvider(config('services.huggingface.key'));
            }

            return new LLMFailoverService($providers);
        });
    }

    public function boot(): void
    {
        //
    }
}
