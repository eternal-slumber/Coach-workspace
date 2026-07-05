<?php

use App\Models\Trainee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests cannot access trainees', function () {
    $this->getJson(route('trainees.index'))->assertUnauthorized();
});

test('a user can create a trainee owned by themselves', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $response = $this->actingAs($user)->postJson(route('trainees.store'), [
        ...traineePayload(),
        'user_id' => $otherUser->id,
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('name', 'Алексей Смирнов')
        ->assertJsonPath('user_id', $user->id);

    $trainee = Trainee::query()->sole();

    expect($trainee->user->is($user))->toBeTrue();
});

test('a user sees only their trainees', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $user->trainees()->create(traineePayload(['name' => 'Свой клиент']));
    $otherUser->trainees()->create(traineePayload(['name' => 'Чужой клиент']));

    $this->actingAs($user)
        ->getJson(route('trainees.index'))
        ->assertOk()
        ->assertJsonCount(1, 'trainees')
        ->assertJsonPath('trainees.0.name', 'Свой клиент')
        ->assertJsonMissing(['name' => 'Чужой клиент']);
});

test('a user can view update and delete their trainee', function () {
    $user = User::factory()->create();
    $trainee = $user->trainees()->create(traineePayload());

    $this->actingAs($user)
        ->getJson(route('trainees.show', $trainee))
        ->assertOk()
        ->assertJsonPath('id', $trainee->id);

    $this->actingAs($user)
        ->putJson(route('trainees.update', $trainee), traineePayload(['goal' => 'Новая цель']))
        ->assertOk()
        ->assertJsonPath('goal', 'Новая цель');

    expect($trainee->fresh()?->goal)->toBe('Новая цель');

    $this->actingAs($user)
        ->deleteJson(route('trainees.destroy', $trainee))
        ->assertNoContent();

    $this->assertModelMissing($trainee);
});

test('a user cannot access another users trainee', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $trainee = $otherUser->trainees()->create(traineePayload());

    $this->actingAs($user)
        ->getJson(route('trainees.show', $trainee))
        ->assertNotFound();

    $this->actingAs($user)
        ->putJson(route('trainees.update', $trainee), traineePayload(['name' => 'Взлом']))
        ->assertForbidden();

    $this->actingAs($user)
        ->deleteJson(route('trainees.destroy', $trainee))
        ->assertNotFound();

    expect($trainee->fresh()?->name)->toBe('Алексей Смирнов');
});

test('trainee data is validated', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('trainees.store'), traineePayload(['name' => '', 'age' => 0]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'age']);
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function traineePayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Алексей Смирнов',
        'age' => 28,
        'level' => 'Начинающий',
        'goal' => 'Улучшить общую физическую форму',
        'restrictions' => null,
        'notes' => null,
    ], $overrides);
}
