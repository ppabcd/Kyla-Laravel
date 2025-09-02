<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pair>
 */
class PairFactory extends Factory
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
            'partner_id' => \App\Models\User::factory(),
            'status' => 'active',
            'active' => true,
            'started_at' => now(),
            'ended_at' => null,
            'ended_by_user_id' => null,
            'ended_reason' => null,
            'metadata' => null,
        ];
    }
}
