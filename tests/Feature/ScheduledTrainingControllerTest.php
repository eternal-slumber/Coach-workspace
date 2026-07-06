<?php

use App\Models\ScheduledTraining;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests cannot access scheduled trainings', function () {
    $this->getJson(route('scheduled-trainings.index'))->assertUnauthorized();
});

test('a user can schedule a training for their trainee', function () {
    $user = User::factory()->create();
    $trainee = $user->trainees()->create(scheduledTraineePayload());

    $response = $this->actingAs($user)->postJson(
        route('scheduled-trainings.store'),
        scheduledTrainingPayload(['trainee_id' => $trainee->id]),
    );

    $response
        ->assertCreated()
        ->assertJsonPath('trainee_id', $trainee->id)
        ->assertJsonPath('training_group_id', null)
        ->assertJsonPath('status', 'planned');

    $scheduledTraining = ScheduledTraining::query()->sole();

    expect($scheduledTraining->user->is($user))->toBeTrue()
        ->and($scheduledTraining->trainee?->is($trainee))->toBeTrue()
        ->and($trainee->scheduledTrainings->contains($scheduledTraining))->toBeTrue();
});

test('a user can schedule a training for their group', function () {
    $user = User::factory()->create();
    $trainingGroup = $user->trainingGroups()->create(scheduledTrainingGroupPayload());

    $response = $this->actingAs($user)->postJson(
        route('scheduled-trainings.store'),
        scheduledTrainingPayload(['training_group_id' => $trainingGroup->id]),
    );

    $response
        ->assertCreated()
        ->assertJsonPath('trainee_id', null)
        ->assertJsonPath('training_group_id', $trainingGroup->id)
        ->assertJsonPath('training_group.name', 'Группа U12');
});

test('a scheduled training requires exactly one trainee or training group', function () {
    $user = User::factory()->create();
    $trainee = $user->trainees()->create(scheduledTraineePayload());
    $trainingGroup = $user->trainingGroups()->create(scheduledTrainingGroupPayload());

    $this->actingAs($user)
        ->postJson(route('scheduled-trainings.store'), scheduledTrainingPayload())
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['trainee_id', 'training_group_id']);

    $this->actingAs($user)
        ->postJson(route('scheduled-trainings.store'), scheduledTrainingPayload([
            'trainee_id' => $trainee->id,
            'training_group_id' => $trainingGroup->id,
        ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['trainee_id', 'training_group_id']);
});

test('a user cannot schedule a training for another users trainee or group', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $trainee = $otherUser->trainees()->create(scheduledTraineePayload());
    $trainingGroup = $otherUser->trainingGroups()->create(scheduledTrainingGroupPayload());

    $this->actingAs($user)
        ->postJson(
            route('scheduled-trainings.store'),
            scheduledTrainingPayload(['trainee_id' => $trainee->id]),
        )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('trainee_id');

    $this->actingAs($user)
        ->postJson(
            route('scheduled-trainings.store'),
            scheduledTrainingPayload(['training_group_id' => $trainingGroup->id]),
        )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('training_group_id');
});

test('a user can change scheduled training time and status', function () {
    $user = User::factory()->create();
    $trainee = $user->trainees()->create(scheduledTraineePayload());
    $scheduledTraining = $user->scheduledTrainings()->create(
        scheduledTrainingPayload(['trainee_id' => $trainee->id]),
    );

    $this->actingAs($user)
        ->patchJson(route('scheduled-trainings.update', $scheduledTraining), [
            'starts_at' => '2026-07-07 20:00:00+03:00',
            'ends_at' => '2026-07-07 21:30:00+03:00',
            'status' => 'completed',
        ])
        ->assertOk()
        ->assertJsonPath('status', 'completed');

    $scheduledTraining->refresh();

    expect($scheduledTraining->starts_at->format('Y-m-d H:i'))->toBe('2026-07-07 20:00')
        ->and($scheduledTraining->ends_at->format('Y-m-d H:i'))->toBe('2026-07-07 21:30');

    $this->actingAs($user)
        ->patchJson(route('scheduled-trainings.update', $scheduledTraining), [
            'status' => 'cancelled',
        ])
        ->assertOk()
        ->assertJsonPath('status', 'cancelled');
});

test('scheduled training time and status are validated', function () {
    $user = User::factory()->create();
    $trainee = $user->trainees()->create(scheduledTraineePayload());

    $this->actingAs($user)
        ->postJson(route('scheduled-trainings.store'), scheduledTrainingPayload([
            'trainee_id' => $trainee->id,
            'ends_at' => '2026-07-06 17:00:00+03:00',
            'status' => 'unknown',
        ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['ends_at', 'status']);
});

test('a user sees and accesses only their scheduled trainings', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $trainee = $user->trainees()->create(scheduledTraineePayload());
    $otherTrainee = $otherUser->trainees()->create(scheduledTraineePayload());

    $scheduledTraining = $user->scheduledTrainings()->create(
        scheduledTrainingPayload(['trainee_id' => $trainee->id]),
    );
    $otherScheduledTraining = $otherUser->scheduledTrainings()->create(
        scheduledTrainingPayload(['trainee_id' => $otherTrainee->id]),
    );

    $this->actingAs($user)
        ->getJson(route('scheduled-trainings.index'))
        ->assertOk()
        ->assertJsonCount(1, 'scheduled_trainings')
        ->assertJsonPath('scheduled_trainings.0.id', $scheduledTraining->id);

    $this->actingAs($user)
        ->getJson(route('scheduled-trainings.show', $otherScheduledTraining))
        ->assertNotFound();

    $this->actingAs($user)
        ->putJson(
            route('scheduled-trainings.update', $otherScheduledTraining),
            scheduledTrainingPayload(['trainee_id' => $trainee->id]),
        )
        ->assertForbidden();

    $this->actingAs($user)
        ->deleteJson(route('scheduled-trainings.destroy', $otherScheduledTraining))
        ->assertNotFound();

    $this->assertModelExists($otherScheduledTraining);
});

test('a user can delete their scheduled training', function () {
    $user = User::factory()->create();
    $trainee = $user->trainees()->create(scheduledTraineePayload());
    $scheduledTraining = $user->scheduledTrainings()->create(
        scheduledTrainingPayload(['trainee_id' => $trainee->id]),
    );

    $this->actingAs($user)
        ->deleteJson(route('scheduled-trainings.destroy', $scheduledTraining))
        ->assertNoContent();

    $this->assertModelMissing($scheduledTraining);
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function scheduledTrainingPayload(array $overrides = []): array
{
    return array_merge([
        'trainee_id' => null,
        'training_group_id' => null,
        'starts_at' => '2026-07-06 18:00:00+03:00',
        'ends_at' => '2026-07-06 19:00:00+03:00',
        'location' => 'Зал №2',
        'status' => 'planned',
    ], $overrides);
}

/**
 * @return array<string, mixed>
 */
function scheduledTraineePayload(): array
{
    return [
        'name' => 'Алексей Смирнов',
        'age' => 28,
        'level' => 'Начинающий',
        'goal' => 'Улучшить общую физическую форму',
    ];
}

/**
 * @return array<string, mixed>
 */
function scheduledTrainingGroupPayload(): array
{
    return [
        'name' => 'Группа U12',
        'sport_type' => 'Функциональный тренинг',
        'age_range' => '10–12 лет',
        'level' => 'Средний',
        'goal' => 'Развить выносливость',
    ];
}
