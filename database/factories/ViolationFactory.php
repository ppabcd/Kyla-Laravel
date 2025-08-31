<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Violation>
 */
class ViolationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'content' => fake()->sentence(),
            'violation_type' => fake()->randomElement(['promotion', 'spam', 'inappropriate', 'harassment']),
            'severity' => fake()->numberBetween(1, 5),
            'detected_at' => now(),
            'action_taken' => null,
            'ban_duration_minutes' => null,
        ];
    }
}
