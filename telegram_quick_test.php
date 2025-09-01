<?php

/**
 * Quick Test Script for Main Telegram Commands and Callbacks
 * For rapid testing of core bot functionality
 */
$baseUrl = 'http://127.0.0.1:8000/api/telegram/webhook';

echo "🚀 Quick Telegram Bot Test\n";
echo "========================\n\n";

// Start server
echo "Starting server...\n";
exec('php artisan serve --host=127.0.0.1 --port=8000 > /dev/null 2>&1 &');
sleep(2);

function quickTest($url, $data, $testName)
{
    echo "Testing {$testName}... ";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $response === 'OK') {
        echo "✅\n";

        return true;
    } else {
        echo "❌ (HTTP: {$httpCode})\n";

        return false;
    }
}

// Quick command tests
echo "🔧 TESTING MAIN COMMANDS\n";
$commands = [
    '/start' => 'Start conversation',
    '/stop' => 'Stop conversation',
    '/next' => 'Next partner',
    '/help' => 'Show help',
    '/balance' => 'Show balance',
    '/profile' => 'Show profile',
    '/settings' => 'Show settings',
    '/language' => 'Language selection',
];

foreach ($commands as $cmd => $desc) {
    $data = [
        'update_id' => rand(100000, 999999),
        'message' => [
            'message_id' => rand(600, 999),
            'from' => ['username' => 'testuser', 'id' => rand(100000, 999999), 'is_bot' => false, 'first_name' => 'Test User', 'language_code' => 'en'],
            'date' => time(),
            'chat' => ['username' => 'testuser', 'first_name' => 'Test User', 'type' => 'private', 'id' => rand(100000, 999999)],
            'text' => $cmd,
            'entities' => [['type' => 'bot_command', 'offset' => 0, 'length' => strlen($cmd)]],
        ],
    ];
    quickTest($baseUrl, $data, $cmd);
}

// Quick callback tests
echo "\n📞 TESTING MAIN CALLBACKS\n";
$callbacks = [
    'lang-id' => 'Indonesian language',
    'lang-en' => 'English language',
    'gender-male' => 'Male gender',
    'gender-female' => 'Female gender',
    'profile-back' => 'Profile back button',
    'help-commands' => 'Help commands',
];

foreach ($callbacks as $callback => $desc) {
    $data = [
        'update_id' => rand(100000, 999999),
        'callback_query' => [
            'id' => 'callback'.rand(100, 999),
            'from' => ['username' => 'testuser', 'id' => rand(100000, 999999), 'is_bot' => false, 'first_name' => 'Test User', 'language_code' => 'en'],
            'message' => ['message_id' => rand(600, 999), 'date' => time(), 'chat' => ['id' => rand(100000, 999999), 'first_name' => 'Test User', 'username' => 'testuser', 'type' => 'private']],
            'data' => $callback,
        ],
    ];
    quickTest($baseUrl, $data, $callback);
}

// Stop server
echo "\nStopping server...\n";
exec('pkill -f "php artisan serve"');

echo "\n✨ Quick test completed!\n";
