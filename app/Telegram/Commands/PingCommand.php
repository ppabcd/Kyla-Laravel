<?php

namespace App\Telegram\Commands;

use App\Telegram\Contracts\CommandInterface;
use App\Telegram\Core\BaseCommand;
use Illuminate\Support\Facades\Log;

class PingCommand extends BaseCommand implements CommandInterface
{
    protected string|array $commandName = 'ping';

    public function handle(\App\Telegram\Contracts\TelegramContextInterface $context): void
    {
        try {
            $startTime = microtime(true);

            // Send initial message
            $response = $context->reply('🏓?');

            // Calculate response time
            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime) * 1000, 2); // Convert to milliseconds

            // Edit the message with response time
            $message = "Pong! 🏓\n\nResponse time: {$responseTime}ms";

            // Note: In a real implementation, you would need to edit the message
            // For now, we'll just send a new message
            $context->reply($message);

            Log::info('Ping command executed', [
                'response_time' => $responseTime,
                'user_id' => $context->getUser()['id'] ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error('Error in PingCommand', [
                'error' => $e->getMessage(),
                'user_id' => $context->getUser()['id'] ?? null,
            ]);

            $context->reply('❌ An error occurred while processing ping.');
        }
    }
}
