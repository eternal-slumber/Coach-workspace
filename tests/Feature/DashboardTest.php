<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

afterEach(function () {
    Date::setTestNow();
});

test('guests are redirected to the login page', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('authenticated users see an empty overview when they have no upcoming trainings', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard')
            ->has('scheduledTrainings', 0)
            ->has('days', 7)
            ->where('days.0.scheduled_trainings', []));
});

test('dashboard shows upcoming trainings grouped by day for the current user', function () {
    Date::setTestNow('2026-07-06 12:00:00');

    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $trainee = $user->trainees()->create(dashboardTraineePayload());
    $trainingGroup = $user->trainingGroups()->create(dashboardTrainingGroupPayload());
    $otherTrainingGroup = $otherUser->trainingGroups()->create(dashboardTrainingGroupPayload());

    $user->scheduledTrainings()->create(dashboardTrainingPayload([
        'trainee_id' => $trainee->id,
        'starts_at' => Date::parse('2026-07-06 18:00:00'),
        'ends_at' => Date::parse('2026-07-06 19:00:00'),
    ]));
    $user->scheduledTrainings()->create(dashboardTrainingPayload([
        'training_group_id' => $trainingGroup->id,
        'starts_at' => Date::parse('2026-07-06 09:00:00'),
        'ends_at' => Date::parse('2026-07-06 10:00:00'),
    ]));
    $user->scheduledTrainings()->create(dashboardTrainingPayload([
        'training_group_id' => $trainingGroup->id,
        'starts_at' => Date::parse('2026-07-05 18:00:00'),
        'ends_at' => Date::parse('2026-07-05 19:00:00'),
    ]));
    $user->scheduledTrainings()->create(dashboardTrainingPayload([
        'training_group_id' => $trainingGroup->id,
        'starts_at' => Date::parse('2026-07-10 17:00:00'),
        'ends_at' => Date::parse('2026-07-10 18:00:00'),
    ]));
    $otherUser->scheduledTrainings()->create(dashboardTrainingPayload([
        'training_group_id' => $otherTrainingGroup->id,
        'starts_at' => Date::parse('2026-07-06 08:00:00'),
        'ends_at' => Date::parse('2026-07-06 09:00:00'),
    ]));

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard')
            ->has('scheduledTrainings', 3)
            ->has('days', 7)
            ->where('days.0.title', 'Сегодня, 6 июля')
            ->where('days.1.title', 'Завтра, 7 июля')
            ->where('days.0.scheduled_trainings.0.subject_name', 'Группа U12')
            ->where('days.0.scheduled_trainings.1.subject_name', 'Алексей Смирнов')
            ->where('days.4.scheduled_trainings.0.subject_name', 'Группа U12')
            ->where('scheduledTrainings.0.subject_name', 'Группа U12')
            ->where('scheduledTrainings.0.subject_type', 'training_group')
            ->where('scheduledTrainings.0.location', 'Зал №1')
            ->where('scheduledTrainings.0.status', 'planned')
            ->where('scheduledTrainings.0.color', 'blue')
            ->where('scheduledTrainings.1.subject_name', 'Алексей Смирнов')
            ->where('scheduledTrainings.1.subject_type', 'trainee'));
});

/**
 * @return array<string, mixed>
 */
function dashboardTraineePayload(): array
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
function dashboardTrainingGroupPayload(): array
{
    return [
        'name' => 'Группа U12',
        'sport_type' => 'ОФП',
        'age_range' => '10–12 лет',
        'level' => 'Начинающий',
        'goal' => 'Координация и общая физическая подготовка',
    ];
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function dashboardTrainingPayload(array $overrides = []): array
{
    return array_merge([
        'trainee_id' => null,
        'training_group_id' => null,
        'starts_at' => Date::parse('2026-07-06 18:00:00'),
        'ends_at' => Date::parse('2026-07-06 19:00:00'),
        'location' => 'Зал №1',
        'status' => 'planned',
        'color' => 'blue',
    ], $overrides);
}
