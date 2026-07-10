<?php

namespace App\Services\Agent;

use App\Services\Agent\DTO\TrainingContext;
use App\Services\Agent\DTO\TrainingExerciseItem;
use App\Services\Agent\DTO\ValidatedTrainingPlan;
use App\Services\Agent\DTO\ValidatedTrainingPlanBlock;
use App\Services\Agent\DTO\ValidatedTrainingPlanExercise;
use JsonException;

class TrainingPlanValidator
{
    private const int DURATION_TOLERANCE_MINUTES = 5;

    public function validate(string $json, TrainingContext $context): ValidatedTrainingPlan
    {
        $payload = $this->decode($json);
        $title = $this->requiredString($payload, 'title', 'title', 255);
        $goal = $this->requiredString($payload, 'goal', 'goal');
        $totalDuration = $this->requiredPositiveInteger(
            $payload,
            'total_duration_minutes',
            'total_duration_minutes',
        );

        $this->validateScheduledDuration($totalDuration, $context);

        $blockPayloads = $this->requiredList($payload, 'blocks', 'blocks');

        if ($blockPayloads === []) {
            $this->invalid('blocks', 'должен содержать хотя бы один блок.');
        }

        $availableExercises = $this->availableExercises($context);
        $blocks = [];

        foreach ($blockPayloads as $index => $blockPayload) {
            $blocks[] = $this->validateBlock(
                $blockPayload,
                $index,
                $availableExercises,
            );
        }

        $this->validateBlockDuration($totalDuration, $blocks);

        if (! array_key_exists('ai_reasoning', $payload)) {
            $this->invalid('ai_reasoning', 'является обязательным полем.');
        }

        return new ValidatedTrainingPlan(
            title: $title,
            goal: $goal,
            totalDurationMinutes: $totalDuration,
            aiReasoning: $this->nullableString($payload, 'ai_reasoning', 'ai_reasoning'),
            warnings: $this->warnings($payload),
            blocks: $blocks,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(string $json): array
    {
        try {
            $decoded = json_decode(
                $this->jsonObjectFromResponse($json),
                true,
                512,
                JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $exception) {
            throw new InvalidTrainingPlanResponseException(
                'Ответ AI не является валидным JSON.',
                previous: $exception,
            );
        }

        return $this->object($decoded, 'root');
    }

    private function jsonObjectFromResponse(string $response): string
    {
        $json = trim($response);

        if (str_starts_with($json, '```')) {
            $json = preg_replace('/^```(?:json)?\s*/iu', '', $json) ?? $json;
            $json = preg_replace('/\s*```$/u', '', $json) ?? $json;
            $json = trim($json);
        }

        if (str_starts_with($json, '{') && str_ends_with($json, '}')) {
            return $json;
        }

        $firstBrace = mb_strpos($json, '{');
        $lastBrace = mb_strrpos($json, '}');

        if ($firstBrace === false || $lastBrace === false || $lastBrace <= $firstBrace) {
            return $json;
        }

        return mb_substr($json, $firstBrace, $lastBrace - $firstBrace + 1);
    }

    /**
     * @param  array<int, TrainingExerciseItem>  $availableExercises
     */
    private function validateBlock(
        mixed $blockPayload,
        int $index,
        array $availableExercises,
    ): ValidatedTrainingPlanBlock {
        $path = "blocks.{$index}";
        $block = $this->object($blockPayload, $path);
        $position = $this->requiredPositiveInteger($block, 'position', "{$path}.position");

        $position = $index + 1;

        $exercisePayloads = $this->requiredList($block, 'exercises', "{$path}.exercises");

        if ($exercisePayloads === []) {
            $this->invalid("{$path}.exercises", 'должен содержать хотя бы одно упражнение.');
        }

        $exercises = [];

        foreach ($exercisePayloads as $exerciseIndex => $exercisePayload) {
            $exercises[] = $this->validateExercise(
                $exercisePayload,
                $index,
                $exerciseIndex,
                $availableExercises,
            );
        }

        return new ValidatedTrainingPlanBlock(
            name: $this->requiredString($block, 'name', "{$path}.name", 255),
            durationMinutes: $this->requiredPositiveInteger(
                $block,
                'duration_minutes',
                "{$path}.duration_minutes",
            ),
            position: $position,
            notes: $this->nullableString($block, 'notes', "{$path}.notes"),
            exercises: $exercises,
        );
    }

    /**
     * @param  array<int, TrainingExerciseItem>  $availableExercises
     */
    private function validateExercise(
        mixed $exercisePayload,
        int $blockIndex,
        int $exerciseIndex,
        array $availableExercises,
    ): ValidatedTrainingPlanExercise {
        $path = "blocks.{$blockIndex}.exercises.{$exerciseIndex}";
        $exercise = $this->object($exercisePayload, $path);
        $exerciseId = $this->requiredPositiveInteger(
            $exercise,
            'exercise_id',
            "{$path}.exercise_id",
        );

        if (! isset($availableExercises[$exerciseId])) {
            $this->invalid(
                "{$path}.exercise_id",
                "содержит недоступное упражнение с id {$exerciseId}.",
            );
        }

        $position = $this->requiredPositiveInteger($exercise, 'position', "{$path}.position");

        $position = $exerciseIndex + 1;

        return new ValidatedTrainingPlanExercise(
            exerciseId: $exerciseId,
            name: $availableExercises[$exerciseId]->name,
            description: $availableExercises[$exerciseId]->description,
            durationMinutes: $this->nullableInteger(
                $exercise,
                'duration_minutes',
                "{$path}.duration_minutes",
                minimum: 1,
            ),
            sets: $this->nullableInteger($exercise, 'sets', "{$path}.sets", minimum: 1),
            repetitions: $this->nullableRepetitions($exercise, "{$path}.repetitions"),
            restSeconds: $this->nullableInteger(
                $exercise,
                'rest_seconds',
                "{$path}.rest_seconds",
                minimum: 0,
            ),
            position: $position,
            notes: $this->nullableString($exercise, 'notes', "{$path}.notes"),
        );
    }

    /**
     * @return array<int, TrainingExerciseItem>
     */
    private function availableExercises(TrainingContext $context): array
    {
        $availableExercises = [];

        foreach ($context->exercises as $exercise) {
            $availableExercises[$exercise->id] = $exercise;
        }

        return $availableExercises;
    }

    /**
     * @param  list<ValidatedTrainingPlanBlock>  $blocks
     */
    private function validateBlockDuration(int $totalDuration, array $blocks): void
    {
        $blockDuration = array_sum(array_map(
            fn (ValidatedTrainingPlanBlock $block): int => $block->durationMinutes,
            $blocks,
        ));

        if (abs($totalDuration - $blockDuration) > self::DURATION_TOLERANCE_MINUTES) {
            $this->invalid(
                'blocks',
                "суммарная длительность {$blockDuration} минут слишком сильно отличается "
                    ."от total_duration_minutes {$totalDuration}.",
            );
        }
    }

    private function validateScheduledDuration(int $totalDuration, TrainingContext $context): void
    {
        if (abs($totalDuration - $context->scheduledTrainingDurationMinutes) > self::DURATION_TOLERANCE_MINUTES) {
            $this->invalid(
                'total_duration_minutes',
                "должен соответствовать длительности расписания {$context->scheduledTrainingDurationMinutes} минут.",
            );
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<string>
     */
    private function warnings(array $payload): array
    {
        if (! array_key_exists('warnings', $payload)) {
            return [];
        }

        $warnings = $this->list($payload['warnings'], 'warnings');
        $validatedWarnings = [];

        foreach ($warnings as $index => $warning) {
            if (! is_string($warning) || trim($warning) === '') {
                $this->invalid("warnings.{$index}", 'должен быть непустой строкой.');
            }

            $validatedWarnings[] = trim($warning);
        }

        return $validatedWarnings;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function requiredString(
        array $payload,
        string $key,
        string $path,
        ?int $maximumLength = null,
    ): string {
        if (! array_key_exists($key, $payload) || ! is_string($payload[$key])) {
            $this->invalid($path, 'должен быть строкой.');
        }

        $value = trim($payload[$key]);

        if ($value === '') {
            $this->invalid($path, 'не должен быть пустым.');
        }

        if ($maximumLength !== null && mb_strlen($value) > $maximumLength) {
            $this->invalid($path, "не должен быть длиннее {$maximumLength} символов.");
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function requiredPositiveInteger(array $payload, string $key, string $path): int
    {
        if (! array_key_exists($key, $payload) || ! is_int($payload[$key]) || $payload[$key] < 1) {
            $this->invalid($path, 'должен быть положительным целым числом.');
        }

        return $payload[$key];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function nullableInteger(
        array $payload,
        string $key,
        string $path,
        int $minimum,
    ): ?int {
        if (! array_key_exists($key, $payload) || $payload[$key] === null) {
            return null;
        }

        if (! is_int($payload[$key]) || $payload[$key] < $minimum) {
            $this->invalid($path, "должен быть целым числом не меньше {$minimum} или null.");
        }

        return $payload[$key];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function nullableString(array $payload, string $key, string $path): ?string
    {
        if (! array_key_exists($key, $payload) || $payload[$key] === null) {
            return null;
        }

        if (! is_string($payload[$key])) {
            $this->invalid($path, 'должен быть строкой или null.');
        }

        return trim($payload[$key]) === '' ? null : trim($payload[$key]);
    }

    /**
     * @param  array<string, mixed>  $exercise
     */
    private function nullableRepetitions(array $exercise, string $path): ?string
    {
        if (! array_key_exists('repetitions', $exercise) || $exercise['repetitions'] === null) {
            return null;
        }

        $repetitions = $exercise['repetitions'];

        if (! is_string($repetitions) && ! is_int($repetitions) && ! is_float($repetitions)) {
            $this->invalid($path, 'должен быть строкой, числом или null.');
        }

        $normalized = trim((string) $repetitions);

        if ($normalized === '') {
            $this->invalid($path, 'не должен быть пустым.');
        }

        if (mb_strlen($normalized) > 255) {
            $this->invalid($path, 'не должен быть длиннее 255 символов.');
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<mixed>
     */
    private function requiredList(array $payload, string $key, string $path): array
    {
        if (! array_key_exists($key, $payload)) {
            $this->invalid($path, 'является обязательным полем.');
        }

        return $this->list($payload[$key], $path);
    }

    /** @return list<mixed> */
    private function list(mixed $value, string $path): array
    {
        if (! is_array($value) || ! array_is_list($value)) {
            $this->invalid($path, 'должен быть массивом-списком.');
        }

        return $value;
    }

    /** @return array<string, mixed> */
    private function object(mixed $value, string $path): array
    {
        if (! is_array($value) || array_is_list($value)) {
            $this->invalid($path, 'должен быть JSON-объектом.');
        }

        $object = [];

        foreach ($value as $key => $item) {
            if (! is_string($key)) {
                $this->invalid($path, 'содержит недопустимый ключ.');
            }

            $object[$key] = $item;
        }

        return $object;
    }

    private function invalid(string $path, string $message): never
    {
        throw new InvalidTrainingPlanResponseException(
            "Некорректный ответ AI: {$path} {$message}",
        );
    }
}
