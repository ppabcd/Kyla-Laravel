<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'telegram_id' => fake()->unique()->randomNumber(9),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'username' => fake()->optional()->userName(),
            'language_code' => fake()->randomElement(['en', 'id', 'ms']),
            'gender' => fake()->randomElement(['male', 'female']),
            'interest' => fake()->randomElement(['male', 'female']),
            'age' => fake()->numberBetween(18, 50),
            'location' => fake()->optional()->city(),
            'is_premium' => fake()->boolean(20), // 20% chance
            'is_banned' => fake()->boolean(5),   // 5% chance
            'is_searching' => fake()->boolean(30), // 30% chance
            'balance' => fake()->numberBetween(0, 1000),
            'last_activity_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
