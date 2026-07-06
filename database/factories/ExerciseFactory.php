<?php

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Exercise>
 */
class ExerciseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'goal' => fake()->randomElement(['Разминка', 'Координация', 'Сила', 'Мобильность']),
            'difficulty' => fake()->randomElement(['Лёгкая', 'Средняя', 'Высокая']),
            'equipment' => fake()->randomElement(['Без инвентаря', 'Мяч', 'Конусы']),
            'duration_minutes' => fake()->numberBetween(5, 30),
            'contraindications' => null,
            'age_min' => 8,
            'age_max' => 60,
            'tags' => ['офп', 'координация'],
            'is_system' => false,
        ];
    }

    public function system(): static
    {
        return $this->state(fn (): array => [
            'user_id' => null,
            'is_system' => true,
        ]);
    }
}
