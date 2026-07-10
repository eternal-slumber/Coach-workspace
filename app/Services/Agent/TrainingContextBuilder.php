<?php

namespace App\Services\Agent;

use App\Models\AgentMemory;
use App\Models\Exercise;
use App\Models\ScheduledTraining;
use App\Models\Trainee;
use App\Models\TrainingGroup;
use App\Models\TrainingNote;
use App\Models\TrainingPlan;
use App\Models\TrainingPlanBlock;
use App\Models\TrainingPlanExercise;
use App\Models\User;
use App\Services\Agent\DTO\TrainingContext;
use App\Services\Agent\DTO\TrainingContextTarget;
use App\Services\Agent\DTO\TrainingExerciseItem;
use App\Services\Agent\DTO\TrainingHistoryItem;
use App\Services\Agent\DTO\TrainingMemoryItem;
use App\Services\Agent\DTO\TrainingNoteItem;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use LogicException;

class TrainingContextBuilder
{
    public function buildForScheduledTraining(
        User $user,
        ScheduledTraining $scheduledTraining,
    ): TrainingContext {
        if ($scheduledTraining->user_id !== $user->id) {
            throw new AuthorizationException('This scheduled training belongs to another user.');
        }

        $scheduledTraining->load([
            'trainee:id,user_id,name,age,level,goal,restrictions',
            'trainingGroup:id,user_id,name,sport_type,age_range,level,goal,restrictions',
        ]);

        [$target, $targetColumn] = $this->resolveTarget($user, $scheduledTraining);
        $history = $this->history($user, $targetColumn, $target->id);

        return new TrainingContext(
            userId: $user->id,
            userName: $user->name,
            scheduledTrainingId: $scheduledTraining->id,
            scheduledTrainingStartsAt: $scheduledTraining->starts_at,
            scheduledTrainingDurationMinutes: (int) $scheduledTraining->starts_at
                ->diffInMinutes($scheduledTraining->ends_at),
            scheduledTrainingLocation: $scheduledTraining->location,
            scheduledTrainingNotes: $scheduledTraining->notes,
            target: $target,
            history: $history,
            notes: array_values(array_filter(
                array_map(
                    fn (TrainingHistoryItem $historyItem): ?TrainingNoteItem => $historyItem->note,
                    $history,
                ),
            )),
            memories: $this->memories($user, $targetColumn, $target->id),
            exercises: $this->exercises($user),
        );
    }

    /**
     * @return array{TrainingContextTarget, 'trainee_id'|'training_group_id'}
     */
    private function resolveTarget(
        User $user,
        ScheduledTraining $scheduledTraining,
    ): array {
        if ($scheduledTraining->trainee instanceof Trainee) {
            $this->ensureTargetOwnership($user, $scheduledTraining->trainee->user_id);

            return [
                new TrainingContextTarget(
                    type: 'trainee',
                    id: $scheduledTraining->trainee->id,
                    name: $scheduledTraining->trainee->name,
                    level: $scheduledTraining->trainee->level,
                    goal: $scheduledTraining->trainee->goal,
                    restrictions: $scheduledTraining->trainee->restrictions,
                    age: $scheduledTraining->trainee->age,
                ),
                'trainee_id',
            ];
        }

        if ($scheduledTraining->trainingGroup instanceof TrainingGroup) {
            $this->ensureTargetOwnership($user, $scheduledTraining->trainingGroup->user_id);

            return [
                new TrainingContextTarget(
                    type: 'training_group',
                    id: $scheduledTraining->trainingGroup->id,
                    name: $scheduledTraining->trainingGroup->name,
                    level: $scheduledTraining->trainingGroup->level,
                    goal: $scheduledTraining->trainingGroup->goal,
                    restrictions: $scheduledTraining->trainingGroup->restrictions,
                    ageRange: $scheduledTraining->trainingGroup->age_range,
                    sportType: $scheduledTraining->trainingGroup->sport_type,
                ),
                'training_group_id',
            ];
        }

        throw new LogicException('Scheduled training must have a trainee or a training group.');
    }

    private function ensureTargetOwnership(User $user, int $ownerId): void
    {
        if ($ownerId !== $user->id) {
            throw new AuthorizationException('The scheduled training target belongs to another user.');
        }
    }

