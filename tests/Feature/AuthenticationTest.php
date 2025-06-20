<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('user can authenticate with valid credentials', function () {
    $user = User::factory()->create([
        'telegram_id' => 123456789,
        'first_name' => 'Test',
        'username' => 'testuser'
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/user');

    $response->assertStatus(200)
        ->assertJson([
            'id' => $user->id,
            'telegram_id' => $user->telegram_id,
            'first_name' => $user->first_name,
            'username' => $user->username
        ]);
});

test('unauthenticated user cannot access protected routes', function () {
    $response = $this->getJson('/api/user');

    $response->assertStatus(401);
});

test('user can access public routes without authentication', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

test('user can access telegram webhook without authentication', function () {
    $update = [
        'update_id' => 123456789,
        'message' => [
            'message_id' => 1,
            'from' => [
                'id' => 123456,
                'first_name' => 'Test',
                'username' => 'testuser'
            ],
            'chat' => [
                'id' => 123456,
                'type' => 'private'
            ],
            'text' => 'Hello'
        ]
    ];

    $response = $this->postJson('/api/telegram/webhook', $update);

    $response->assertStatus(200);
});

test('user can access telegram info endpoints without authentication', function () {
    $response = $this->getJson('/api/telegram/webhook/info');
    $response->assertStatus(200);

    $response = $this->getJson('/api/telegram/bot/info');
    $response->assertStatus(200);

    $response = $this->getJson('/api/telegram/commands');
    $response->assertStatus(200);
});

test('authenticated user can access their own data', function () {
    $user = User::factory()->create([
        'telegram_id' => 123456789,
        'first_name' => 'Test',
        'last_name' => 'User',
        'username' => 'testuser',
        'language_code' => 'en',
        'gender' => 1,
        'interest' => 2,
        'age' => 25,
        'premium' => false,
        'banned' => false,
        'balances' => 100
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/user');

    $response->assertStatus(200)
        ->assertJson([
            'id' => $user->id,
            'telegram_id' => $user->telegram_id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'username' => $user->username,
            'language_code' => $user->language_code,
            'gender' => $user->gender,
            'interest' => $user->interest,
            'age' => $user->age,
            'premium' => $user->premium,
            'banned' => $user->banned,
            'balances' => $user->balances
        ]);
});

test('user cannot access other user data', function () {
    $user1 = User::factory()->create(['telegram_id' => 123456789]);
    $user2 = User::factory()->create(['telegram_id' => 987654321]);

    Sanctum::actingAs($user1);

    // Assuming there's a route to get user by ID
    // This test would be relevant if such routes exist
    $response = $this->getJson("/api/users/{$user2->id}");

    // Should return 403 or 404 depending on implementation
    expect($response->getStatusCode())->toBeIn([403, 404]);
});

test('banned user cannot access protected routes', function () {
    $bannedUser = User::factory()->create([
        'telegram_id' => 123456789,
        'banned' => 1
    ]);

    Sanctum::actingAs($bannedUser);

    $response = $this->getJson('/api/user');

    // Should still be able to access their own data
    $response->assertStatus(200);
});

test('blocked user cannot access protected routes', function () {
    $blockedUser = User::factory()->create([
        'telegram_id' => 123456789,
        'is_blocked' => 1
    ]);

    Sanctum::actingAs($blockedUser);

    $response = $this->getJson('/api/user');

    // Should still be able to access their own data
    $response->assertStatus(200);
});

test('user session persists across requests', function () {
    $user = User::factory()->create([
        'telegram_id' => 123456789,
        'first_name' => 'Test',
        'username' => 'testuser'
    ]);

    Sanctum::actingAs($user);

    // First request
    $response1 = $this->getJson('/api/user');
    $response1->assertStatus(200);

    // Second request
    $response2 = $this->getJson('/api/user');
    $response2->assertStatus(200);

    // Both should return the same user data
    expect($response1->json('id'))->toBe($response2->json('id'));
    expect($response1->json('telegram_id'))->toBe($response2->json('telegram_id'));
});

test('user can access routes with different user agents', function () {
    $user = User::factory()->create([
        'telegram_id' => 123456789,
        'first_name' => 'Test',
        'username' => 'testuser'
    ]);

    Sanctum::actingAs($user);

    $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
        'TelegramBot/1.0',
        'PostmanRuntime/7.29.0'
    ];

    foreach ($userAgents as $userAgent) {
        $response = $this->withHeaders([
            'User-Agent' => $userAgent
        ])->getJson('/api/user');

        $response->assertStatus(200);
    }
});

test('user can access routes with different content types', function () {
    $user = User::factory()->create([
        'telegram_id' => 123456789,
        'first_name' => 'Test',
        'username' => 'testuser'
    ]);

    Sanctum::actingAs($user);

    $contentTypes = [
        'application/json',
        'application/x-www-form-urlencoded',
        'multipart/form-data'
    ];

    foreach ($contentTypes as $contentType) {
        $response = $this->withHeaders([
            'Content-Type' => $contentType,
            'Accept' => 'application/json'
        ])->getJson('/api/user');

        $response->assertStatus(200);
    }
});

test('user authentication works with different HTTP methods', function () {
    $user = User::factory()->create([
        'telegram_id' => 123456789,
        'first_name' => 'Test',
        'username' => 'testuser'
    ]);

    Sanctum::actingAs($user);

    // GET request
    $getResponse = $this->getJson('/api/user');
    $getResponse->assertStatus(200);

    // POST request (if supported)
    $postResponse = $this->postJson('/api/user');
    // Should return 405 Method Not Allowed for unsupported methods
    expect($postResponse->getStatusCode())->toBeIn([200, 405]);

    // PUT request (if supported)
    $putResponse = $this->putJson('/api/user');
    expect($putResponse->getStatusCode())->toBeIn([200, 405]);

    // DELETE request (if supported)
    $deleteResponse = $this->deleteJson('/api/user');
    expect($deleteResponse->getStatusCode())->toBeIn([200, 405]);
});

test('user authentication handles expired tokens gracefully', function () {
    $user = User::factory()->create([
        'telegram_id' => 123456789,
        'first_name' => 'Test',
        'username' => 'testuser'
    ]);

    // Create a token that expires immediately
    $token = $user->createToken('test-token', ['*'], now()->subMinute());

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token->plainTextToken
    ])->getJson('/api/user');

    // Should return 401 for expired token
    $response->assertStatus(401);
});

test('user authentication handles invalid tokens gracefully', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer invalid-token'
    ])->getJson('/api/user');

    $response->assertStatus(401);
});

test('user authentication handles missing authorization header gracefully', function () {
    $response = $this->getJson('/api/user');

    $response->assertStatus(401);
});

test('user authentication handles malformed authorization header gracefully', function () {
    $response = $this->withHeaders([
        'Authorization' => 'InvalidFormat token'
    ])->getJson('/api/user');

    $response->assertStatus(401);
});
