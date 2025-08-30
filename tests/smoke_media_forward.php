<?php

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Domain\Entities\Pair;
use App\Domain\Entities\User;
use App\Listeners\MessageListener;

echo "\n== Kyla Media Forward Smoke Test ==\n";

User::whereIn('telegram_id', [910001, 910002])->delete();
Pair::whereIn('user_id', function ($q) {
    $q->select('id')->from('users')->whereIn('telegram_id', [910001, 910002]);
})
    ->orWhereIn('partner_id', function ($q) {
        $q->select('id')->from('users')->whereIn('telegram_id', [910001, 910002]);
    })
    ->delete();

$u1 = User::create([
    'telegram_id' => 910001,
    'first_name' => 'Alice',
    'language_code' => 'en',
    'gender' => 'female',
    'interest' => 'male',
    'last_activity_at' => now(),
]);

$u2 = User::create([
    'telegram_id' => 910002,
    'first_name' => 'Bob',
    'language_code' => 'en',
    'gender' => 'male',
    'interest' => 'female',
    'last_activity_at' => now(),
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

// Simulate photo from u1 to u2
$ctx = [
    'message' => [
        'message_id' => 2001,
        'from' => ['id' => $u1->telegram_id, 'is_bot' => false, 'first_name' => $u1->first_name],
        'chat' => ['id' => $u1->telegram_id, 'type' => 'private'],
        'date' => time(),
        'photo' => [
            ['file_id' => 'FAKE_FILE_ID_SMALL', 'width' => 100, 'height' => 100],
            ['file_id' => 'FAKE_FILE_ID_LARGE', 'width' => 1280, 'height' => 720],
        ],
        'caption' => 'Test photo',
    ],
];

$res = $listener->handleMediaMessage($u1, $ctx);

var_export($res);
echo "\n";

$ok = is_array($res) && ($res['chat_id'] ?? null) === $u2->telegram_id && isset($res['photo']);
echo $ok ? "✅ Media forward payload OK\n" : "❌ Media forward payload BAD\n";
