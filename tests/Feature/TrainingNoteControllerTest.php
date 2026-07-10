<?php

use App\Models\ScheduledTraining;
use App\Models\TrainingNote;
use App\Models\TrainingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('a user can add a note to their completed trainee plan', function () {
    $user = User::factory()->create();
    [$trainingPlan] = createNotePlan($user);
    $otherUser = User::factory()->create();

    $response = $this->actingAs($user)->post(
        route('training-plans.note.store', $trainingPlan),
        [
            ...trainingNotePayload(),
            'user_id' => $otherUser->id,
            'trainee_id' => null,
            'training_group_id' => 999,
        ],
    );

    $trainingNote = TrainingNote::query()->sole();

    $response->assertRedirect(route('training-plans.show', $trainingPlan));
    expect($trainingNote->user->is($user))->toBeTrue()
        ->and($trainingNote->trainingPlan->is($trainingPlan))->toBeTrue()
        ->and($trainingNote->trainee_id)->toBe($trainingPlan->trainee_id)
        ->and($trainingNote->training_group_id)->toBeNull()
        ->and($trainingNote->tags)->toBe(['устали', 'повторить технику']);
});

test('a group note inherits the group from its plan', function () {
    $user = User::factory()->create();
    [$trainingPlan] = createNotePlan($user, 'group');

    $this->actingAs($user)
        ->post(
            route('training-plans.note.store', $trainingPlan),
            trainingNotePayload(),
        )
        ->assertRedirect(route('training-plans.show', $trainingPlan));

    $trainingNote = TrainingNote::query()->sole();

    expect($trainingNote->trainee_id)->toBeNull()
        ->and($trainingNote->training_group_id)->toBe($trainingPlan->training_group_id);
});

test('a note cannot be added before the plan is completed', function () {
    $user = User::factory()->create();
    [$trainingPlan] = createNotePlan($user, status: 'approved');

    $this->actingAs($user)
        ->post(
            route('training-plans.note.store', $trainingPlan),
            trainingNotePayload(),
        )
        ->assertSessionHasErrors('training_plan');

    expect(TrainingNote::query()->count())->toBe(0);
});

test('a user cannot add a note to another users plan', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    [$trainingPlan] = createNotePlan($otherUser);

    $this->actingAs($user)
        ->post(
            route('training-plans.note.store', $trainingPlan),
            trainingNotePayload(),
        )
        ->assertForbidden();

    expect(TrainingNote::query()->count())->toBe(0);
});

test('a completed plan can only have one note', function () {
    $user = User::factory()->create();
    [$trainingPlan] = createNotePlan($user);
    createNoteRecord($user, $trainingPlan);

    $this->actingAs($user)
        ->post(
            route('training-plans.note.store', $trainingPlan),
            trainingNotePayload(),
        )
        ->assertSessionHasErrors('training_plan');

    expect(TrainingNote::query()->count())->toBe(1);
});

test('a user can update their training note', function () {
    $user = User::factory()->create();
    [$trainingPlan] = createNotePlan($user);
    $trainingNote = createNoteRecord($user, $trainingPlan);

    $this->actingAs($user)
        ->patch(route('training-notes.update', $trainingNote), [
            ...trainingNotePayload(),
            'intensity' => 'high',
            'result' => 'good',
            'tags' => 'увеличить сложность, оставить тот же уровень',
            'note' => 'Группа справилась лучше ожидаемого.',
        ])
        ->assertRedirect(route('training-plans.show', $trainingPlan));

    $trainingNote->refresh();

    expect($trainingNote->intensity)->toBe('high')
        ->and($trainingNote->result)->toBe('good')
        ->and($trainingNote->tags)->toBe(['увеличить сложность', 'оставить тот же уровень'])
        ->and($trainingNote->note)->toBe('Группа справилась лучше ожидаемого.');
});

test('a user cannot update another users note', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    [$trainingPlan] = createNotePlan($otherUser);
    $trainingNote = createNoteRecord($otherUser, $trainingPlan);

    $this->actingAs($user)
        ->patch(
            route('training-notes.update', $trainingNote),
            trainingNotePayload(['note' => 'Взлом']),
        )
        ->assertForbidden();

    expect($trainingNote->fresh()?->note)->not->toBe('Взлом');
});

