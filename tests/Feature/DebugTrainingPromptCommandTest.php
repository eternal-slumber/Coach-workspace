<?php

use App\Models\Exercise;
use App\Models\ScheduledTraining;
use App\Models\TrainingGroup;
use App\Models\User;
use App\Services\AI\AiClientInterface;
use App\Services\AI\AiResponse;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

uses(RefreshDatabase::class);

test('it prints the training prompt without calling the AI client', function () {
    Config::set('ai.provider', 'openrouter');
    Config::set('ai.model', 'test/model');

    $user = User::factory()->create(['name' => 'Тренер']);
    $trainingGroup = createDebugPromptTrainingGroup($user);
    $scheduledTraining = createDebugPromptScheduledTraining($user, $trainingGroup);
    $exercise = Exercise::factory()->system()->create([
        'name' => 'Суставная разминка',
    ]);

    app()->instance(AiClientInterface::class, new class implements AiClientInterface
    {
        public function chat(array $messages, array $options = []): AiResponse
        {
            throw new RuntimeException('The AI client must not be called by the debug command.');
        }
    });

    $exitCode = Artisan::call('ai:debug-prompt', [
        'scheduledTrainingId' => $scheduledTraining->id,
    ]);
    $output = Artisan::output();

    expect($exitCode)->toBe(0)
        ->and($output)->toContain('AI DEBUG PROMPT')
        ->and($output)->toContain('MESSAGE #1 — SYSTEM')
        ->and($output)->toContain('MESSAGE #2 — USER')
        ->and($output)->toContain('AI provider: openrouter')
        ->and($output)->toContain('AI model: test/model')
        ->and($output)->toContain('Группа U12')
        ->and($output)->toContain('Без высокой прыжковой нагрузки')
        ->and($output)->toContain('exercise_id: '.$exercise->id)
        ->and($output)->toContain('Суставная разминка')
        ->and($output)->toContain('Prompt was not sent to AI model.');
});

test('it accepts an explicit matching user', function () {
    $user = User::factory()->create();
    $scheduledTraining = createDebugPromptScheduledTraining(
        $user,
        createDebugPromptTrainingGroup($user),
    );

    $exitCode = Artisan::call('ai:debug-prompt', [
        'scheduledTrainingId' => $scheduledTraining->id,
        '--user' => $user->id,
    ]);

    expect($exitCode)->toBe(0)
        ->and(Artisan::output())->toContain('User ID: '.$user->id);
});

test('it rejects an explicit user who does not own the training', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $scheduledTraining = createDebugPromptScheduledTraining(
        $owner,
        createDebugPromptTrainingGroup($owner),
    );

    $exitCode = Artisan::call('ai:debug-prompt', [
        'scheduledTrainingId' => $scheduledTraining->id,
        '--user' => $otherUser->id,
    ]);

    expect($exitCode)->toBe(1)
        ->and(Artisan::output())->toContain('ScheduledTraining does not belong to this user.');
});

test('it reports a missing scheduled training', function () {
    $exitCode = Artisan::call('ai:debug-prompt', [
        'scheduledTrainingId' => 999,
    ]);

    expect($exitCode)->toBe(1)
        ->and(Artisan::output())->toContain('ScheduledTraining #999 not found.');
});

function createDebugPromptTrainingGroup(User $user): TrainingGroup
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

function createDebugPromptScheduledTraining(
    User $user,
    TrainingGroup $trainingGroup,
): ScheduledTraining {
    $startsAt = CarbonImmutable::parse('2026-07-10 18:00:00');

    return $user->scheduledTrainings()->create([
        'training_group_id' => $trainingGroup->id,
        'starts_at' => $startsAt,
        'ends_at' => $startsAt->addHour(),
        'location' => 'Зал №1',
        'status' => 'planned',
        'color' => 'blue',
    ]);
}
