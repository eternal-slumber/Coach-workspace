<?php

namespace App\Services\Agent\DTO;

readonly class TrainingContextTarget
{
    public function __construct(
        public string $type,
        public int $id,
        public string $name,
        public string $level,
        public string $goal,
        public ?string $restrictions,
        public ?int $age = null,
        public ?string $ageRange = null,
        public ?string $sportType = null,
    ) {}
}
