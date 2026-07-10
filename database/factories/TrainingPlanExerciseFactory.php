<?php

namespace Database\Factories;

use App\Models\TrainingPlanExercise;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrainingPlanExercise>
 */
class TrainingPlanExerciseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'exercise_id' => null,
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'duration_minutes' => 10,
            'sets' => null,
            'repetitions' => null,
            'rest_seconds' => null,
            'position' => 1,
            'notes' => null,
        ];
    }
}
