<?php

namespace App\Domains\LLM\Interfaces;

interface LLMProviderInterface
{
    public function generateText(string $prompt, array $options = []): string;
    public function generateChat(array $messages, array $options = []): string;
    public function getName(): string;
}
