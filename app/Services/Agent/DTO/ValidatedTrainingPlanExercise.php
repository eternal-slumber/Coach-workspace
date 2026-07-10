<?php

namespace App\Services\Agent\DTO;

final readonly class ValidatedTrainingPlanExercise
{
    public function __construct(
        public int $exerciseId,
        public string $name,
        public string $description,
        public ?int $durationMinutes,
        public ?int $sets,
        public ?string $repetitions,
        public ?int $restSeconds,
        public int $position,
        public ?string $notes,
    ) {}
}
