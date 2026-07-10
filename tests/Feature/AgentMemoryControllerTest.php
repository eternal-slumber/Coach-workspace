<?php

use App\Models\AgentMemory;
use App\Models\Trainee;
use App\Models\TrainingGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('guests cannot store or update memories', function () {
    $user = User::factory()->create();
    $trainee = createMemoryTrainee($user);
    $agentMemory = createMemoryRecord($user, trainee: $trainee);

    $this->post(route('agent-memories.store'), agentMemoryPayload([
        'trainee_id' => $trainee->id,
    ]))->assertRedirect(route('login'));

    $this->patch(
        route('agent-memories.update', $agentMemory),
        agentMemoryPayload(),
    )->assertRedirect(route('login'));
});

test('a user can create memory for their trainee', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $trainee = createMemoryTrainee($user);

    $response = $this->actingAs($user)->post(
        route('agent-memories.store'),
        agentMemoryPayload([
            'trainee_id' => $trainee->id,
            'user_id' => $otherUser->id,
        ]),
    );

    $agentMemory = AgentMemory::query()->sole();

    $response->assertRedirect(route('trainees.show', $trainee));
    expect($agentMemory->user->is($user))->toBeTrue()
        ->and($agentMemory->trainee?->is($trainee))->toBeTrue()
        ->and($agentMemory->training_group_id)->toBeNull()
        ->and($agentMemory->type)->toBe('restriction')
        ->and($agentMemory->importance)->toBe(8)
        ->and($agentMemory->is_active)->toBeTrue();
});

test('a user can create memory for their training group', function () {
    $user = User::factory()->create();
    $trainingGroup = createMemoryTrainingGroup($user);

    $this->actingAs($user)
        ->post(route('agent-memories.store'), agentMemoryPayload([
            'training_group_id' => $trainingGroup->id,
        ]))
        ->assertRedirect(route('training-groups.show', $trainingGroup));

    $agentMemory = AgentMemory::query()->sole();

    expect($agentMemory->trainee_id)->toBeNull()
        ->and($agentMemory->trainingGroup?->is($trainingGroup))->toBeTrue();
});

test('memory requires exactly one trainee or training group', function () {
    $user = User::factory()->create();
    $trainee = createMemoryTrainee($user);
    $trainingGroup = createMemoryTrainingGroup($user);

    $this->actingAs($user)
        ->post(route('agent-memories.store'), agentMemoryPayload())
        ->assertSessionHasErrors(['trainee_id', 'training_group_id']);

    $this->actingAs($user)
        ->post(route('agent-memories.store'), agentMemoryPayload([
            'trainee_id' => $trainee->id,
            'training_group_id' => $trainingGroup->id,
        ]))
        ->assertSessionHasErrors(['trainee_id', 'training_group_id']);

    expect(AgentMemory::query()->count())->toBe(0);
});

test('a user cannot create memory for another users subject', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $trainee = createMemoryTrainee($otherUser);
    $trainingGroup = createMemoryTrainingGroup($otherUser);

    $this->actingAs($user)
        ->post(route('agent-memories.store'), agentMemoryPayload([
            'trainee_id' => $trainee->id,
        ]))
        ->assertSessionHasErrors('trainee_id');

    $this->actingAs($user)
        ->post(route('agent-memories.store'), agentMemoryPayload([
            'training_group_id' => $trainingGroup->id,
        ]))
        ->assertSessionHasErrors('training_group_id');

    expect(AgentMemory::query()->count())->toBe(0);
});

test('memory appears only in its owners trainee card', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $trainee = createMemoryTrainee($user);

    $activeMemory = createMemoryRecord($user, trainee: $trainee, overrides: [
        'content' => 'Активная память',
        'importance' => 7,
    ]);
    createMemoryRecord($user, trainee: $trainee, overrides: [
        'content' => 'Отключённая память',
        'is_active' => false,
        'importance' => 10,
    ]);
    AgentMemory::factory()->create([
        'user_id' => $otherUser->id,
        'trainee_id' => $trainee->id,
        'training_group_id' => null,
        'content' => 'Чужая память',
    ]);

    $this->actingAs($user)
        ->get(route('trainees.show', $trainee))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('agentMemories', 2)
            ->where('agentMemories.0.id', $activeMemory->id)
            ->where('agentMemories.0.content', 'Активная память')
            ->where('agentMemories.1.content', 'Отключённая память'));
});

