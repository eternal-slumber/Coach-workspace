<?php

namespace App\Http\Requests;

use App\Models\Exercise;
use App\Models\TrainingPlan;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

abstract class TrainingPlanRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function planRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'goal' => ['required', 'string', 'max:5000'],
            'total_duration_minutes' => ['required', 'integer', 'between:1,1440'],
            'status' => ['required', Rule::in($this->allowedStatuses())],
            'notes' => ['nullable', 'string', 'max:10000'],
            'blocks' => ['required', 'array', 'min:1', 'max:20'],
            'blocks.*.name' => ['required', 'string', 'max:255'],
            'blocks.*.duration_minutes' => ['required', 'integer', 'between:1,1440'],
            'blocks.*.notes' => ['nullable', 'string', 'max:5000'],
            'blocks.*.exercises' => ['present', 'array', 'max:50'],
            'blocks.*.exercises.*.exercise_id' => ['nullable', 'integer'],
            'blocks.*.exercises.*.name' => ['required', 'string', 'max:255'],
            'blocks.*.exercises.*.description' => ['nullable', 'string', 'max:10000'],
            'blocks.*.exercises.*.duration_minutes' => ['nullable', 'integer', 'between:1,1440'],
            'blocks.*.exercises.*.sets' => ['nullable', 'integer', 'between:1,100'],
            'blocks.*.exercises.*.repetitions' => ['nullable', 'string', 'max:100'],
            'blocks.*.exercises.*.rest_seconds' => ['nullable', 'integer', 'between:0,3600'],
            'blocks.*.exercises.*.notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<callable(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $user = $this->user();

            if ($user === null) {
                return;
            }

            $blocks = $this->input('blocks', []);

            if (! is_array($blocks)) {
                return;
            }

            $visibleExerciseIds = Exercise::query()
                ->visibleTo($user)
                ->whereKey($this->requestedExerciseIds())
                ->pluck('id')
                ->all();

            foreach ($blocks as $blockIndex => $block) {
                if (! is_array($block)) {
                    continue;
                }

                $exercises = $block['exercises'] ?? [];

                if (! is_array($exercises)) {
                    continue;
                }

                foreach ($exercises as $exerciseIndex => $exercise) {
                    if (! is_array($exercise)) {
                        continue;
                    }

                    $exerciseId = $exercise['exercise_id'] ?? null;

                    if ($exerciseId === null) {
                        continue;
                    }

                    if (! in_array((int) $exerciseId, $visibleExerciseIds, true)) {
                        $validator->errors()->add(
                            "blocks.{$blockIndex}.exercises.{$exerciseIndex}.exercise_id",
                            __('The selected exercise is unavailable.'),
                        );
                    }
                }
            }
        }];
    }

    /** @return list<string> */
    protected function allowedStatuses(): array
    {
        return TrainingPlan::EDITABLE_STATUSES;
    }

    /** @return list<int> */
    private function requestedExerciseIds(): array
    {
        $blocks = $this->input('blocks', []);

        if (! is_array($blocks)) {
            return [];
        }

        return array_values(collect($blocks)
            ->filter(fn (mixed $block): bool => is_array($block))
            ->flatMap(fn (array $block): array => $block['exercises'] ?? [])
            ->pluck('exercise_id')
            ->filter(fn (mixed $exerciseId): bool => is_numeric($exerciseId))
            ->map(fn (mixed $exerciseId): int => (int) $exerciseId)
            ->unique()
            ->values()
            ->all());
    }
}
