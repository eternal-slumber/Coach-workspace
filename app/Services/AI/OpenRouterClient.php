<?php

namespace App\Services\AI;

class OpenRouterClient extends OpenAiCompatibleClient
{
    public function provider(): string
    {
        return 'openrouter';
    }

    /**
     * @return array<string, string>
     */
    protected function headers(): array
    {
        $headers = parent::headers();
        $openRouterConfig = $this->config['openrouter'] ?? [];
        $referer = is_array($openRouterConfig) ? $openRouterConfig['referer'] ?? null : null;
        $title = is_array($openRouterConfig) ? $openRouterConfig['title'] ?? null : null;

        if (is_string($referer) && $referer !== '') {
            $headers['HTTP-Referer'] = $referer;
        }

        if (is_string($title) && $title !== '') {
            $headers['X-Title'] = $title;
        }

        return $headers;
    }

    protected function requiresApiKey(): bool
    {
        return true;
    }
}
