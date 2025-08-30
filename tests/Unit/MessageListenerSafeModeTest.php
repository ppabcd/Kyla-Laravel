<?php

use App\Application\Services\MatchingService;
use App\Application\Services\UserService;
use App\Domain\Entities\User;
use App\Infrastructure\Repositories\ConversationLogRepository;
use App\Listeners\MessageListener;
use App\Telegram\Services\KeyboardService;
use Illuminate\Support\Facades\Cache;

it('blocks media with safe mode and shows enable button', function () {
    $matching = Mockery::mock(MatchingService::class);
    $userService = Mockery::mock(UserService::class);
    $convRepo = Mockery::mock(ConversationLogRepository::class);
    $keyboard = Mockery::mock(KeyboardService::class);

    $listener = new MessageListener($matching, $userService, $convRepo, $keyboard);

    $sender = new User(['id' => 1, 'telegram_id' => 111]);
    $partner = new User(['id' => 2, 'telegram_id' => 222, 'safe_mode' => true]);

    $matching->shouldReceive('getConversationPartner')->once()->withArgs(function ($u) use ($sender) {
        return $u->id === $sender->id;
    })->andReturn($partner);

    $keyboard->shouldReceive('getSearchKeyboard')->andReturn([]);
    $keyboard->shouldReceive('getSafeModeKeyboard')->andReturn([
        'inline_keyboard' => [
            [['text' => '📸 Enable Media', 'callback_data' => 'enable_media']],
        ],
    ]);

    $context = [
        'message' => [
            'message_id' => 10,
            'chat' => ['id' => 111, 'type' => 'private'],
            'sticker' => ['file_id' => 'stkr_123'],
        ],
    ];

    Cache::forget('enable-media:'.$partner->id);

    $res = $listener->handleMediaMessage($sender, $context);

    expect($res['chat_id'])->toBe(111)
        ->and($res['text'])->toBeString()
        ->and($res['reply_markup'])->toBeArray();
});

it('forwards media when partner confirmed temporary enable', function () {
    $matching = Mockery::mock(MatchingService::class);
    $userService = Mockery::mock(UserService::class);
    $convRepo = Mockery::mock(ConversationLogRepository::class);
    $keyboard = Mockery::mock(KeyboardService::class);

    $listener = new MessageListener($matching, $userService, $convRepo, $keyboard);

    $sender = new User(['id' => 1, 'telegram_id' => 111]);
    $partner = new User(['id' => 2, 'telegram_id' => 222, 'safe_mode' => true]);

    $matching->shouldReceive('getConversationPartner')->once()->andReturn($partner);

    $userService->shouldReceive('updateLastActivity')->andReturnTrue();

    Cache::put('enable-media:'.$partner->id, true, now()->addHour());

    $context = [
        'message' => [
            'message_id' => 10,
            'chat' => ['id' => 111, 'type' => 'private'],
            'sticker' => ['file_id' => 'stkr_123'],
        ],
    ];

    $res = $listener->handleMediaMessage($sender, $context);

    expect($res['chat_id'])->toBe(222)
        ->and($res['sticker'])->toBe('stkr_123');
});
