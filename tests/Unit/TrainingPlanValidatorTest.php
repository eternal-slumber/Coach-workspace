<?php

use App\Services\Agent\DTO\TrainingContext;
use App\Services\Agent\DTO\TrainingContextTarget;
use App\Services\Agent\DTO\TrainingExerciseItem;
use App\Services\Agent\DTO\ValidatedTrainingPlan;
use App\Services\Agent\InvalidTrainingPlanResponseException;
use App\Services\Agent\TrainingPlanValidator;
use Carbon\CarbonImmutable;

test('valid json returns a safe training plan dto', function () {
    $validatedPlan = (new TrainingPlanValidator)->validate(
        validationJson(validTrainingPlanPayload()),
        validationTrainingContext(),
    );

    expect($validatedPlan)->toBeInstanceOf(ValidatedTrainingPlan::class)
        ->and($validatedPlan->title)->toBe('Координация и ОФП')
        ->and($validatedPlan->goal)->toBe('Развитие координации')
        ->and($validatedPlan->totalDurationMinutes)->toBe(60)
        ->and($validatedPlan->aiReasoning)->toBe('План учитывает ограничения группы.')
        ->and($validatedPlan->warnings)->toBe(['Без длительных прыжков'])
        ->and($validatedPlan->blocks)->toHaveCount(1)
        ->and($validatedPlan->blocks[0]->durationMinutes)->toBe(60)
        ->and($validatedPlan->blocks[0]->exercises)->toHaveCount(1)
        ->and($validatedPlan->blocks[0]->exercises[0]->exerciseId)->toBe(42)
        ->and($validatedPlan->blocks[0]->exercises[0]->name)->toBe('Суставная разминка');
});

test('malformed json is rejected', function () {
    expect(fn () => (new TrainingPlanValidator)->validate(
        '{"title":',
        validationTrainingContext(),
    ))->toThrow(
        InvalidTrainingPlanResponseException::class,
        'Ответ AI не является валидным JSON.',
    );
});

test('json wrapped in markdown or text is accepted', function () {
    $json = validationJson(validTrainingPlanPayload());

    $validatedPlan = (new TrainingPlanValidator)->validate(
        "Вот план:\n```json\n{$json}\n```\nГотово.",
        validationTrainingContext(),
    );

    expect($validatedPlan->title)->toBe('Координация и ОФП');
});

test('positions are normalized from list order', function () {
    $payload = validTrainingPlanPayload();
    $payload['blocks'][0]['position'] = 7;
    $payload['blocks'][0]['exercises'][0]['position'] = 9;

    $validatedPlan = (new TrainingPlanValidator)->validate(
        validationJson($payload),
        validationTrainingContext(),
    );

    expect($validatedPlan->blocks[0]->position)->toBe(1)
        ->and($validatedPlan->blocks[0]->exercises[0]->position)->toBe(1);
});

test('missing position is still rejected', function () {
    $payload = validTrainingPlanPayload();
    unset($payload['blocks'][0]['position']);

    expectInvalidTrainingPlan(
        $payload,
        'blocks.0.position должен быть положительным целым числом.',
    );
});

test('response without blocks is rejected', function () {
    $payload = validTrainingPlanPayload();
    unset($payload['blocks']);

    expectInvalidTrainingPlan($payload, 'blocks является обязательным полем.');
});

test('empty blocks are rejected', function () {
    $payload = validTrainingPlanPayload();
    $payload['blocks'] = [];

    expectInvalidTrainingPlan($payload, 'blocks должен содержать хотя бы один блок.');
});

test('block without exercises is rejected', function () {
    $payload = validTrainingPlanPayload();
    $payload['blocks'][0]['exercises'] = [];

    expectInvalidTrainingPlan(
        $payload,
        'blocks.0.exercises должен содержать хотя бы одно упражнение.',
    );
});

test('exercise id outside the context is rejected', function () {
    $payload = validTrainingPlanPayload();
    $payload['blocks'][0]['exercises'][0]['exercise_id'] = 999;

    expectInvalidTrainingPlan(
        $payload,
        'blocks.0.exercises.0.exercise_id содержит недоступное упражнение с id 999.',
    );
});

test('null exercise id is rejected', function () {
    $payload = validTrainingPlanPayload();
    $payload['blocks'][0]['exercises'][0]['exercise_id'] = null;

    expectInvalidTrainingPlan(
        $payload,
        'blocks.0.exercises.0.exercise_id должен быть положительным целым числом.',
    );
});

test('total duration must be an integer', function () {
    $payload = validTrainingPlanPayload();
    $payload['total_duration_minutes'] = '60';

    expectInvalidTrainingPlan(
        $payload,
        'total_duration_minutes должен быть положительным целым числом.',
    );
});

test('missing warnings are normalized to an empty list', function () {
    $payload = validTrainingPlanPayload();
    unset($payload['warnings']);

    $validatedPlan = (new TrainingPlanValidator)->validate(
        validationJson($payload),
        validationTrainingContext(),
    );

    expect($validatedPlan->warnings)->toBe([]);
});