    /**
     * @param  'trainee_id'|'training_group_id'  $targetColumn
     * @return list<TrainingHistoryItem>
     */
    private function history(User $user, string $targetColumn, int $targetId): array
    {
        return array_values(TrainingPlan::query()
            ->select([
                'id',
                'user_id',
                'scheduled_training_id',
                'title',
                'goal',
                'total_duration_minutes',
                'status',
            ])
            ->where('user_id', $user->id)
            ->where($targetColumn, $targetId)
            ->completed()
            ->whereHas('scheduledTraining', fn (Builder $query): Builder => $query
                ->where('user_id', $user->id))
            ->with([
                'scheduledTraining:id,user_id,starts_at',
                'blocks:id,training_plan_id,name,duration_minutes,position,notes',
                'blocks.exercises:id,training_plan_block_id,name,description,duration_minutes,sets,repetitions,rest_seconds,position,notes',
                'trainingNote:id,user_id,training_plan_id,intensity,result,tags,note',
            ])
            ->latestScheduled()
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(fn (TrainingPlan $plan): TrainingHistoryItem => $this->historyItem($plan))
            ->values()
            ->all());
    }

    private function historyItem(TrainingPlan $plan): TrainingHistoryItem
    {
        if ($plan->scheduledTraining === null) {
            throw new LogicException('Completed training plan must have a scheduled training.');
        }

        return new TrainingHistoryItem(
            id: $plan->id,
            title: $plan->title,
            goal: $plan->goal,
            totalDurationMinutes: $plan->total_duration_minutes,
            startsAt: $plan->scheduledTraining->starts_at,
            blocks: array_values($plan->blocks
                ->map(fn (TrainingPlanBlock $block): array => [
                    'name' => $block->name,
                    'duration_minutes' => $block->duration_minutes,
                    'notes' => $block->notes,
                    'exercises' => array_values($block->exercises
                        ->map(fn (TrainingPlanExercise $exercise): array => [
                            'name' => $exercise->name,
                            'description' => $exercise->description,
                            'duration_minutes' => $exercise->duration_minutes ?? 0,
                            'sets' => $exercise->sets,
                            'repetitions' => $exercise->repetitions,
                            'rest_seconds' => $exercise->rest_seconds,
                            'notes' => $exercise->notes,
                        ])
                        ->values()
                        ->all()),
                ])
                ->values()
                ->all()),
            note: $plan->trainingNote === null || $plan->trainingNote->user_id !== $plan->user_id
                ? null
                : $this->noteItem($plan->trainingNote),
        );
    }

    private function noteItem(TrainingNote $trainingNote): TrainingNoteItem
    {
        return new TrainingNoteItem(
            trainingPlanId: $trainingNote->training_plan_id,
            intensity: $trainingNote->intensity,
            result: $trainingNote->result,
            tags: $this->stringList($trainingNote->tags),
            note: $trainingNote->note,
        );
    }

    /**
     * @param  'trainee_id'|'training_group_id'  $targetColumn
     * @return list<TrainingMemoryItem>
     */
    private function memories(User $user, string $targetColumn, int $targetId): array
    {
        return array_values(AgentMemory::query()
            ->select(['id', 'type', 'content', 'importance'])
            ->where('user_id', $user->id)
            ->where($targetColumn, $targetId)
            ->where('is_active', true)
            ->orderByDesc('importance')
            ->latest('id')
            ->get()
            ->map(fn (AgentMemory $memory): TrainingMemoryItem => new TrainingMemoryItem(
                id: $memory->id,
                type: $memory->type,
                content: $memory->content,
                importance: $memory->importance,
            ))
            ->values()
            ->all());
    }

    /** @return list<TrainingExerciseItem> */
    private function exercises(User $user): array
    {
        return array_values(Exercise::query()
            ->visibleTo($user)
            ->select([
                'id',
                'name',
                'description',
                'goal',
                'difficulty',
                'equipment',
                'duration_minutes',
                'muscle_groups',
                'load_type',
                'movement_pattern',
                'contraindications',
                'age_min',
                'age_max',
                'tags',
                'is_system',
            ])
            ->orderByDesc('is_system')
            ->orderBy('name')
            ->limit(50)
            ->get()
            ->map(fn (Exercise $exercise): TrainingExerciseItem => new TrainingExerciseItem(
                id: $exercise->id,
                name: $exercise->name,
                description: $exercise->description,
                goal: $exercise->goal,
                difficulty: $exercise->difficulty,
                equipment: $exercise->equipment,
                durationMinutes: $exercise->duration_minutes,
                muscleGroups: $this->stringList($exercise->muscle_groups),
                loadType: $exercise->load_type,
                movementPattern: $exercise->movement_pattern,
                contraindications: $exercise->contraindications,
                ageMin: $exercise->age_min,
                ageMax: $exercise->age_max,
                tags: $this->stringList($exercise->tags),
                isSystem: $exercise->is_system,
            ))
            ->values()
            ->all());
    }

    /** @return list<string> */
    private function stringList(mixed $values): array
    {
        if (! is_array($values)) {
            return [];
        }

        return array_values(array_filter(
            $values,
            fn (mixed $value): bool => is_string($value) && $value !== '',
        ));
    }
}
