<?php

namespace Database\Factories;

use App\Models\TrainingPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrainingPlan>
 */
class TrainingPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'goal' => fake()->sentence(),
            'total_duration_minutes' => 60,
            'status' => 'draft',
            'source' => 'manual',
            'notes' => null,
            'warnings' => [],
            'ai_reasoning' => null,
        ];
    }
}
