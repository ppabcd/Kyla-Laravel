<?php

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Domain\Entities\Pair;
use App\Domain\Entities\User;
use App\Listeners\MessageListener;

echo "\n== Kyla Sticker Forward Smoke Test ==\n";

User::whereIn('telegram_id', [920001, 920002])->delete();
Pair::whereIn('user_id', function ($q) {
    $q->select('id')->from('users')->whereIn('telegram_id', [920001, 920002]);
})
    ->orWhereIn('partner_id', function ($q) {
        $q->select('id')->from('users')->whereIn('telegram_id', [920001, 920002]);
    })
    ->delete();

$u1 = User::create([
    'telegram_id' => 920001,
    'first_name' => 'Alice',
    'language_code' => 'en',
    'gender' => 'female',
    'interest' => 'male',
]);

$u2 = User::create([
    'telegram_id' => 920002,
    'first_name' => 'Bob',
    'language_code' => 'en',
    'gender' => 'male',
    'interest' => 'female',
]);

$pair = Pair::create([
    'user_id' => $u1->id,
    'partner_id' => $u2->id,
    'status' => 'active',
    'active' => 1,
    'started_at' => now(),
]);

/** @var MessageListener $listener */
$listener = app(MessageListener::class);

// Simulate sticker from u1 to u2
$ctx = [
    'message' => [
        'message_id' => 3001,
        'from' => ['id' => $u1->telegram_id, 'is_bot' => false, 'first_name' => $u1->first_name],
        'chat' => ['id' => $u1->telegram_id, 'type' => 'private'],
        'date' => time(),
        'sticker' => [
            'file_id' => 'FAKE_STICKER_FILE_ID',
            'type' => 'regular',
        ],
    ],
];

$res = $listener->handleMediaMessage($u1, $ctx);

var_export($res);
echo "\n";

$ok = is_array($res) && ($res['chat_id'] ?? null) === $u2->telegram_id && (isset($res['sticker']) || (isset($res['copy_from_chat_id']) && isset($res['copy_message_id'])));
echo $ok ? "✅ Sticker forward payload OK\n" : "❌ Sticker forward payload BAD\n";
