<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PairPending>
 */
class PairPendingFactory extends Factory
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
            'gender' => fake()->randomElement([1, 2]), // 1 = male, 2 = female
            'interest' => fake()->randomElement([1, 2, null]), // null = all
            'emoji' => fake()->randomElement(['😊', '😎', '🥰', '😍']),
            'language' => fake()->randomElement(['en', 'id', 'ms']),
            'platform_id' => 1,
            'is_premium' => fake()->boolean(20), // 20% chance of premium
            'is_safe_mode' => fake()->boolean(80), // 80% chance of safe mode
        ];
    }
}