test('memory appears in its training group card', function () {
    $user = User::factory()->create();
    $trainingGroup = createMemoryTrainingGroup($user);
    $agentMemory = createMemoryRecord($user, trainingGroup: $trainingGroup);

    $this->actingAs($user)
        ->get(route('training-groups.show', $trainingGroup))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('agentMemories', 1)
            ->where('agentMemories.0.id', $agentMemory->id));
});

test('a user can edit and disable their memory without changing its subject', function () {
    $user = User::factory()->create();
    $trainee = createMemoryTrainee($user);
    $trainingGroup = createMemoryTrainingGroup($user);
    $agentMemory = createMemoryRecord($user, trainee: $trainee);

    $this->actingAs($user)
        ->patch(route('agent-memories.update', $agentMemory), agentMemoryPayload([
            'type' => 'progress',
            'content' => 'Координация заметно улучшилась.',
            'importance' => 6,
            'is_active' => false,
            'trainee_id' => null,
            'training_group_id' => $trainingGroup->id,
        ]))
        ->assertRedirect(route('trainees.show', $trainee));

    $agentMemory->refresh();

    expect($agentMemory->type)->toBe('progress')
        ->and($agentMemory->content)->toBe('Координация заметно улучшилась.')
        ->and($agentMemory->importance)->toBe(6)
        ->and($agentMemory->is_active)->toBeFalse()
        ->and($agentMemory->trainee_id)->toBe($trainee->id)
        ->and($agentMemory->training_group_id)->toBeNull();
});

test('a user cannot edit another users memory', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $trainee = createMemoryTrainee($otherUser);
    $agentMemory = createMemoryRecord($otherUser, trainee: $trainee);

    $this->actingAs($user)
        ->patch(
            route('agent-memories.update', $agentMemory),
            agentMemoryPayload(['content' => 'Взлом']),
        )
        ->assertForbidden();

    expect($agentMemory->fresh()?->content)->not->toBe('Взлом');
});

test('memory data is validated', function () {
    $user = User::factory()->create();
    $trainee = createMemoryTrainee($user);

    $this->actingAs($user)
        ->post(route('agent-memories.store'), agentMemoryPayload([
            'trainee_id' => $trainee->id,
            'type' => 'unknown',
            'content' => '',
            'importance' => 11,
            'is_active' => 'maybe',
        ]))
        ->assertSessionHasErrors([
            'type',
            'content',
            'importance',
            'is_active',
        ]);
});

function createMemoryTrainee(User $user): Trainee
{
    return $user->trainees()->create([
        'name' => 'Алексей Смирнов',
        'age' => 12,
        'level' => 'Средний',
        'goal' => 'Координация',
    ]);
}

function createMemoryTrainingGroup(User $user): TrainingGroup
{
    return $user->trainingGroups()->create([
        'name' => 'Группа U12',
        'sport_type' => 'ОФП',
        'age_range' => '10–12',
        'level' => 'Средний',
        'goal' => 'Координация',
    ]);
}

/**
 * @param  array<string, mixed>  $overrides
 */
function createMemoryRecord(
    User $user,
    ?Trainee $trainee = null,
    ?TrainingGroup $trainingGroup = null,
    array $overrides = [],
): AgentMemory {
    return AgentMemory::factory()->create([
        'user_id' => $user->id,
        'trainee_id' => $trainee?->id,
        'training_group_id' => $trainingGroup?->id,
        ...$overrides,
    ]);
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function agentMemoryPayload(array $overrides = []): array
{
    return array_merge([
        'trainee_id' => null,
        'training_group_id' => null,
        'type' => 'restriction',
        'content' => 'Не давать длительную прыжковую нагрузку.',
        'importance' => 8,
        'is_active' => true,
    ], $overrides);
}
