<?php

use App\Models\Exercise;
use App\Models\User;
use Database\Seeders\ExerciseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('guests cannot access exercise pages', function () {
    $this->get(route('exercises.index'))->assertRedirect(route('login'));
    $this->get(route('exercises.create'))->assertRedirect(route('login'));
});

test('a user sees system and personal exercises but not another users exercises', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Exercise::factory()->system()->create(['name' => 'Системное упражнение']);
    Exercise::factory()->for($user)->create(['name' => 'Своё упражнение']);
    Exercise::factory()->for($otherUser)->create(['name' => 'Чужое упражнение']);

    $this->actingAs($user)
        ->get(route('exercises.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('exercises/index')
            ->has('exercises', 2)
            ->where('exercises', fn ($exercises): bool => collect($exercises)
                ->pluck('name')
                ->sort()
                ->values()
                ->all() === ['Своё упражнение', 'Системное упражнение']));
});

test('a user can create a personal exercise', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $response = $this->actingAs($user)->post(route('exercises.store'), [
        ...exercisePayload(),
        'user_id' => $otherUser->id,
        'is_system' => true,
    ]);

    $exercise = Exercise::query()->sole();

    $response->assertRedirect(route('exercises.show', $exercise));
    expect($exercise->user->is($user))->toBeTrue()
        ->and($exercise->is_system)->toBeFalse()
        ->and($exercise->tags)->toBe(['офп', 'без инвентаря']);
});

test('a user can view system and personal exercise cards', function () {
    $user = User::factory()->create();
    $systemExercise = Exercise::factory()->system()->create();
    $personalExercise = Exercise::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('exercises.show', $systemExercise))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('exercises/show')
            ->where('exercise.id', $systemExercise->id)
            ->where('canManage', false));

    $this->actingAs($user)
        ->get(route('exercises.show', $personalExercise))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('exercises/show')
            ->where('exercise.id', $personalExercise->id)
            ->where('canManage', true));
});

test('a user can update and delete their exercise', function () {
    $user = User::factory()->create();
    $exercise = Exercise::factory()->for($user)->create();

    $this->actingAs($user)
        ->patch(route('exercises.update', $exercise), exercisePayload([
            'name' => 'Обновлённое упражнение',
            'tags' => ['сила'],
        ]))
        ->assertRedirect(route('exercises.show', $exercise));

    expect($exercise->fresh()?->name)->toBe('Обновлённое упражнение')
        ->and($exercise->fresh()?->tags)->toBe(['сила']);

    $this->actingAs($user)
        ->delete(route('exercises.destroy', $exercise))
        ->assertRedirect(route('exercises.index'));

    $this->assertModelMissing($exercise);
});

test('system exercises are read only', function () {
    $user = User::factory()->create();
    $exercise = Exercise::factory()->system()->create();

    $this->actingAs($user)
        ->get(route('exercises.edit', $exercise))
        ->assertForbidden();

    $this->actingAs($user)
        ->patch(route('exercises.update', $exercise), exercisePayload())
        ->assertForbidden();

    $this->actingAs($user)
        ->delete(route('exercises.destroy', $exercise))
        ->assertForbidden();

    $this->assertModelExists($exercise);
});

test('a user cannot access another users exercise', function () {
    $user = User::factory()->create();
    $exercise = Exercise::factory()->create();

    $this->actingAs($user)
        ->get(route('exercises.show', $exercise))
        ->assertNotFound();

    $this->actingAs($user)
        ->get(route('exercises.edit', $exercise))
        ->assertNotFound();

    $this->actingAs($user)
        ->patch(route('exercises.update', $exercise), exercisePayload())
        ->assertForbidden();

    $this->actingAs($user)
        ->delete(route('exercises.destroy', $exercise))
        ->assertNotFound();
});

test('exercise data is validated', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('exercises.store'), exercisePayload([
            'name' => '',
            'difficulty' => 'Невозможная',
            'duration_minutes' => 0,
            'age_min' => 18,
            'age_max' => 10,
        ]))
        ->assertSessionHasErrors([
            'name',
            'difficulty',
            'duration_minutes',
            'age_max',
        ]);
});

test('the exercise seeder creates an idempotent system library', function () {
    $this->seed(ExerciseSeeder::class);
    $this->seed(ExerciseSeeder::class);

    expect(Exercise::query()->count())->toBe(25)
        ->and(Exercise::query()->where('is_system', true)->count())->toBe(25)
        ->and(Exercise::query()->whereNotNull('user_id')->count())->toBe(0);
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function exercisePayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Приседания',
        'description' => 'Контролируемые приседания с собственным весом.',
        'goal' => 'Сила',
        'difficulty' => 'Лёгкая',
        'equipment' => 'Без инвентаря',
        'duration_minutes' => 10,
        'contraindications' => null,
        'age_min' => 8,
        'age_max' => 65,
        'tags' => 'офп, без инвентаря',
    ], $overrides);
}
