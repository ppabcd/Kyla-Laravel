<?php

require __DIR__.'/vendor/autoload.php';

use App\Telegram\Services\TelegramBotService;
use Illuminate\Foundation\Application;

// Initialize Laravel application
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

class MessageListenerTest
{
    private TelegramBotService $botService;

    private array $testResults = [];

    public function __construct()
    {
        $this->botService = app(TelegramBotService::class);
    }

    public function runTests(): void
    {
        echo "🧪 Testing Telegram Message Listener Integration\n";
        echo str_repeat('=', 60)."\n";

        $this->testPrivateTextMessage();
        $this->testPrivatePhotoMessage();
        $this->testGroupTextMessage();
        $this->testGroupMediaMessage();
        $this->testLocationMessage();
        $this->testContactMessage();
        $this->testBotMentionInGroup();

        $this->printResults();
    }

    private function testPrivateTextMessage(): void
    {
        echo "Testing private text message handling...\n";

        $update = [
            'update_id' => 123456,
            'message' => [
                'message_id' => 1,
                'from' => [
                    'id' => 1234567890,
                    'is_bot' => false,
                    'first_name' => 'John',
                    'username' => 'john_doe',
                    'language_code' => 'en',
                ],
                'chat' => [
                    'id' => 1234567890,
                    'first_name' => 'John',
                    'username' => 'john_doe',
                    'type' => 'private',
                ],
                'date' => time(),
                'text' => 'Hello, this is a test message!',
            ],
        ];

        try {
            $this->botService->handleUpdate($update);
            $this->testResults['private_text'] = '✅ PASS - Private text message handled';
            echo "✅ PASS - Private text message handled\n";
        } catch (Exception $e) {
            $this->testResults['private_text'] = '❌ FAIL - '.$e->getMessage();
            echo '❌ FAIL - '.$e->getMessage()."\n";
        }
    }

    private function testPrivatePhotoMessage(): void
    {
        echo "Testing private photo message handling...\n";

        $update = [
            'update_id' => 123457,
            'message' => [
                'message_id' => 2,
                'from' => [
                    'id' => 1234567890,
                    'is_bot' => false,
                    'first_name' => 'John',
                    'username' => 'john_doe',
                ],
                'chat' => [
                    'id' => 1234567890,
                    'first_name' => 'John',
                    'username' => 'john_doe',
                    'type' => 'private',
                ],
                'date' => time(),
                'photo' => [
                    [
                        'file_id' => 'test_photo_file_id',
                        'file_unique_id' => 'test_unique_id',
                        'width' => 1280,
                        'height' => 720,
                        'file_size' => 65535,
                    ],
                ],
                'caption' => 'This is a test photo',
            ],
        ];

        try {
            $this->botService->handleUpdate($update);
            $this->testResults['private_photo'] = '✅ PASS - Private photo message handled';
            echo "✅ PASS - Private photo message handled\n";
        } catch (Exception $e) {
            $this->testResults['private_photo'] = '❌ FAIL - '.$e->getMessage();
            echo '❌ FAIL - '.$e->getMessage()."\n";
        }
    }

    private function testGroupTextMessage(): void
    {
        echo "Testing group text message handling...\n";

        $update = [
            'update_id' => 123458,
            'message' => [
                'message_id' => 3,
                'from' => [
                    'id' => 1234567890,
                    'is_bot' => false,
                    'first_name' => 'John',
                    'username' => 'john_doe',
                ],
                'chat' => [
                    'id' => -1001234567890,
                    'title' => 'Test Group',
                    'type' => 'supergroup',
                ],
                'date' => time(),
                'text' => 'Hello everyone in the group!',
            ],
        ];

        try {
            $this->botService->handleUpdate($update);
            $this->testResults['group_text'] = '✅ PASS - Group text message handled';
            echo "✅ PASS - Group text message handled\n";
        } catch (Exception $e) {
            $this->testResults['group_text'] = '❌ FAIL - '.$e->getMessage();
            echo '❌ FAIL - '.$e->getMessage()."\n";
        }
    }

    private function testGroupMediaMessage(): void
    {
        echo "Testing group media message handling...\n";

        $update = [
            'update_id' => 123459,
            'message' => [
                'message_id' => 4,
                'from' => [
                    'id' => 1234567890,
                    'is_bot' => false,
                    'first_name' => 'John',
                    'username' => 'john_doe',
                ],
                'chat' => [
                    'id' => -1001234567890,
                    'title' => 'Test Group',
                    'type' => 'supergroup',
                ],
                'date' => time(),
                'video' => [
                    'file_id' => 'test_video_file_id',
                    'file_unique_id' => 'test_unique_video_id',
                    'width' => 1920,
                    'height' => 1080,
                    'duration' => 30,
                    'file_size' => 1048576,
                ],
                'caption' => 'Check out this video!',
            ],
        ];

        try {
            $this->botService->handleUpdate($update);
            $this->testResults['group_media'] = '✅ PASS - Group media message handled';
            echo "✅ PASS - Group media message handled\n";
        } catch (Exception $e) {
            $this->testResults['group_media'] = '❌ FAIL - '.$e->getMessage();
            echo '❌ FAIL - '.$e->getMessage()."\n";
        }
    }

