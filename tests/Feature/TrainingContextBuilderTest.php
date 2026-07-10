<?php

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
use App\Services\Agent\TrainingContextBuilder;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it builds a complete context for a training group', function () {
    $user = User::factory()->create(['name' => 'Тренер']);
    $otherUser = User::factory()->create();
    $trainingGroup = createContextTrainingGroup($user);
    $scheduledTraining = createContextScheduledTraining(
        $user,
        trainingGroup: $trainingGroup,
        startsAt: CarbonImmutable::parse('2026-07-10 18:00:00'),
    );

    $completedPlan = createContextPlan(
        $user,
        createContextScheduledTraining(
            $user,
            trainingGroup: $trainingGroup,
            startsAt: CarbonImmutable::parse('2026-07-07 18:00:00'),
        ),
        status: 'completed',
        title: 'Координация и ОФП',
    );
    $block = TrainingPlanBlock::factory()->for($completedPlan)->create([
        'name' => 'Разминка',
        'duration_minutes' => 10,
    ]);
    TrainingPlanExercise::factory()->for($block)->create([
        'name' => 'Суставная разминка',
        'duration_minutes' => 5,
    ]);
    TrainingNote::factory()->create([
        'user_id' => $user->id,
        'training_plan_id' => $completedPlan->id,
        'trainee_id' => null,
        'training_group_id' => $trainingGroup->id,
        'intensity' => 'medium',
        'result' => 'normal',
        'tags' => ['устали', 'повторить технику'],
        'note' => 'Координация шла тяжело.',
    ]);

    createContextPlan(
        $user,
        createContextScheduledTraining($user, trainingGroup: $trainingGroup),
        status: 'draft',
        title: 'Черновик',
    );
    createContextPlan(
        $user,
        createContextScheduledTraining($user, trainingGroup: $trainingGroup),
        status: 'approved',
        title: 'Утверждённый план',
    );
    createContextPlan(
        $otherUser,
        createContextScheduledTraining($otherUser, trainingGroup: $trainingGroup),
        status: 'completed',
        title: 'Чужой план',
    );

    createContextMemory($user, trainingGroup: $trainingGroup, content: 'Слабая координация', importance: 6);
    createContextMemory($user, trainingGroup: $trainingGroup, content: 'Без длительных прыжков', importance: 9);
    createContextMemory($user, trainingGroup: $trainingGroup, content: 'Неактуальная память', importance: 10, isActive: false);
    createContextMemory($otherUser, trainingGroup: $trainingGroup, content: 'Чужая память', importance: 10);

    Exercise::factory()->system()->create([
        'name' => 'Системная разминка',
        'muscle_groups' => ['shoulders', 'hips'],
        'load_type' => 'warmup',
        'movement_pattern' => 'stretch',
    ]);
    Exercise::factory()->for($user)->create(['name' => 'Моё упражнение']);
    Exercise::factory()->for($otherUser)->create(['name' => 'Чужое упражнение']);

    $context = app(TrainingContextBuilder::class)
        ->buildForScheduledTraining($user, $scheduledTraining);

    expect($context->userId)->toBe($user->id)
        ->and($context->userName)->toBe('Тренер')
        ->and($context->scheduledTrainingId)->toBe($scheduledTraining->id)
        ->and($context->scheduledTrainingDurationMinutes)->toBe(60)
        ->and($context->target->type)->toBe('training_group')
        ->and($context->target->name)->toBe('Группа U12')
        ->and($context->target->level)->toBe('beginner')
        ->and($context->target->goal)->toBe('Развитие координации')
        ->and($context->target->restrictions)->toBe('Без высокой прыжковой нагрузки')
        ->and($context->target->ageRange)->toBe('10–12')
        ->and($context->target->sportType)->toBe('ОФП')
        ->and($context->history)->toHaveCount(1)
        ->and($context->history[0]->title)->toBe('Координация и ОФП')
        ->and($context->history[0]->blocks[0]['name'])->toBe('Разминка')
        ->and($context->history[0]->blocks[0]['exercises'][0]['name'])->toBe('Суставная разминка')
        ->and($context->notes)->toHaveCount(1)
        ->and($context->notes[0]->tags)->toBe(['устали', 'повторить технику'])
        ->and($context->notes[0]->note)->toBe('Координация шла тяжело.')
        ->and(array_column($context->memories, 'content'))->toBe([
            'Без длительных прыжков',
            'Слабая координация',
        ])
        ->and(array_column($context->exercises, 'name'))->toContain('Системная разминка')
        ->and(array_column($context->exercises, 'name'))->toContain('Моё упражнение')
        ->and(array_column($context->exercises, 'name'))->not->toContain('Чужое упражнение');

    $systemExercise = collect($context->exercises)
        ->firstWhere('name', 'Системная разминка');

    expect($systemExercise?->muscleGroups)->toBe(['shoulders', 'hips'])
        ->and($systemExercise?->loadType)->toBe('warmup')
        ->and($systemExercise?->movementPattern)->toBe('stretch');
});

