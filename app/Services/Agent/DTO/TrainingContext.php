<?php

namespace App\Services\Agent\DTO;

use Carbon\CarbonImmutable;

readonly class TrainingContext
{
    /**
     * @param  list<TrainingHistoryItem>  $history
     * @param  list<TrainingNoteItem>  $notes
     * @param  list<TrainingMemoryItem>  $memories
     * @param  list<TrainingExerciseItem>  $exercises
     */
    public function __construct(
        public int $userId,
        public string $userName,
        public int $scheduledTrainingId,
        public CarbonImmutable $scheduledTrainingStartsAt,
        public int $scheduledTrainingDurationMinutes,
        public string $scheduledTrainingLocation,
        public ?string $scheduledTrainingNotes,
        public TrainingContextTarget $target,
        public array $history,
        public array $notes,
        public array $memories,
        public array $exercises,
    ) {}
}