    private function testLocationMessage(): void
    {
        echo "Testing location message handling...\n";

        $update = [
            'update_id' => 123460,
            'message' => [
                'message_id' => 5,
                'from' => [
                    'id' => 1234567890,
                    'is_bot' => false,
                    'first_name' => 'John',
                    'username' => 'john_doe',
                ],
                'chat' => [
                    'id' => 1234567890,
                    'first_name' => 'John',
                    'username' => 'john_doe',
                    'type' => 'private',
                ],
                'date' => time(),
                'location' => [
                    'longitude' => 106.8456,
                    'latitude' => -6.2088,
                ],
            ],
        ];

        try {
            $this->botService->handleUpdate($update);
            $this->testResults['location'] = '✅ PASS - Location message handled';
            echo "✅ PASS - Location message handled\n";
        } catch (Exception $e) {
            $this->testResults['location'] = '❌ FAIL - '.$e->getMessage();
            echo '❌ FAIL - '.$e->getMessage()."\n";
        }
    }

    private function testContactMessage(): void
    {
        echo "Testing contact message handling...\n";

        $update = [
            'update_id' => 123461,
            'message' => [
                'message_id' => 6,
                'from' => [
                    'id' => 1234567890,
                    'is_bot' => false,
                    'first_name' => 'John',
                    'username' => 'john_doe',
                ],
                'chat' => [
                    'id' => 1234567890,
                    'first_name' => 'John',
                    'username' => 'john_doe',
                    'type' => 'private',
                ],
                'date' => time(),
                'contact' => [
                    'phone_number' => '+6281234567890',
                    'first_name' => 'Contact',
                    'last_name' => 'Test',
                    'user_id' => 9876543210,
                ],
            ],
        ];

        try {
            $this->botService->handleUpdate($update);
            $this->testResults['contact'] = '✅ PASS - Contact message handled';
            echo "✅ PASS - Contact message handled\n";
        } catch (Exception $e) {
            $this->testResults['contact'] = '❌ FAIL - '.$e->getMessage();
            echo '❌ FAIL - '.$e->getMessage()."\n";
        }
    }

    private function testBotMentionInGroup(): void
    {
        echo "Testing bot mention in group...\n";

        $botUsername = config('telegram.bot_username', 'testbot');

        $update = [
            'update_id' => 123462,
            'message' => [
                'message_id' => 7,
                'from' => [
                    'id' => 1234567890,
                    'is_bot' => false,
                    'first_name' => 'John',
                    'username' => 'john_doe',
                ],
                'chat' => [
                    'id' => -1001234567890,
                    'title' => 'Test Group',
                    'type' => 'supergroup',
                ],
                'date' => time(),
                'text' => "Hey @{$botUsername}, can you help us?",
            ],
        ];

        try {
            $this->botService->handleUpdate($update);
            $this->testResults['bot_mention'] = '✅ PASS - Bot mention in group handled';
            echo "✅ PASS - Bot mention in group handled\n";
        } catch (Exception $e) {
            $this->testResults['bot_mention'] = '❌ FAIL - '.$e->getMessage();
            echo '❌ FAIL - '.$e->getMessage()."\n";
        }
    }

    private function printResults(): void
    {
        echo "\n".str_repeat('=', 60)."\n";
        echo "📊 TEST RESULTS SUMMARY\n";
        echo str_repeat('=', 60)."\n";

        $passed = 0;
        $total = count($this->testResults);

        foreach ($this->testResults as $test => $result) {
            echo $result."\n";
            if (str_contains($result, '✅ PASS')) {
                $passed++;
            }
        }

        echo str_repeat('-', 60)."\n";
        echo "Total: {$total} | Passed: {$passed} | Failed: ".($total - $passed)."\n";
        echo 'Success Rate: '.round(($passed / $total) * 100, 2)."%\n";
        echo str_repeat('=', 60)."\n";

        if ($passed === $total) {
            echo "🎉 ALL TESTS PASSED! Message Listener is working correctly.\n";
        } else {
            echo "⚠️  Some tests failed. Please check the implementation.\n";
        }
    }
}

// Run the tests
try {
    $tester = new MessageListenerTest;
    $tester->runTests();
} catch (Exception $e) {
    echo '❌ CRITICAL ERROR: '.$e->getMessage()."\n";
    echo "Stack trace:\n".$e->getTraceAsString()."\n";
}
