<?php

namespace App\Services\Agent\DTO;

use Carbon\CarbonImmutable;

readonly class TrainingHistoryItem
{
    /**
     * @param  list<array{
     *     name: string,
     *     duration_minutes: int,
     *     notes: string|null,
     *     exercises: list<array{
     *         name: string,
     *         description: string|null,
     *         duration_minutes: int,
     *         sets: int|null,
     *         repetitions: string|null,
     *         rest_seconds: int|null,
     *         notes: string|null
     *     }>
     * }>  $blocks
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $goal,
        public int $totalDurationMinutes,
        public CarbonImmutable $startsAt,
        public array $blocks,
        public ?TrainingNoteItem $note,
    ) {}
}
