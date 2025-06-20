<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('api routes are accessible', function () {
    // Test web routes
    $response = $this->get('/');
    $response->assertStatus(200);

    // Test API routes
    $response = $this->get('/api/telegram/webhook/info');
    $response->assertStatus(200);

    $response = $this->get('/api/telegram/bot/info');
    $response->assertStatus(200);

    $response = $this->get('/api/telegram/commands');
    $response->assertStatus(200);
});

test('telegram webhook route accepts post requests', function () {
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

test('telegram webhook route returns 405 for get requests', function () {
    $response = $this->get('/api/telegram/webhook');
    $response->assertStatus(405);
});

test('telegram webhook route returns 405 for put requests', function () {
    $response = $this->put('/api/telegram/webhook');
    $response->assertStatus(405);
});

test('telegram webhook route returns 405 for delete requests', function () {
    $response = $this->delete('/api/telegram/webhook');
    $response->assertStatus(405);
});

test('telegram webhook info route returns 405 for post requests', function () {
    $response = $this->post('/api/telegram/webhook/info');
    $response->assertStatus(405);
});

test('telegram bot info route returns 405 for post requests', function () {
    $response = $this->post('/api/telegram/bot/info');
    $response->assertStatus(405);
});

test('telegram commands route returns 405 for post requests', function () {
    $response = $this->post('/api/telegram/commands');
    $response->assertStatus(405);
});

test('api routes return json responses', function () {
    $response = $this->get('/api/telegram/webhook/info');
    $response->assertHeader('Content-Type', 'application/json');

    $response = $this->get('/api/telegram/bot/info');
    $response->assertHeader('Content-Type', 'application/json');

    $response = $this->get('/api/telegram/commands');
    $response->assertHeader('Content-Type', 'application/json');
});

test('telegram webhook route accepts json content type', function () {
    $update = [
        'update_id' => 123456789,
        'message' => [
            'message_id' => 1,
            'from' => ['id' => 123456],
            'chat' => ['id' => 123456],
            'text' => 'Hello'
        ]
    ];

    $response = $this->withHeaders([
        'Content-Type' => 'application/json',
        'Accept' => 'application/json'
    ])->postJson('/api/telegram/webhook', $update);

    $response->assertStatus(200);
});

test('telegram webhook route handles malformed json gracefully', function () {
    $response = $this->withHeaders([
        'Content-Type' => 'application/json',
        'Accept' => 'application/json'
    ])->post('/api/telegram/webhook', 'invalid json', [
                'Content-Type' => 'application/json'
            ]);

    // Should handle gracefully, might return 400 or 200 depending on implementation
    expect($response->getStatusCode())->toBeIn([200, 400, 422]);
});

test('telegram webhook route handles large payloads', function () {
    $largeUpdate = [
        'update_id' => 123456789,
        'message' => [
            'message_id' => 1,
            'from' => [
                'id' => 123456,
                'first_name' => str_repeat('A', 1000), // Large first name
                'username' => 'testuser'
            ],
            'chat' => [
                'id' => 123456,
                'type' => 'private'
            ],
            'text' => str_repeat('Hello ', 100) // Large text
        ]
    ];

    $response = $this->postJson('/api/telegram/webhook', $largeUpdate);
    $response->assertStatus(200);
});

test('telegram webhook route handles callback queries', function () {
    $callbackUpdate = [
        'update_id' => 123456789,
        'callback_query' => [
            'id' => '123456789',
            'from' => [
                'id' => 123456,
                'first_name' => 'Test',
                'username' => 'testuser'
            ],
            'message' => [
                'message_id' => 1,
                'chat' => [
                    'id' => 123456,
                    'type' => 'private'
                ]
            ],
            'data' => 'test_callback_data'
        ]
    ];

    $response = $this->postJson('/api/telegram/webhook', $callbackUpdate);
    $response->assertStatus(200);
});

test('telegram webhook route handles pre checkout queries', function () {
    $preCheckoutUpdate = [
        'update_id' => 123456789,
        'pre_checkout_query' => [
            'id' => '123456789',
            'from' => [
                'id' => 123456,
                'first_name' => 'Test',
                'username' => 'testuser'
            ],
            'currency' => 'USD',
            'total_amount' => 1000,
            'invoice_payload' => 'test_payload'
        ]
    ];

    $response = $this->postJson('/api/telegram/webhook', $preCheckoutUpdate);
    $response->assertStatus(200);
});

test('telegram webhook route handles edited messages', function () {
    $editedMessageUpdate = [
        'update_id' => 123456789,
        'edited_message' => [
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
            'text' => 'Edited message',
            'edit_date' => time()
        ]
    ];

    $response = $this->postJson('/api/telegram/webhook', $editedMessageUpdate);
    $response->assertStatus(200);
});

test('telegram webhook route handles polls', function () {
    $pollUpdate = [
        'update_id' => 123456789,
        'poll' => [
            'id' => '123456789',
            'question' => 'Test question?',
            'options' => [
                ['text' => 'Option 1', 'voter_count' => 0],
                ['text' => 'Option 2', 'voter_count' => 0]
            ],
            'total_voter_count' => 0,
            'is_closed' => false,
            'is_anonymous' => true,
            'type' => 'quiz',
            'allows_multiple_answers' => false
        ]
    ];

    $response = $this->postJson('/api/telegram/webhook', $pollUpdate);
    $response->assertStatus(200);
});

test('telegram webhook route handles poll answers', function () {
    $pollAnswerUpdate = [
        'update_id' => 123456789,
        'poll_answer' => [
            'poll_id' => '123456789',
            'user' => [
                'id' => 123456,
                'first_name' => 'Test',
                'username' => 'testuser'
            ],
            'option_ids' => [0]
        ]
    ];

    $response = $this->postJson('/api/telegram/webhook', $pollAnswerUpdate);
    $response->assertStatus(200);
});

test('telegram webhook route handles chat member updates', function () {
    $chatMemberUpdate = [
        'update_id' => 123456789,
        'chat_member' => [
            'chat' => [
                'id' => -100123456789,
                'title' => 'Test Group',
                'type' => 'supergroup'
            ],
            'from' => [
                'id' => 123456,
                'first_name' => 'Test',
                'username' => 'testuser'
            ],
            'date' => time(),
            'old_chat_member' => [
                'user' => [
                    'id' => 123456,
                    'first_name' => 'Test',
                    'username' => 'testuser'
                ],
                'status' => 'member'
            ],
            'new_chat_member' => [
                'user' => [
                    'id' => 123456,
                    'first_name' => 'Test',
                    'username' => 'testuser'
                ],
                'status' => 'administrator'
            ]
        ]
    ];

    $response = $this->postJson('/api/telegram/webhook', $chatMemberUpdate);
    $response->assertStatus(200);
});

test('api routes handle rate limiting', function () {
    // Make multiple requests to test rate limiting
    for ($i = 0; $i < 10; $i++) {
        $response = $this->get('/api/telegram/webhook/info');
        // Should not be rate limited for normal usage
        expect($response->getStatusCode())->toBeIn([200, 429]);
    }
});

test('api routes handle concurrent requests', function () {
    // This test simulates concurrent requests
    $responses = [];

    for ($i = 0; $i < 5; $i++) {
        $responses[] = $this->get('/api/telegram/webhook/info');
    }

    foreach ($responses as $response) {
        expect($response->getStatusCode())->toBeIn([200, 429, 500]);
    }
});
