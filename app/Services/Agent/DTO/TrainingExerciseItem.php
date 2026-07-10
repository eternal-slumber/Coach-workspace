<?php

namespace App\Services\Agent\DTO;

readonly class TrainingExerciseItem
{
    /**
     * @param  list<string>  $muscleGroups
     * @param  list<string>  $tags
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $description,
        public string $goal,
        public string $difficulty,
        public ?string $equipment,
        public ?int $durationMinutes,
        public array $muscleGroups,
        public ?string $loadType,
        public ?string $movementPattern,
        public ?string $contraindications,
        public ?int $ageMin,
        public ?int $ageMax,
        public array $tags,
        public bool $isSystem,
    ) {}
}
