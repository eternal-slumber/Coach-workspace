<?php

use App\Models\ScheduledTraining;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

afterEach(function () {
    Date::setTestNow();
});

test('guests cannot access scheduled training pages', function () {
    $this->get(route('scheduled-trainings.index'))->assertRedirect(route('login'));
    $this->get(route('scheduled-trainings.create'))->assertRedirect(route('login'));
});

test('a user sees only their upcoming scheduled trainings', function () {
    Date::setTestNow('2026-07-06 12:00:00');

    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $trainee = $user->trainees()->create(scheduledTraineePayload());
    $otherTrainee = $otherUser->trainees()->create(scheduledTraineePayload());

    $scheduledTraining = $user->scheduledTrainings()->create(
        scheduledTrainingPayload([
            'trainee_id' => $trainee->id,
            'starts_at' => '2026-07-07 18:00:00+03:00',
            'ends_at' => '2026-07-07 19:00:00+03:00',
        ]),
    );
    $user->scheduledTrainings()->create(scheduledTrainingPayload([
        'trainee_id' => $trainee->id,
        'starts_at' => '2026-07-05 18:00:00+03:00',
        'ends_at' => '2026-07-05 19:00:00+03:00',
    ]));
    $otherUser->scheduledTrainings()->create(scheduledTrainingPayload([
        'trainee_id' => $otherTrainee->id,
        'starts_at' => '2026-07-07 17:00:00+03:00',
        'ends_at' => '2026-07-07 18:00:00+03:00',
    ]));

    $this->actingAs($user)
        ->get(route('scheduled-trainings.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('scheduled-trainings/index')
            ->has('scheduledTrainings', 1)
            ->where('scheduledTrainings.0.id', $scheduledTraining->id)
            ->where('scheduledTrainings.0.subject_name', 'Алексей Смирнов'));
});

test('create page contains only the current users trainees and groups', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $user->trainees()->create(scheduledTraineePayload(['name' => 'Свой клиент']));
    $otherUser->trainees()->create(scheduledTraineePayload(['name' => 'Чужой клиент']));
    $user->trainingGroups()->create(scheduledTrainingGroupPayload(['name' => 'Своя группа']));
    $otherUser->trainingGroups()->create(scheduledTrainingGroupPayload(['name' => 'Чужая группа']));

    $this->actingAs($user)
        ->get(route('scheduled-trainings.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('scheduled-trainings/create')
            ->has('trainees', 1)
            ->where('trainees.0.name', 'Свой клиент')
            ->has('trainingGroups', 1)
            ->where('trainingGroups.0.name', 'Своя группа'));
});

test('a user can schedule a training for their trainee', function () {
    $user = User::factory()->create();
    $trainee = $user->trainees()->create(scheduledTraineePayload());

    $response = $this->actingAs($user)->post(
        route('scheduled-trainings.store'),
        scheduledTrainingPayload([
            'trainee_id' => $trainee->id,
            'color' => 'green',
            'notes' => 'Проверить технику приседаний',
        ]),
    );

    $scheduledTraining = ScheduledTraining::query()->sole();

    $response->assertRedirect(route('scheduled-trainings.show', $scheduledTraining));
    expect($scheduledTraining->user->is($user))->toBeTrue()
        ->and($scheduledTraining->trainee?->is($trainee))->toBeTrue()
        ->and($scheduledTraining->color)->toBe('green')
        ->and($scheduledTraining->notes)->toBe('Проверить технику приседаний');
});

test('a scheduled training color must belong to the supported palette', function () {
    $user = User::factory()->create();
    $trainee = $user->trainees()->create(scheduledTraineePayload());

    $this->actingAs($user)
        ->post(route('scheduled-trainings.store'), scheduledTrainingPayload([
            'trainee_id' => $trainee->id,
            'color' => 'transparent',
        ]))
        ->assertSessionHasErrors('color');
});

test('a user can schedule a training for their group', function () {
    $user = User::factory()->create();
    $trainingGroup = $user->trainingGroups()->create(scheduledTrainingGroupPayload());

    $response = $this->actingAs($user)->post(
        route('scheduled-trainings.store'),
        scheduledTrainingPayload(['training_group_id' => $trainingGroup->id]),
    );

    $scheduledTraining = ScheduledTraining::query()->sole();

    $response->assertRedirect(route('scheduled-trainings.show', $scheduledTraining));
    expect($scheduledTraining->trainingGroup?->is($trainingGroup))->toBeTrue();
});

test('a scheduled training requires exactly one trainee or training group', function () {
    $user = User::factory()->create();
    $trainee = $user->trainees()->create(scheduledTraineePayload());
    $trainingGroup = $user->trainingGroups()->create(scheduledTrainingGroupPayload());

    $this->actingAs($user)
        ->post(route('scheduled-trainings.store'), scheduledTrainingPayload())
        ->assertSessionHasErrors(['trainee_id', 'training_group_id']);

    $this->actingAs($user)
        ->post(route('scheduled-trainings.store'), scheduledTrainingPayload([
            'trainee_id' => $trainee->id,
            'training_group_id' => $trainingGroup->id,
        ]))
        ->assertSessionHasErrors(['trainee_id', 'training_group_id']);
});

test('a user cannot schedule a training for another users trainee or group', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $trainee = $otherUser->trainees()->create(scheduledTraineePayload());
    $trainingGroup = $otherUser->trainingGroups()->create(scheduledTrainingGroupPayload());

    $this->actingAs($user)
        ->post(
            route('scheduled-trainings.store'),
            scheduledTrainingPayload(['trainee_id' => $trainee->id]),
        )
        ->assertSessionHasErrors('trainee_id');

    $this->actingAs($user)
        ->post(
            route('scheduled-trainings.store'),
            scheduledTrainingPayload(['training_group_id' => $trainingGroup->id]),
        )
        ->assertSessionHasErrors('training_group_id');
});

