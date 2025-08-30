<?php

// Simple smoke test to verify both users in a pair can send messages

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Domain\Entities\Pair;
use App\Domain\Entities\User;
use App\Listeners\MessageListener;

echo "\n== Kyla Pair Chat Smoke Test ==\n";

// Clean previous test users if present
User::whereIn('telegram_id', [900001, 900002])->delete();
Pair::whereIn('user_id', function ($q) {
    $q->select('id')->from('users')->whereIn('telegram_id', [900001, 900002]);
})
    ->orWhereIn('partner_id', function ($q) {
        $q->select('id')->from('users')->whereIn('telegram_id', [900001, 900002]);
    })
    ->delete();

// Create two users
$u1 = User::create([
    'telegram_id' => 900001,
    'first_name' => 'Alice',
    'username' => 'alice_test',
    'language_code' => 'en',
    'gender' => 'female',
    'interest' => 'male',
    'last_activity_at' => now(),
]);

$u2 = User::create([
    'telegram_id' => 900002,
    'first_name' => 'Bob',
    'username' => 'bob_test',
    'language_code' => 'en',
    'gender' => 'male',
    'interest' => 'female',
    'last_activity_at' => now(),
]);

// Create active pair
$pair = Pair::create([
    'user_id' => $u1->id,
    'partner_id' => $u2->id,
    'status' => 'active',
    'active' => 1,
    'started_at' => now(),
]);

echo "Created users U1={$u1->id}, U2={$u2->id}, Pair={$pair->id}\n";

// Build minimal Telegram-like context arrays
$ctx1 = [
    'message' => [
        'message_id' => 1001,
        'from' => ['id' => $u1->telegram_id, 'is_bot' => false, 'first_name' => $u1->first_name, 'language_code' => 'en'],
        'chat' => ['id' => $u1->telegram_id, 'type' => 'private'],
        'date' => time(),
        'text' => 'Hello from Alice',
    ],
];

$ctx2 = [
    'message' => [
        'message_id' => 1002,
        'from' => ['id' => $u2->telegram_id, 'is_bot' => false, 'first_name' => $u2->first_name, 'language_code' => 'en'],
        'chat' => ['id' => $u2->telegram_id, 'type' => 'private'],
        'date' => time(),
        'text' => 'Hi Alice, this is Bob',
    ],
];

/** @var MessageListener $listener */
$listener = app(MessageListener::class);

$res1 = $listener->handleTextMessage($u1, $ctx1);
$res2 = $listener->handleTextMessage($u2, $ctx2);

echo "\nResult from Alice -> Bob:\n";
var_export($res1);
echo "\n\nResult from Bob -> Alice:\n";
var_export($res2);
echo "\n\n";

// Quick assertions
$ok1 = is_array($res1) && ($res1['chat_id'] ?? null) === $u2->telegram_id && ($res1['text'] ?? '') === 'Hello from Alice';
$ok2 = is_array($res2) && ($res2['chat_id'] ?? null) === $u1->telegram_id && ($res2['text'] ?? '') === 'Hi Alice, this is Bob';

if ($ok1 && $ok2) {
    echo "✅ Smoke test passed: both users can chat.\n";
    exit(0);
}

echo "❌ Smoke test failed.\n";
exit(1);
