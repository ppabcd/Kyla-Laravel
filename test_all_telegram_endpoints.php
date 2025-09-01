<?php

/**
 * Comprehensive Test Suite for All Telegram Commands and Callbacks
 * Tests all available commands and callbacks via webhook endpoint
 */
$baseUrl = 'http://127.0.0.1:8000/api/telegram/webhook';
$testUserId = 123456789;
$testChatId = 123456789;

echo "🚀 Starting Telegram Bot Comprehensive Test Suite\n";
echo '='.str_repeat('=', 60)."\n\n";

// Start server
echo "Starting test server...\n";
exec('php artisan serve --host=127.0.0.1 --port=8000 > /dev/null 2>&1 &');
sleep(3);

$testResults = [];

/**
 * Test helper function
 */
function testWebhook($url, $data, $testName)
{
    global $testResults;

    echo "Testing: {$testName}... ";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $response === 'OK') {
        echo "✅ PASS\n";
        $testResults[$testName] = 'PASS';

        return true;
    } else {
        echo "❌ FAIL (HTTP: {$httpCode}, Response: {$response})\n";
        $testResults[$testName] = 'FAIL';

        return false;
    }
}

/**
 * Generate test message for commands
 */
function generateCommandMessage($command, $userId = null, $chatId = null)
{
    global $testUserId, $testChatId;

    $userId = $userId ?? $testUserId + rand(1, 1000);
    $chatId = $chatId ?? $testChatId + rand(1, 1000);

    return [
        'update_id' => rand(100000, 999999),
        'message' => [
            'message_id' => rand(600, 999),
            'from' => [
                'username' => 'testuser'.rand(1, 1000),
                'id' => $userId,
                'is_bot' => false,
                'first_name' => 'Test User '.rand(1, 1000),
                'language_code' => 'en',
            ],
            'date' => time(),
            'chat' => [
                'username' => 'testuser'.rand(1, 1000),
                'first_name' => 'Test User '.rand(1, 1000),
                'type' => 'private',
                'id' => $chatId,
            ],
            'text' => "/{$command}",
            'entities' => [
                [
                    'type' => 'bot_command',
                    'offset' => 0,
                    'length' => strlen($command) + 1,
                ],
            ],
        ],
    ];
}

/**
 * Generate test callback query
 */
function generateCallbackQuery($callbackData, $userId = null, $chatId = null)
{
    global $testUserId, $testChatId;

    $userId = $userId ?? $testUserId + rand(1, 1000);
    $chatId = $chatId ?? $testChatId + rand(1, 1000);

    return [
        'update_id' => rand(100000, 999999),
        'callback_query' => [
            'id' => 'callback'.rand(100, 999),
            'from' => [
                'username' => 'testuser'.rand(1, 1000),
                'id' => $userId,
                'is_bot' => false,
                'first_name' => 'Test User '.rand(1, 1000),
                'language_code' => 'en',
            ],
            'message' => [
                'message_id' => rand(600, 999),
                'date' => time(),
                'chat' => [
                    'id' => $chatId,
                    'first_name' => 'Test User '.rand(1, 1000),
                    'username' => 'testuser'.rand(1, 1000),
                    'type' => 'private',
                ],
            ],
            'data' => $callbackData,
        ],
    ];
}

// ====================
// TEST COMMANDS
// ====================
echo "🔧 TESTING COMMANDS\n";
echo '-'.str_repeat('-', 30)."\n";

$commands = [
    'start' => 'Start conversation command',
    'search' => 'Search for partner (alias for start)',
    'stop' => 'Stop current conversation',
    'next' => 'Find next conversation partner',
    'help' => 'Show help information',
    'balance' => 'Show user balance',
    'profile' => 'Show user profile',
    'settings' => 'Show user settings',
    'language' => 'Change language preferences',
    'interest' => 'Set user interests',
    'mode' => 'Change bot mode',
    'privacy' => 'Show privacy policy',
    'rules' => 'Show community rules',
    'ping' => 'Ping command for testing',
    'test' => 'Test command',
    'donasi' => 'Donation command',
    'feedback' => 'Send feedback',
    'referral' => 'Referral system',
    'transfer' => 'Transfer balance',
    'pending' => 'Show pending requests',
    'invalidate_session' => 'Invalidate user session',
    'test_middleware' => 'Test middleware functionality',
];

foreach ($commands as $command => $description) {
    $data = generateCommandMessage($command);
    testWebhook($baseUrl, $data, "Command: /{$command} - {$description}");
    usleep(500000); // 0.5 second delay
}

// ====================
// TEST CALLBACKS
// ====================
echo "\n📞 TESTING CALLBACKS\n";
echo '-'.str_repeat('-', 30)."\n";

