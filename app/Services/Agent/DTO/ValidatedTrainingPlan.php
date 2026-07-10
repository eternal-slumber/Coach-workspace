<?php

namespace App\Services\Agent\DTO;

final readonly class ValidatedTrainingPlan
{
    /**
     * @param  list<string>  $warnings
     * @param  list<ValidatedTrainingPlanBlock>  $blocks
     */
    public function __construct(
        public string $title,
        public string $goal,
        public int $totalDurationMinutes,
        public ?string $aiReasoning,
        public array $warnings,
        public array $blocks,
    ) {}
}