test('ai reasoning accepts null and rejects invalid values', function () {
    $payload = validTrainingPlanPayload();
    $payload['ai_reasoning'] = null;

    $validatedPlan = (new TrainingPlanValidator)->validate(
        validationJson($payload),
        validationTrainingContext(),
    );

    expect($validatedPlan->aiReasoning)->toBeNull();

    $payload['ai_reasoning'] = ['not a string'];
    expectInvalidTrainingPlan($payload, 'ai_reasoning должен быть строкой или null.');
});

test('ai reasoning is required in the response structure', function () {
    $payload = validTrainingPlanPayload();
    unset($payload['ai_reasoning']);

    expectInvalidTrainingPlan($payload, 'ai_reasoning является обязательным полем.');
});

test('large difference between block and total duration is rejected', function () {
    $payload = validTrainingPlanPayload();
    $payload['blocks'][0]['duration_minutes'] = 120;

    expectInvalidTrainingPlan(
        $payload,
        'суммарная длительность 120 минут слишком сильно отличается',
    );
});

test('total duration must match the scheduled training', function () {
    $payload = validTrainingPlanPayload();
    $payload['total_duration_minutes'] = 90;
    $payload['blocks'][0]['duration_minutes'] = 90;

    expectInvalidTrainingPlan(
        $payload,
        'total_duration_minutes должен соответствовать длительности расписания 60 минут.',
    );
});

test('sets repetitions and rest seconds are normalized', function () {
    $payload = validTrainingPlanPayload();
    $payload['blocks'][0]['exercises'][0]['duration_minutes'] = null;
    $payload['blocks'][0]['exercises'][0]['sets'] = 3;
    $payload['blocks'][0]['exercises'][0]['repetitions'] = 12;
    $payload['blocks'][0]['exercises'][0]['rest_seconds'] = 45;

    $validatedPlan = (new TrainingPlanValidator)->validate(
        validationJson($payload),
        validationTrainingContext(),
    );
    $exercise = $validatedPlan->blocks[0]->exercises[0];

    expect($exercise->durationMinutes)->toBeNull()
        ->and($exercise->sets)->toBe(3)
        ->and($exercise->repetitions)->toBe('12')
        ->and($exercise->restSeconds)->toBe(45);
});

/** @return array<string, mixed> */
function validTrainingPlanPayload(): array
{
    return [
        'title' => 'Координация и ОФП',
        'goal' => 'Развитие координации',
        'total_duration_minutes' => 60,
        'ai_reasoning' => 'План учитывает ограничения группы.',
        'warnings' => ['Без длительных прыжков'],
        'blocks' => [[
            'name' => 'Основная часть',
            'duration_minutes' => 60,
            'position' => 1,
            'notes' => 'Контролировать технику',
            'exercises' => [[
                'exercise_id' => 42,
                'name' => 'Название от модели не считается доверенным',
                'duration_minutes' => 20,
                'sets' => null,
                'repetitions' => null,
                'rest_seconds' => null,
                'position' => 1,
                'notes' => null,
            ]],
        ]],
    ];
}

function validationTrainingContext(): TrainingContext
{
    return new TrainingContext(
        userId: 1,
        userName: 'Тренер',
        scheduledTrainingId: 15,
        scheduledTrainingStartsAt: CarbonImmutable::parse('2026-07-10 18:00:00'),
        scheduledTrainingDurationMinutes: 60,
        scheduledTrainingLocation: 'Зал №1',
        scheduledTrainingNotes: null,
        target: new TrainingContextTarget(
            type: 'training_group',
            id: 10,
            name: 'Группа U12',
            level: 'beginner',
            goal: 'Развитие координации',
            restrictions: 'Без высокой прыжковой нагрузки',
        ),
        history: [],
        notes: [],
        memories: [],
        exercises: [
            new TrainingExerciseItem(
                id: 42,
                name: 'Суставная разминка',
                description: 'Мягкая разминка суставов',
                goal: 'Разминка',
                difficulty: 'Лёгкая',
                equipment: null,
                durationMinutes: 5,
                muscleGroups: ['full_body'],
                loadType: 'warmup',
                movementPattern: 'stretch',
                contraindications: null,
                ageMin: 8,
                ageMax: 60,
                tags: ['разминка'],
                isSystem: true,
            ),
        ],
    );
}

/** @param array<string, mixed> $payload */
function expectInvalidTrainingPlan(array $payload, string $message): void
{
    expect(fn () => (new TrainingPlanValidator)->validate(
        validationJson($payload),
        validationTrainingContext(),
    ))->toThrow(InvalidTrainingPlanResponseException::class, $message);
}

/** @param array<string, mixed> $payload */
function validationJson(array $payload): string
{
    return json_encode($payload, JSON_THROW_ON_ERROR);
}
