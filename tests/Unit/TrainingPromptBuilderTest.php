<?php

use App\Services\Agent\DTO\TrainingContext;
use App\Services\Agent\DTO\TrainingContextTarget;
use App\Services\Agent\DTO\TrainingExerciseItem;
use App\Services\Agent\DTO\TrainingHistoryItem;
use App\Services\Agent\DTO\TrainingMemoryItem;
use App\Services\Agent\DTO\TrainingNoteItem;
use App\Services\Agent\TrainingPromptBuilder;
use App\Services\AI\AiMessage;
use Carbon\CarbonImmutable;

test('it returns system and user messages', function () {
    $messages = (new TrainingPromptBuilder)->buildTrainingPlanPrompt(
        trainingPromptContext(),
    );

    expect($messages)->toHaveCount(2)
        ->and($messages[0])->toBeInstanceOf(AiMessage::class)
        ->and($messages[0]->role)->toBe('system')
        ->and($messages[0]->content)->not->toBeEmpty()
        ->and($messages[1])->toBeInstanceOf(AiMessage::class)
        ->and($messages[1]->role)->toBe('user')
        ->and($messages[1]->content)->not->toBeEmpty();
});

test('system prompt defines safety and strict json rules', function () {
    $systemPrompt = (new TrainingPromptBuilder)
        ->buildTrainingPlanPrompt(trainingPromptContext())[0]
        ->content;

    expect($systemPrompt)
        ->toContain('не игнорируй ограничения')
        ->toContain('учитывай заметки после прошлых тренировок')
        ->toContain('активную память')
        ->toContain('используй только упражнения из списка')
        ->toContain('не выдумывай упражнения, медицинские диагнозы')
        ->toContain('для силового упражнения обязательно заполняй sets')
        ->toContain('темп, интенсивность и ключевую подсказку')
        ->toContain('набор мышечной массы')
        ->toContain('load_type strength')
        ->toContain('«Круговая ОФП»')
        ->toContain('только один валидный JSON-объект')
        ->toContain('не рассуждай и не выводи chain-of-thought')
        ->toContain('не используй reasoning_content как финальный ответ')
        ->toContain('только в message.content')
        ->toContain('первый символ ответа должен быть {')
        ->toContain('последний символ должен быть }')
        ->toContain('"total_duration_minutes": 60')
        ->toContain('"ai_reasoning"')
        ->toContain('кратко объясни выбор блоков')
        ->toContain('"exercise_id": 1')
        ->toContain('"blocks"');

    expect($systemPrompt)->not->toContain('exercise_id: null');
});

test('user prompt contains the complete filtered training context', function () {
    $userPrompt = (new TrainingPromptBuilder)
        ->buildTrainingPlanPrompt(trainingPromptContext())[1]
        ->content;

    expect($userPrompt)
        ->toContain('тип: группа')
        ->toContain('"Группа U12"')
        ->toContain('"beginner"')
        ->toContain('"Развитие координации"')
        ->toContain('"Без высокой прыжковой нагрузки"')
        ->toContain('2026-07-10 18:00')
        ->toContain('длительность: 60 минут')
        ->toContain('[restriction, importance 9] "Без длительных прыжков"')
        ->toContain('"Координация и ОФП"')
        ->toContain('"Координация шла тяжело"')
        ->toContain('exercise_id: 42')
        ->toContain('"Суставная разминка"')
        ->toContain('группы мышц: "shoulders", "hips"')
        ->toContain('тип нагрузки: "warmup"')
        ->toContain('паттерн движения: "stretch"');

    expect($userPrompt)->not->toContain('Чужой план');
    expect($userPrompt)->not->toContain('Чужая память');
    expect($userPrompt)->not->toContain('Чужое упражнение');
});

