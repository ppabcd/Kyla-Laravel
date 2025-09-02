<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ConversationLog>
 */
class ConversationLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conv_id' => fake()->uuid(),
            'user_id' => \App\Models\User::factory(),
            'chat_id' => fake()->randomNumber(8),
            'message_id' => fake()->randomNumber(8),
            'is_action' => fake()->boolean(),
        ];
    }
}
