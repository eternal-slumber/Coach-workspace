<?php

use App\Models\ScheduledTraining;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('guests cannot access the calendar or reschedule trainings', function () {
    $user = User::factory()->create();
    $trainee = $user->trainees()->create(calendarTraineePayload());
    $scheduledTraining = $user->scheduledTrainings()->create(
        calendarTrainingPayload(['trainee_id' => $trainee->id]),
    );

    $this->get(route('calendar'))->assertRedirect(route('login'));
    $this->patchJson(route('scheduled-trainings.schedule', $scheduledTraining), [
        'starts_at' => '2026-07-08T18:00:00+03:00',
        'ends_at' => '2026-07-08T19:00:00+03:00',
    ])->assertUnauthorized();
    $this->post(route('scheduled-trainings.duplicate', $scheduledTraining))
        ->assertRedirect(route('login'));
});

test('the calendar contains only the current users trainings', function () {
    $this->withoutVite();

    $user = User::factory()->create([
        'working_day_starts_at' => '07:30',
        'working_day_ends_at' => '21:30',
    ]);
    $otherUser = User::factory()->create();
    $trainee = $user->trainees()->create(calendarTraineePayload(['name' => 'Свой клиент']));
    $otherTrainee = $otherUser->trainees()->create(calendarTraineePayload(['name' => 'Чужой клиент']));

    $scheduledTraining = $user->scheduledTrainings()->create(
        calendarTrainingPayload([
            'trainee_id' => $trainee->id,
            'color' => 'green',
            'starts_at' => '2026-07-07T23:00:00+03:00',
            'ends_at' => '2026-07-07T23:45:00+03:00',
        ]),
    );
    $otherUser->scheduledTrainings()->create(
        calendarTrainingPayload(['trainee_id' => $otherTrainee->id]),
    );

    $this->actingAs($user)
        ->get(route('calendar'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('calendar')
            ->has('scheduledTrainings', 1)
            ->where('scheduledTrainings.0.id', $scheduledTraining->id)
            ->where('scheduledTrainings.0.title', 'Свой клиент')
            ->where('scheduledTrainings.0.location', 'Зал №2')
            ->where('scheduledTrainings.0.color', 'green')
            ->where('scheduledTrainings.0.subject_type', 'trainee')
            ->where('scheduledTrainings.0.starts_at', $scheduledTraining->starts_at->toIso8601String())
            ->where('workingHours.startsAt', '07:30')
            ->where('workingHours.endsAt', '21:30'));
});

test('a user can reschedule their training from the calendar', function () {
    $user = User::factory()->create();
    $trainee = $user->trainees()->create(calendarTraineePayload());
    $scheduledTraining = $user->scheduledTrainings()->create(
        calendarTrainingPayload(['trainee_id' => $trainee->id]),
    );

    $this->actingAs($user)
        ->patchJson(route('scheduled-trainings.schedule', $scheduledTraining), [
            'starts_at' => '2026-07-08T15:30:00.000Z',
            'ends_at' => '2026-07-08T17:00:00.000Z',
        ])
        ->assertOk()
        ->assertJsonStructure(['starts_at', 'ends_at']);

    $scheduledTraining->refresh();

    expect($scheduledTraining->starts_at->toIso8601String())
        ->toBe('2026-07-08T15:30:00+00:00')
        ->and($scheduledTraining->ends_at->toIso8601String())
        ->toBe('2026-07-08T17:00:00+00:00');
});

test('calendar rescheduling requires an end after the start', function () {
    $user = User::factory()->create();
    $trainee = $user->trainees()->create(calendarTraineePayload());
    $scheduledTraining = $user->scheduledTrainings()->create(
        calendarTrainingPayload(['trainee_id' => $trainee->id]),
    );

    $this->actingAs($user)
        ->patchJson(route('scheduled-trainings.schedule', $scheduledTraining), [
            'starts_at' => '2026-07-08T20:00:00+03:00',
            'ends_at' => '2026-07-08T19:00:00+03:00',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('ends_at');
});

test('a user cannot reschedule another users training', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $otherTrainee = $otherUser->trainees()->create(calendarTraineePayload());
    $scheduledTraining = $otherUser->scheduledTrainings()->create(
        calendarTrainingPayload(['trainee_id' => $otherTrainee->id]),
    );

    $this->actingAs($user)
        ->patchJson(route('scheduled-trainings.schedule', $scheduledTraining), [
            'starts_at' => '2026-07-08T18:30:00+03:00',
            'ends_at' => '2026-07-08T20:00:00+03:00',
        ])
        ->assertForbidden();
});

test('a user can duplicate their training for the next week', function () {
    $user = User::factory()->create();
    $trainee = $user->trainees()->create(calendarTraineePayload());
    $scheduledTraining = $user->scheduledTrainings()->create(
        calendarTrainingPayload([
            'trainee_id' => $trainee->id,
            'status' => 'completed',
            'color' => 'purple',
            'notes' => 'Эта заметка не должна копироваться',
        ]),
    );

    $this->actingAs($user)
        ->post(route('scheduled-trainings.duplicate', $scheduledTraining))
        ->assertRedirect(route('calendar'));

    $duplicate = ScheduledTraining::query()
        ->whereKeyNot($scheduledTraining->id)
        ->sole();

    expect($duplicate->user->is($user))->toBeTrue()
        ->and($duplicate->trainee?->is($trainee))->toBeTrue()
        ->and($duplicate->training_group_id)->toBeNull()
        ->and($duplicate->starts_at->equalTo($scheduledTraining->starts_at->addWeek()))->toBeTrue()
        ->and($duplicate->ends_at->equalTo($scheduledTraining->ends_at->addWeek()))->toBeTrue()
        ->and($duplicate->location)->toBe($scheduledTraining->location)
        ->and($duplicate->color)->toBe('purple')
        ->and($duplicate->status)->toBe('planned')
        ->and($duplicate->notes)->toBeNull();
});

test('a user cannot duplicate another users training', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $trainee = $otherUser->trainees()->create(calendarTraineePayload());
    $scheduledTraining = $otherUser->scheduledTrainings()->create(
        calendarTrainingPayload(['trainee_id' => $trainee->id]),
    );

    $this->actingAs($user)
        ->post(route('scheduled-trainings.duplicate', $scheduledTraining))
        ->assertNotFound();

    expect(ScheduledTraining::query()->count())->toBe(1);
});

test('deleting a training from the calendar redirects back to the calendar', function () {
    $user = User::factory()->create();
    $trainee = $user->trainees()->create(calendarTraineePayload());
    $scheduledTraining = $user->scheduledTrainings()->create(
        calendarTrainingPayload(['trainee_id' => $trainee->id]),
    );

    $this->actingAs($user)
        ->delete(route('scheduled-trainings.destroy', [
            'scheduled_training' => $scheduledTraining,
            'redirect' => 'calendar',
        ]))
        ->assertRedirect(route('calendar'));

    $this->assertModelMissing($scheduledTraining);
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function calendarTraineePayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Алексей Смирнов',
        'age' => 28,
        'level' => 'Начинающий',
        'goal' => 'Улучшить общую физическую форму',
    ], $overrides);
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function calendarTrainingPayload(array $overrides = []): array
{
    return array_merge([
        'trainee_id' => null,
        'training_group_id' => null,
        'starts_at' => '2026-07-07T18:00:00+03:00',
        'ends_at' => '2026-07-07T19:00:00+03:00',
        'location' => 'Зал №2',
        'status' => 'planned',
        'color' => 'blue',
        'notes' => null,
    ], $overrides);
}
