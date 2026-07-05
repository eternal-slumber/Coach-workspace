<?php

use App\Models\TrainingGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests cannot access training groups', function () {
    $this->getJson(route('training-groups.index'))->assertUnauthorized();
});

test('a user can create a training group owned by themselves', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $response = $this->actingAs($user)->postJson(route('training-groups.store'), [
        ...trainingGroupPayload(),
        'user_id' => $otherUser->id,
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('name', 'Группа U12')
        ->assertJsonPath('user_id', $user->id);

    $trainingGroup = TrainingGroup::query()->sole();

    expect($trainingGroup->user->is($user))->toBeTrue();
});

test('a user sees only their training groups', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $user->trainingGroups()->create(trainingGroupPayload(['name' => 'Своя группа']));
    $otherUser->trainingGroups()->create(trainingGroupPayload(['name' => 'Чужая группа']));

    $this->actingAs($user)
        ->getJson(route('training-groups.index'))
        ->assertOk()
        ->assertJsonCount(1, 'training_groups')
        ->assertJsonPath('training_groups.0.name', 'Своя группа')
        ->assertJsonMissing(['name' => 'Чужая группа']);
});

test('a user can view update and delete their training group', function () {
    $user = User::factory()->create();
    $trainingGroup = $user->trainingGroups()->create(trainingGroupPayload());

    $this->actingAs($user)
        ->getJson(route('training-groups.show', $trainingGroup))
        ->assertOk()
        ->assertJsonPath('id', $trainingGroup->id);

    $this->actingAs($user)
        ->putJson(
            route('training-groups.update', $trainingGroup),
            trainingGroupPayload(['goal' => 'Новая цель группы']),
        )
        ->assertOk()
        ->assertJsonPath('goal', 'Новая цель группы');

    $this->actingAs($user)
        ->deleteJson(route('training-groups.destroy', $trainingGroup))
        ->assertNoContent();

    $this->assertModelMissing($trainingGroup);
});

test('a user cannot access another users training group', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $trainingGroup = $otherUser->trainingGroups()->create(trainingGroupPayload());

    $this->actingAs($user)
        ->getJson(route('training-groups.show', $trainingGroup))
        ->assertNotFound();

    $this->actingAs($user)
        ->putJson(
            route('training-groups.update', $trainingGroup),
            trainingGroupPayload(['name' => 'Взлом']),
        )
        ->assertForbidden();

    $this->actingAs($user)
        ->deleteJson(route('training-groups.destroy', $trainingGroup))
        ->assertNotFound();

    expect($trainingGroup->fresh()?->name)->toBe('Группа U12');
});

test('training group data is validated', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('training-groups.store'), trainingGroupPayload(['sport_type' => '']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('sport_type');
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function trainingGroupPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Группа U12',
        'sport_type' => 'Функциональный тренинг',
        'age_range' => '10–12 лет',
        'level' => 'Средний',
        'goal' => 'Развить выносливость',
        'restrictions' => null,
        'notes' => null,
    ], $overrides);
}