test('it builds context for a trainee', function () {
    $user = User::factory()->create();
    $trainee = createContextTrainee($user);
    $scheduledTraining = createContextScheduledTraining($user, trainee: $trainee);
    $completedPlan = createContextPlan(
        $user,
        createContextScheduledTraining(
            $user,
            trainee: $trainee,
            startsAt: CarbonImmutable::parse('2026-07-05 12:00:00'),
        ),
        status: 'completed',
        title: 'Индивидуальная техника',
    );
    TrainingNote::factory()->create([
        'user_id' => $user->id,
        'training_plan_id' => $completedPlan->id,
        'trainee_id' => $trainee->id,
        'training_group_id' => null,
    ]);
    createContextMemory($user, trainee: $trainee, content: 'Не давать осевую нагрузку', importance: 8);

    $context = app(TrainingContextBuilder::class)
        ->buildForScheduledTraining($user, $scheduledTraining);

    expect($context->target->type)->toBe('trainee')
        ->and($context->target->id)->toBe($trainee->id)
        ->and($context->target->name)->toBe('Алексей')
        ->and($context->target->age)->toBe(14)
        ->and($context->target->level)->toBe('intermediate')
        ->and($context->target->goal)->toBe('Развитие силы')
        ->and($context->target->restrictions)->toBe('Без осевой нагрузки')
        ->and($context->history)->toHaveCount(1)
        ->and($context->history[0]->title)->toBe('Индивидуальная техника')
        ->and($context->notes)->toHaveCount(1)
        ->and($context->memories)->toHaveCount(1);
});

test('it limits history to the five latest completed plans', function () {
    $user = User::factory()->create();
    $trainingGroup = createContextTrainingGroup($user);
    $scheduledTraining = createContextScheduledTraining($user, trainingGroup: $trainingGroup);

    foreach (range(1, 6) as $day) {
        createContextPlan(
            $user,
            createContextScheduledTraining(
                $user,
                trainingGroup: $trainingGroup,
                startsAt: CarbonImmutable::parse("2026-07-0{$day} 18:00:00"),
            ),
            status: 'completed',
            title: "Тренировка {$day}",
        );
    }

    $context = app(TrainingContextBuilder::class)
        ->buildForScheduledTraining($user, $scheduledTraining);

    expect(array_column($context->history, 'title'))->toBe([
        'Тренировка 6',
        'Тренировка 5',
        'Тренировка 4',
        'Тренировка 3',
        'Тренировка 2',
    ]);
});

test('it rejects a scheduled training owned by another user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $trainingGroup = createContextTrainingGroup($otherUser);
    $scheduledTraining = createContextScheduledTraining($otherUser, trainingGroup: $trainingGroup);

    expect(fn () => app(TrainingContextBuilder::class)
        ->buildForScheduledTraining($user, $scheduledTraining))
        ->toThrow(AuthorizationException::class);
});

function createContextTrainingGroup(User $user): TrainingGroup
{
    return $user->trainingGroups()->create([
        'name' => 'Группа U12',
        'sport_type' => 'ОФП',
        'age_range' => '10–12',
        'level' => 'beginner',
        'goal' => 'Развитие координации',
        'restrictions' => 'Без высокой прыжковой нагрузки',
    ]);
}

function createContextTrainee(User $user): Trainee
{
    return $user->trainees()->create([
        'name' => 'Алексей',
        'age' => 14,
        'level' => 'intermediate',
        'goal' => 'Развитие силы',
        'restrictions' => 'Без осевой нагрузки',
    ]);
}

function createContextScheduledTraining(
    User $user,
    ?Trainee $trainee = null,
    ?TrainingGroup $trainingGroup = null,
    ?CarbonImmutable $startsAt = null,
): ScheduledTraining {
    $startsAt ??= CarbonImmutable::parse('2026-07-10 18:00:00');

    return $user->scheduledTrainings()->create([
        'trainee_id' => $trainee?->id,
        'training_group_id' => $trainingGroup?->id,
        'starts_at' => $startsAt,
        'ends_at' => $startsAt->addHour(),
        'location' => 'Зал №1',
        'status' => 'planned',
        'color' => 'blue',
    ]);
}

function createContextPlan(
    User $user,
    ScheduledTraining $scheduledTraining,
    string $status,
    string $title,
): TrainingPlan {
    return TrainingPlan::factory()->create([
        'user_id' => $user->id,
        'scheduled_training_id' => $scheduledTraining->id,
        'trainee_id' => $scheduledTraining->trainee_id,
        'training_group_id' => $scheduledTraining->training_group_id,
        'title' => $title,
        'goal' => 'Развитие координации',
        'total_duration_minutes' => 60,
        'status' => $status,
    ]);
}

function createContextMemory(
    User $user,
    ?Trainee $trainee = null,
    ?TrainingGroup $trainingGroup = null,
    string $content = 'Важный факт',
    int $importance = 5,
    bool $isActive = true,
): AgentMemory {
    return AgentMemory::factory()->create([
        'user_id' => $user->id,
        'trainee_id' => $trainee?->id,
        'training_group_id' => $trainingGroup?->id,
        'content' => $content,
        'importance' => $importance,
        'is_active' => $isActive,
    ]);
}