test('a user can view edit and update their scheduled training', function () {
    $user = User::factory()->create();
    $trainee = $user->trainees()->create(scheduledTraineePayload());
    $scheduledTraining = $user->scheduledTrainings()->create(
        scheduledTrainingPayload(['trainee_id' => $trainee->id]),
    );

    $this->actingAs($user)
        ->get(route('scheduled-trainings.show', $scheduledTraining))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('scheduled-trainings/show')
            ->where('scheduledTraining.id', $scheduledTraining->id));

    $this->actingAs($user)
        ->get(route('scheduled-trainings.edit', $scheduledTraining))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('scheduled-trainings/edit')
            ->where('scheduledTraining.id', $scheduledTraining->id)
            ->has('trainees', 1)
            ->has('trainingGroups', 0));

    $this->actingAs($user)
        ->patch(route('scheduled-trainings.update', $scheduledTraining), [
            'starts_at' => '2026-07-07 20:00:00+03:00',
            'ends_at' => '2026-07-07 21:30:00+03:00',
            'location' => 'Зал №3',
            'status' => 'completed',
            'color' => 'purple',
            'notes' => 'Тренировка завершена',
        ])
        ->assertRedirect(route('scheduled-trainings.show', $scheduledTraining));

    $scheduledTraining->refresh();

    expect($scheduledTraining->location)->toBe('Зал №3')
        ->and($scheduledTraining->status)->toBe('completed')
        ->and($scheduledTraining->color)->toBe('purple')
        ->and($scheduledTraining->notes)->toBe('Тренировка завершена');
});

test('a training created for today appears on dashboard', function () {
    Date::setTestNow('2026-07-06 12:00:00');

    $user = User::factory()->create();
    $trainingGroup = $user->trainingGroups()->create(scheduledTrainingGroupPayload());

    $this->actingAs($user)->post(
        route('scheduled-trainings.store'),
        scheduledTrainingPayload([
            'training_group_id' => $trainingGroup->id,
            'starts_at' => '2026-07-06 18:00:00+03:00',
            'ends_at' => '2026-07-06 19:00:00+03:00',
        ]),
    );

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard')
            ->has('scheduledTrainings', 1)
            ->where('scheduledTrainings.0.subject_name', 'Группа U12'));
});

test('a user sees 404 for another users scheduled training', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $trainee = $otherUser->trainees()->create(scheduledTraineePayload());
    $scheduledTraining = $otherUser->scheduledTrainings()->create(
        scheduledTrainingPayload(['trainee_id' => $trainee->id]),
    );

    $this->actingAs($user)
        ->get(route('scheduled-trainings.show', $scheduledTraining))
        ->assertNotFound();

    $this->actingAs($user)
        ->get(route('scheduled-trainings.edit', $scheduledTraining))
        ->assertNotFound();

    $this->actingAs($user)
        ->patch(route('scheduled-trainings.update', $scheduledTraining), [
            'location' => 'Взлом',
        ])
        ->assertForbidden();

    $this->actingAs($user)
        ->delete(route('scheduled-trainings.destroy', $scheduledTraining))
        ->assertNotFound();

    $this->assertModelExists($scheduledTraining);
});

test('a user can delete their scheduled training', function () {
    $user = User::factory()->create();
    $trainee = $user->trainees()->create(scheduledTraineePayload());
    $scheduledTraining = $user->scheduledTrainings()->create(
        scheduledTrainingPayload(['trainee_id' => $trainee->id]),
    );

    $this->actingAs($user)
        ->delete(route('scheduled-trainings.destroy', $scheduledTraining))
        ->assertRedirect(route('scheduled-trainings.index'));

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
        'starts_at' => '2026-07-07 18:00:00+03:00',
        'ends_at' => '2026-07-07 19:00:00+03:00',
        'location' => 'Зал №2',
        'status' => 'planned',
        'color' => 'blue',
        'notes' => null,
    ], $overrides);
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function scheduledTraineePayload(array $overrides = []): array
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
function scheduledTrainingGroupPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Группа U12',
        'sport_type' => 'Функциональный тренинг',
        'age_range' => '10–12 лет',
        'level' => 'Средний',
        'goal' => 'Развить выносливость',
    ], $overrides);
}