test('target type is rendered for groups and trainees', function (
    string $targetType,
    string $targetName,
    string $expectedType,
) {
    $context = trainingPromptContext(new TrainingContextTarget(
        type: $targetType,
        id: 10,
        name: $targetName,
        level: 'beginner',
        goal: 'Координация',
        restrictions: null,
    ));

    $userPrompt = (new TrainingPromptBuilder)
        ->buildTrainingPlanPrompt($context)[1]
        ->content;

    expect($userPrompt)
        ->toContain("тип: {$expectedType}")
        ->toContain('"'.$targetName.'"');
})->with([
    'group' => ['training_group', 'Группа U12', 'группа'],
    'trainee' => ['trainee', 'Алексей', 'клиент'],
]);

test('empty optional context sections are represented explicitly', function () {
    $context = trainingPromptContext(
        history: [],
        notes: [],
        memories: [],
        exercises: [],
    );

    $userPrompt = (new TrainingPromptBuilder)
        ->buildTrainingPlanPrompt($context)[1]
        ->content;

    expect($userPrompt)
        ->toContain('Нет активной памяти.')
        ->toContain('Нет истории.')
        ->toContain('Нет заметок.')
        ->toContain('Список пуст. Не выдумывай упражнения.');
});

/**
 * @param  list<TrainingHistoryItem>|null  $history
 * @param  list<TrainingNoteItem>|null  $notes
 * @param  list<TrainingMemoryItem>|null  $memories
 * @param  list<TrainingExerciseItem>|null  $exercises
 */
function trainingPromptContext(
    ?TrainingContextTarget $target = null,
    ?array $history = null,
    ?array $notes = null,
    ?array $memories = null,
    ?array $exercises = null,
): TrainingContext {
    $note = new TrainingNoteItem(
        trainingPlanId: 7,
        intensity: 'medium',
        result: 'normal',
        tags: ['устали', 'повторить технику'],
        note: 'Координация шла тяжело',
    );

    return new TrainingContext(
        userId: 1,
        userName: 'Тренер',
        scheduledTrainingId: 15,
        scheduledTrainingStartsAt: CarbonImmutable::parse('2026-07-10 18:00:00'),
        scheduledTrainingDurationMinutes: 60,
        scheduledTrainingLocation: 'Зал №1',
        scheduledTrainingNotes: 'Спокойный вход в нагрузку',
        target: $target ?? new TrainingContextTarget(
            type: 'training_group',
            id: 10,
            name: 'Группа U12',
            level: 'beginner',
            goal: 'Развитие координации',
            restrictions: 'Без высокой прыжковой нагрузки',
            ageRange: '10–12',
            sportType: 'ОФП',
        ),
        history: $history ?? [
            new TrainingHistoryItem(
                id: 7,
                title: 'Координация и ОФП',
                goal: 'Развитие координации',
                totalDurationMinutes: 55,
                startsAt: CarbonImmutable::parse('2026-07-01 18:00:00'),
                blocks: [[
                    'name' => 'Разминка',
                    'duration_minutes' => 10,
                    'notes' => null,
                    'exercises' => [[
                        'name' => 'Лёгкий бег',
                        'description' => null,
                        'duration_minutes' => 5,
                        'sets' => null,
                        'repetitions' => null,
                        'rest_seconds' => null,
                        'notes' => null,
                    ]],
                ]],
                note: $note,
            ),
        ],
        notes: $notes ?? [$note],
        memories: $memories ?? [
            new TrainingMemoryItem(
                id: 3,
                type: 'restriction',
                content: 'Без длительных прыжков',
                importance: 9,
            ),
        ],
        exercises: $exercises ?? [
            new TrainingExerciseItem(
                id: 42,
                name: 'Суставная разминка',
                description: 'Мягкая разминка суставов',
                goal: 'Разминка',
                difficulty: 'Лёгкая',
                equipment: 'Без инвентаря',
                durationMinutes: 5,
                muscleGroups: ['shoulders', 'hips'],
                loadType: 'warmup',
                movementPattern: 'stretch',
                contraindications: null,
                ageMin: 8,
                ageMax: 60,
                tags: ['разминка', 'мобильность'],
                isSystem: true,
            ),
        ],
    );
}
