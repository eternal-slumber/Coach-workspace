<?php

namespace App\Services\AI;

readonly class AiResponse
{
    /**
     * @param  array<string, mixed>|null  $raw
     * @param  array<string, mixed>|null  $usage
     */
    public function __construct(
        public string $content,
        public string $model,
        public string $provider,
        public ?array $raw = null,
        public ?array $usage = null,
    ) {}
}
