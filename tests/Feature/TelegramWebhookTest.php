<?php

use App\Jobs\ProcessTelegramUpdateJob;
use App\Telegram\Services\TelegramBotService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->telegramService = Mockery::mock(TelegramBotService::class);
    $this->app->instance(TelegramBotService::class, $this->telegramService);
});

afterEach(function () {
    Mockery::close();
});

test('webhook returns 200 for valid update', function () {
    $update = [
        'update_id' => 123456789,
        'message' => [
            'message_id' => 1,
            'from' => [
                'id' => 123456,
                'first_name' => 'Test',
                'username' => 'testuser',
            ],
            'chat' => [
                'id' => 123456,
                'type' => 'private',
            ],
            'text' => 'Hello',
        ],
    ];

    $this->telegramService->shouldReceive('handleUpdate')
        ->once()
        ->with($update);

    $response = $this->postJson('/api/telegram/webhook', $update);

    $response->assertStatus(200);
    $response->assertSee('OK');
});

test('webhook returns 401 for invalid signature', function () {
    Config::set('telegram.webhook_secret', 'test-secret');

    $response = $this->postJson('/api/telegram/webhook', [], [
        'X-Telegram-Bot-Api-Secret-Token' => 'wrong-secret',
    ]);

    $response->assertStatus(401);
});

test('webhook returns 200 for empty update', function () {
    $response = $this->postJson('/api/telegram/webhook', []);

    $response->assertStatus(200);
    $response->assertSee('OK');
});

test('webhook processes update asynchronously when queue is enabled', function () {
    Queue::fake();
    Config::set('telegram.queue.enabled', true);

    $update = [
        'update_id' => 123456789,
        'message' => [
            'message_id' => 1,
            'from' => ['id' => 123456],
            'chat' => ['id' => 123456],
            'text' => 'Hello',
        ],
    ];

    $response = $this->postJson('/api/telegram/webhook', $update);

    $response->assertStatus(200);
    Queue::assertPushed(ProcessTelegramUpdateJob::class);
});

test('webhook info endpoint returns bot information', function () {
    $webhookInfo = [
        'url' => 'https://example.com/webhook',
        'has_custom_certificate' => false,
        'pending_update_count' => 0,
        'last_error_date' => null,
        'last_error_message' => null,
        'max_connections' => 40,
        'allowed_updates' => ['message', 'callback_query'],
    ];

    $this->telegramService->shouldReceive('getWebhookInfo')
        ->once()
        ->andReturn($webhookInfo);

    $response = $this->getJson('/api/telegram/webhook/info');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => $webhookInfo,
        ]);
});

test('bot info endpoint returns bot details', function () {
    $botInfo = [
        'id' => 123456789,
        'is_bot' => true,
        'first_name' => 'Test Bot',
        'username' => 'test_bot',
        'can_join_groups' => true,
        'can_read_all_group_messages' => false,
        'supports_inline_queries' => false,
    ];

    $this->telegramService->shouldReceive('getMe')
        ->once()
        ->andReturn($botInfo);

    $response = $this->getJson('/api/telegram/bot/info');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => $botInfo,
        ]);
});

test('commands endpoint returns registered commands and callbacks', function () {
    $commands = [
        'start' => 'Start command',
        'help' => 'Help command',
    ];

    $callbacks = [
        'age_callback' => 'Age callback',
        'location_callback' => 'Location callback',
    ];

    $this->telegramService->shouldReceive('getCommands')
        ->once()
        ->andReturn($commands);

    $this->telegramService->shouldReceive('getCallbacks')
        ->once()
        ->andReturn($callbacks);

    $response = $this->getJson('/api/telegram/commands');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'commands' => $commands,
            'callbacks' => $callbacks,
        ]);
});

test('set webhook endpoint requires url parameter', function () {
    $response = $this->postJson('/api/telegram/webhook/set');

    $response->assertStatus(400)
        ->assertJson(['error' => 'URL is required']);
});

test('set webhook endpoint sets webhook successfully', function () {
    $url = 'https://example.com/webhook';
    $result = ['ok' => true, 'result' => true];

    $this->telegramService->shouldReceive('setWebhook')
        ->once()
        ->with($url, [
            'allowed_updates' => [
                'message',
                'callback_query',
                'pre_checkout_query',
                'edited_message',
                'poll',
                'poll_answer',
                'chat_member',
            ],
            'drop_pending_updates' => true,
        ])
        ->andReturn($result);

    $response = $this->postJson('/api/telegram/webhook/set', ['url' => $url]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'result' => $result,
        ]);
});

test('delete webhook endpoint deletes webhook successfully', function () {
    $result = ['ok' => true, 'result' => true];

    $this->telegramService->shouldReceive('deleteWebhook')
        ->once()
        ->with(['drop_pending_updates' => true])
        ->andReturn($result);

    $response = $this->deleteJson('/api/telegram/webhook');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'result' => $result,
        ]);
});
