<?php

namespace Database\Factories;

use App\Models\AiRequestLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiRequestLog>
 */
class AiRequestLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'provider' => 'openrouter',
            'model' => 'google/gemini-2.5-flash-lite',
            'status' => 'success',
            'prompt_preview' => '[user] Ответь одним словом: работает',
            'response_preview' => 'Работает',
            'error_message' => null,
            'duration_ms' => fake()->numberBetween(50, 2000),
        ];
    }
}
