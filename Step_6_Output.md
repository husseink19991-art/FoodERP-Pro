# FoodERP Pro Enterprise AI Platform - Step 6: AI Innovation & Best Practice Enhancements (LLM Failover Service)

This document details the implementation of the **LLM Failover Service** as part of **Step 6** for the FoodERP Pro Enterprise AI Platform. This service ensures high availability and resilience for AI-powered features by providing a cascading failover mechanism across multiple Large Language Model (LLM) providers.

## 1. LLM Failover Service Architecture

The architecture is designed to abstract the underlying LLM providers behind a common interface and a failover service. This allows the application to seamlessly switch between providers if one becomes unavailable or fails to respond, ensuring continuous operation of AI features.

### 1.1. `LLMProviderInterface.php`

Located at `backend/app/Domains/LLM/Interfaces/LLMProviderInterface.php`, this interface defines the contract that all LLM providers must adhere to. It includes methods for `generateText`, `generateChat`, and `getName`, ensuring a consistent API for the `LLMFailoverService`.

```php
interface LLMProviderInterface
{
    public function generateText(string $prompt, array $options = []): string;
    public function generateChat(array $messages, array $options = []): string;
    public function getName(): string;
}
```

### 1.2. Individual LLM Providers

Each supported LLM (Google Gemini, Groq Cloud, OpenRouter, Hugging Face) has its own implementation of the `LLMProviderInterface`:

-   **`GeminiProvider.php`**: (`backend/app/Domains/LLM/Providers/GeminiProvider.php`) Implements the integration with Google Gemini API, designated as the **Primary** LLM.
-   **`GroqProvider.php`**: (`backend/app/Domains/LLM/Providers/GroqProvider.php`) Implements the integration with Groq Cloud API, serving as **Failover 1**.
-   **`OpenRouterProvider.php`**: (`backend/app/Domains/LLM/Providers/OpenRouterProvider.php`) Implements the integration with OpenRouter Free Models, serving as **Failover 2**.
-   **`HuggingFaceProvider.php`**: (`backend/app/Domains/LLM/Providers/HuggingFaceProvider.php`) Implements the integration with Hugging Face Inference API, serving as **Failover 3**.

Each provider handles its specific API requests, error handling, and response parsing.

### 1.3. `LLMFailoverService.php`

Located at `backend/app/Domains/LLM/Services/LLMFailoverService.php`, this is the core of the failover mechanism. It receives an ordered list of `LLMProviderInterface` implementations and attempts to use them sequentially.

-   **`generateText(string $prompt, array $options = [])`**: Iterates through the configured LLM providers. It attempts to generate text using the current provider. If an exception occurs (e.g., API failure, rate limit), it logs the error and attempts the next provider in the list. If all providers fail, it throws a comprehensive exception.
-   **`generateChat(array $messages, array $options = [])`**: Similar to `generateText`, but designed for conversational AI interactions, also implementing the cascading failover logic.

```php
class LLMFailoverService
{
    protected array $providers = [];

    public function __construct(array $providers)
    {
        $this->providers = $providers;
    }

    public function generateText(string $prompt, array $options = []): string
    {
        // ... failover logic ...
    }

    public function generateChat(array $messages, array $options = []): string
    {
        // ... failover logic ...
    }
}
```

## 2. Configuration and Integration

### 2.1. `LLMServiceProvider.php`

Located at `backend/app/Providers/LLMServiceProvider.php`, this Laravel Service Provider is responsible for binding the `LLMFailoverService` into the application's service container. It dynamically instantiates the LLM providers based on the presence of their respective API keys in the environment configuration.

```php
class LLMServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LLMFailoverService::class, function ($app) {
            $providers = [];

            if (config(\'services.gemini.key\')) {
                $providers[] = new GeminiProvider(config(\'services.gemini.key\'));
            }
            // ... other providers ...

            return new LLMFailoverService($providers);
        });
    }
}
```

### 2.2. `config/services.php`

The `backend/config/services.php` file has been updated to include configuration stubs for each LLM provider. This allows API keys to be managed securely via environment variables (`.env`).

```php
return [
    // ... existing services

    \'gemini\' => [
        \'key\' => env(\'GEMINI_API_KEY\'),
    ],

    \'groq\' => [
        \'key\' => env(\'GROQ_API_KEY\'),
    ],

    \'openrouter\' => [
        \'key\' => env(\'OPENROUTER_API_KEY\'),
    ],

    \'huggingface\' => [
        \'key\' => env(\'HUGGINGFACE_API_KEY\'),
    ],
];
```

## 3. Usage Example

To use the LLM Failover Service within the Laravel application, you can inject it via dependency injection or resolve it from the service container:

```php
use App\Domains\LLM\Services\LLMFailoverService;

class MyAIController extends Controller
{
    protected $llmService;

    public function __construct(LLMFailoverService $llmService)
    {
        $this->llmService = $llmService;
    }

    public function analyzeText(Request $request)
    {
        try {
            $prompt = $request->input(\'text\');
            $result = $thism->llmService->generateText($prompt);
            return response()->json([\'analysis\' => $result]);
        } catch (\Exception $e) {
            return response()->json([\'error\' => $e->getMessage()], 500);
        }
    }
}
```

## 4. Instructions for Local Deployment and Testing

To configure and test the LLM Failover Service:

1.  **Update your `.env` file**: Add the API keys for the desired LLM providers. For example:
    ```dotenv
    GEMINI_API_KEY="your_google_gemini_api_key"
    GROQ_API_KEY="your_groq_api_key"
    OPENROUTER_API_KEY="your_openrouter_api_key"
    HUGGINGFACE_API_KEY="your_huggingface_api_key"
    ```
    *Note: The service will only attempt to use providers for which an API key is configured. If only `GEMINI_API_KEY` is set, it will only try Gemini. If Gemini fails, and `GROQ_API_KEY` is set, it will then try Groq, and so on.*

2.  **Ensure Docker containers are running and updated:**
    ```bash
    cd /home/ubuntu/foodERP
    docker-compose -f docker/docker-compose.yml up --build -d
    ```

3.  **Clear Laravel's configuration cache** (if running locally without Docker rebuild):
    ```bash
    docker exec -it fooderp_backend php artisan config:clear
    ```

4.  **Test the service**: You can create a temporary route or a test command in Laravel to invoke the `LLMFailoverService` and observe its behavior, especially by intentionally invalidating an API key to trigger the failover.

    Example Laravel Artisan Command for testing:
    ```php
    // app/Console/Commands/TestLLMFailover.php
    namespace App\Console\Commands;

    use Illuminate\Console\Command;
    use App\Domains\LLM\Services\LLMFailoverService;

    class TestLLMFailover extends Command
    {
        protected $signature = \'test:llm-failover\';
        protected $description = \'Test the LLM Failover Service\';

        public function handle(LLMFailoverService $llmService)
        {
            $prompt = \'What is the capital of France?\';
            $this->info(\"Testing LLM Failover Service with prompt: \" . $prompt);

            try {
                $response = $llmService->generateText($prompt);
                $this->info(\"Success! Response: \" . $response);
            } catch (\Exception $e) {
                $this->error(\"All LLM providers failed: \" . $e->getMessage());
            }
        }
    }
    ```
    Register this command in `app/Console/Kernel.php` and run `php artisan test:llm-failover` from within the backend Docker container.

This concludes the implementation of the LLM Failover Service. This foundational component is crucial for building resilient AI-powered features within the FoodERP Pro platform. Please review and confirm before we proceed with further AI enhancements.
