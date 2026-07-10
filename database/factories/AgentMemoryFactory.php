<?php

namespace Database\Factories;

use App\Models\AgentMemory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AgentMemory>
 */
class AgentMemoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement([
                'restriction',
                'preference',
                'progress',
                'risk',
                'methodic_note',
                'general',
            ]),
            'content' => fake()->sentence(),
            'importance' => fake()->numberBetween(1, 10),
            'is_active' => true,
        ];
    }
}
