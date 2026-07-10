<?php

namespace App\Services\Agent\DTO;

final readonly class ValidatedTrainingPlanBlock
{
    /**
     * @param  list<ValidatedTrainingPlanExercise>  $exercises
     */
    public function __construct(
        public string $name,
        public int $durationMinutes,
        public int $position,
        public ?string $notes,
        public array $exercises,
    ) {}
}
