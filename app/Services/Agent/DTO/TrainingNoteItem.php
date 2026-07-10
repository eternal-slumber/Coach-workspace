<?php

namespace App\Services\Agent\DTO;

readonly class TrainingNoteItem
{
    /**
     * @param  list<string>  $tags
     */
    public function __construct(
        public int $trainingPlanId,
        public string $intensity,
        public string $result,
        public array $tags,
        public string $note,
    ) {}
}
