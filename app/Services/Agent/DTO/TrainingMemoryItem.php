<?php

namespace App\Services\Agent\DTO;

readonly class TrainingMemoryItem
{
    public function __construct(
        public int $id,
        public string $type,
        public string $content,
        public int $importance,
    ) {}
}