$callbacks = [
    // Language callbacks
    'lang-id' => 'Select Indonesian language',
    'lang-en' => 'Select English language',
    'lang-my' => 'Select Malaysian language',
    'lang-in' => 'Select Hindi language',
    'lang-contribute' => 'Contribute translation',

    // Gender callbacks
    'gender-male' => 'Select male gender',
    'gender-female' => 'Select female gender',

    // Interest callbacks (examples)
    'interest-male' => 'Interest in males',
    'interest-female' => 'Interest in females',

    // Age callback
    'age' => 'Age selection',

    // Location callback
    'location' => 'Location selection',

    // Settings callbacks
    'settings-notifications' => 'Notification settings',
    'settings-privacy' => 'Privacy settings',
    'settings-safe-mode' => 'Safe mode settings',
    'settings-language' => 'Language settings',
    'settings-data-privacy' => 'Data privacy settings',
    'settings-delete-account' => 'Delete account',

    // Other callbacks
    'pending' => 'Pending requests',
    'privacy' => 'Privacy policy',
    'banned' => 'Banned user callback',
    'captcha' => 'Captcha callback',
    'conversation' => 'Conversation callback',
    'donation' => 'Donation callback',
    'crypto-donation' => 'Crypto donation callback',
    'cancel-keyboard' => 'Cancel keyboard',
    'reject-text' => 'Reject text message',
    'reject-media' => 'Reject media message',
    'retry-subscribe' => 'Retry subscription check',
    'topup' => 'Top up balance',
    'enable_media' => 'Enable media messages',
    'banned-text' => 'Banned text callback',
    'banned-media' => 'Banned media callback',
    'report' => 'Report callback',
    'rating' => 'Rating callback',
    'toggle_safe_mode' => 'Toggle safe mode',
    'mode' => 'Mode callback',
];

foreach ($callbacks as $callbackData => $description) {
    $data = generateCallbackQuery($callbackData);
    testWebhook($baseUrl, $data, "Callback: {$callbackData} - {$description}");
    usleep(500000); // 0.5 second delay
}

// ====================
// PROFILE SPECIFIC CALLBACKS
// ====================
echo "\n👤 TESTING PROFILE CALLBACKS\n";
echo '-'.str_repeat('-', 30)."\n";

$profileCallbacks = [
    'profile-back' => 'Profile back button',
    'profile-edit' => 'Edit profile',
    'profile-picture' => 'Change profile picture',
    'profile-bio' => 'Edit bio',
    'profile-age' => 'Edit age',
    'profile-location' => 'Edit location',
    'profile-interests' => 'Edit interests',
    'profile-gender' => 'Edit gender',
];

foreach ($profileCallbacks as $callbackData => $description) {
    $data = generateCallbackQuery($callbackData);
    testWebhook($baseUrl, $data, "Profile: {$callbackData} - {$description}");
    usleep(500000); // 0.5 second delay
}

// ====================
// HELP SPECIFIC CALLBACKS
// ====================
echo "\n❓ TESTING HELP CALLBACKS\n";
echo '-'.str_repeat('-', 30)."\n";

$helpCallbacks = [
    'help-commands' => 'Help commands',
    'help-features' => 'Help features',
    'help-safety' => 'Help safety',
    'help-privacy' => 'Help privacy',
    'help-contact' => 'Help contact',
    'help-back' => 'Help back button',
];

foreach ($helpCallbacks as $callbackData => $description) {
    $data = generateCallbackQuery($callbackData);
    testWebhook($baseUrl, $data, "Help: {$callbackData} - {$description}");
    usleep(500000); // 0.5 second delay
}

// ====================
// ADMIN COMMANDS (if available)
// ====================
echo "\n👑 TESTING ADMIN COMMANDS\n";
echo '-'.str_repeat('-', 30)."\n";

$adminCommands = [
    'ban' => 'Ban user command',
    'unban' => 'Unban user command',
    'stats' => 'Show statistics',
    'count' => 'Show user count',
    'announcement' => 'Send announcement',
    'debug' => 'Debug command',
    'find' => 'Find user command',
    'message' => 'Send message to user',
    'partner' => 'Show partner info',
    'claim' => 'Claim command',
    'banned' => 'Show banned users',
    'ban_history' => 'Show ban history',
    'reset_account' => 'Reset user account',
    'commands' => 'Show all commands',
    'encrypt_decrypt' => 'Encryption test',
];

foreach ($adminCommands as $command => $description) {
    $data = generateCommandMessage($command);
    testWebhook($baseUrl, $data, "Admin: /{$command} - {$description}");
    usleep(500000); // 0.5 second delay
}

// Stop server
echo "\nStopping test server...\n";
exec('pkill -f "php artisan serve"');

// ====================
// TEST RESULTS SUMMARY
// ====================
echo "\n".str_repeat('=', 60)."\n";
echo "📊 TEST RESULTS SUMMARY\n";
echo str_repeat('=', 60)."\n";

$totalTests = count($testResults);
$passedTests = count(array_filter($testResults, fn ($result) => $result === 'PASS'));
$failedTests = $totalTests - $passedTests;

echo "Total Tests: {$totalTests}\n";
echo "✅ Passed: {$passedTests}\n";
echo "❌ Failed: {$failedTests}\n";
echo 'Success Rate: '.round(($passedTests / $totalTests) * 100, 2)."%\n\n";

if ($failedTests > 0) {
    echo "❌ FAILED TESTS:\n";
    echo '-'.str_repeat('-', 30)."\n";
    foreach ($testResults as $testName => $result) {
        if ($result === 'FAIL') {
            echo "• {$testName}\n";
        }
    }
}

echo "\n🎉 Test suite completed!\n";
