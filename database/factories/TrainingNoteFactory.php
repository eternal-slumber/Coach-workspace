<?php

namespace Database\Factories;

use App\Models\TrainingNote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrainingNote>
 */
class TrainingNoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'intensity' => fake()->randomElement(['low', 'medium', 'high']),
            'result' => fake()->randomElement(['bad', 'normal', 'good']),
            'tags' => ['повторить технику'],
            'note' => fake()->paragraph(),
        ];
    }
}
