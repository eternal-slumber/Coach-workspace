<?php

namespace Database\Factories;

use App\Models\TrainingPlanBlock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrainingPlanBlock>
 */
class TrainingPlanBlockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Разминка', 'Основная часть', 'Заминка']),
            'duration_minutes' => 15,
            'position' => 1,
            'notes' => null,
        ];
    }
}