test('training note data is validated', function () {
    $user = User::factory()->create();
    [$trainingPlan] = createNotePlan($user);

    $this->actingAs($user)
        ->post(route('training-plans.note.store', $trainingPlan), [
            'intensity' => 'extreme',
            'result' => 'perfect',
            'tags' => [],
            'note' => '',
        ])
        ->assertSessionHasErrors(['intensity', 'result', 'note']);
});

test('a note is displayed on the plan and trainee history', function () {
    $user = User::factory()->create();
    [$trainingPlan] = createNotePlan($user);
    $trainingNote = createNoteRecord($user, $trainingPlan);

    $this->actingAs($user)
        ->get(route('training-plans.show', $trainingPlan))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('trainingPlan.training_note.id', $trainingNote->id)
            ->where('trainingPlan.training_note.intensity', 'medium'));

    $this->actingAs($user)
        ->get(route('trainees.show', $trainingPlan->trainee_id))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('trainingHistory', 1)
            ->where('trainingHistory.0.training_note.note', $trainingNote->note)
            ->where('trainingHistory.0.training_note.tags', $trainingNote->tags));
});

test('a note is displayed in the training group history', function () {
    $user = User::factory()->create();
    [$trainingPlan] = createNotePlan($user, 'group');
    $trainingNote = createNoteRecord($user, $trainingPlan);

    $this->actingAs($user)
        ->get(route('training-groups.show', $trainingPlan->training_group_id))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('trainingHistory', 1)
            ->where('trainingHistory.0.training_note.result', $trainingNote->result));
});

/**
 * @return array{TrainingPlan, ScheduledTraining}
 */
function createNotePlan(
    User $user,
    string $subject = 'trainee',
    string $status = 'completed',
): array {
    if ($subject === 'group') {
        $trainingGroup = $user->trainingGroups()->create([
            'name' => 'Группа U12',
            'sport_type' => 'ОФП',
            'age_range' => '10–12',
            'level' => 'Средний',
            'goal' => 'Координация',
        ]);
        $subjectAttributes = ['training_group_id' => $trainingGroup->id];
    } else {
        $trainee = $user->trainees()->create([
            'name' => 'Алексей Смирнов',
            'age' => 12,
            'level' => 'Средний',
            'goal' => 'Координация',
        ]);
        $subjectAttributes = ['trainee_id' => $trainee->id];
    }

    $scheduledTraining = $user->scheduledTrainings()->create([
        'trainee_id' => null,
        'training_group_id' => null,
        ...$subjectAttributes,
        'starts_at' => '2026-07-08 18:00:00+03:00',
        'ends_at' => '2026-07-08 19:00:00+03:00',
        'location' => 'Зал №1',
        'status' => $status === 'completed' ? 'completed' : 'planned',
        'color' => 'blue',
    ]);

    $trainingPlan = TrainingPlan::factory()->create([
        'user_id' => $user->id,
        'scheduled_training_id' => $scheduledTraining->id,
        'trainee_id' => $scheduledTraining->trainee_id,
        'training_group_id' => $scheduledTraining->training_group_id,
        'status' => $status,
        'title' => 'Координация и ОФП',
    ]);

    return [$trainingPlan, $scheduledTraining];
}

function createNoteRecord(User $user, TrainingPlan $trainingPlan): TrainingNote
{
    return TrainingNote::factory()->create([
        'user_id' => $user->id,
        'training_plan_id' => $trainingPlan->id,
        'trainee_id' => $trainingPlan->trainee_id,
        'training_group_id' => $trainingPlan->training_group_id,
        ...trainingNotePayload(),
        'tags' => ['устали', 'повторить технику'],
    ]);
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function trainingNotePayload(array $overrides = []): array
{
    return array_merge([
        'intensity' => 'medium',
        'result' => 'normal',
        'tags' => 'устали, повторить технику',
        'note' => 'Хорошо справились с разминкой, но нужно повторить технику.',
    ], $overrides);
}
