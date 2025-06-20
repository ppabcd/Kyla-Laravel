<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Entities\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create some sample users for testing
        $users = [
            [
                'telegram_id' => 123456789,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'username' => 'johndoe',
                'language_code' => 'en',
                'gender' => 'male',
                'interest' => 'female',
                'age' => 25,
                'location' => 'New York',
                'is_premium' => false,
                'is_banned' => false,
                'last_activity_at' => now(),
                'settings' => [
                    'notifications' => true,
                    'privacy' => 'public',
                    'safe_mode' => true
                ]
            ],
            [
                'telegram_id' => 987654321,
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'username' => 'janesmith',
                'language_code' => 'en',
                'gender' => 'female',
                'interest' => 'male',
                'age' => 23,
                'location' => 'Los Angeles',
                'is_premium' => true,
                'is_banned' => false,
                'premium_expires_at' => now()->addDays(30),
                'last_activity_at' => now(),
                'settings' => [
                    'notifications' => true,
                    'privacy' => 'public',
                    'safe_mode' => true
                ]
            ],
            [
                'telegram_id' => 555666777,
                'first_name' => 'Mike',
                'last_name' => 'Johnson',
                'username' => 'mikejohnson',
                'language_code' => 'en',
                'gender' => 'male',
                'interest' => 'female',
                'age' => 28,
                'location' => 'Chicago',
                'is_premium' => false,
                'is_banned' => false,
                'last_activity_at' => now()->subHours(2),
                'settings' => [
                    'notifications' => true,
                    'privacy' => 'public',
                    'safe_mode' => true
                ]
            ],
            [
                'telegram_id' => 111222333,
                'first_name' => 'Sarah',
                'last_name' => 'Wilson',
                'username' => 'sarahwilson',
                'language_code' => 'en',
                'gender' => 'female',
                'interest' => 'male',
                'age' => 26,
                'location' => 'Miami',
                'is_premium' => false,
                'is_banned' => false,
                'last_activity_at' => now()->subHours(1),
                'settings' => [
                    'notifications' => true,
                    'privacy' => 'public',
                    'safe_mode' => true
                ]
            ]
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['telegram_id' => $userData['telegram_id']],
                $userData
            );
        }
    }
} 
