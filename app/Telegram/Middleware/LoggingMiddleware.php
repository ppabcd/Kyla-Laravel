<?php

namespace App\Telegram\Middleware;

use App\Telegram\Contracts\TelegramContextInterface;
use Illuminate\Support\Facades\Log;

class LoggingMiddleware implements MiddlewareInterface
{
    public function handle(TelegramContextInterface $context, callable $next): void
    {
        $update = $context->getUpdate();
        $chatId = $update['message']['chat']['id'] ?? $update['callback_query']['from']['id'] ?? null;
        $username = $update['message']['from']['username'] ?? $update['callback_query']['from']['username'] ?? 'Unknown';
        
        Log::info('Telegram update received', [
            'chat_id' => $chatId,
            'username' => $username,
            'update_type' => $this->getUpdateType($update),
            'timestamp' => now()
        ]);

        // Continue to next middleware/handler
        $next($context);
    }

    private function getUpdateType(array $update): string
    {
        if (isset($update['message']['text'])) {
            return 'text_message';
        }
        
        if (isset($update['callback_query'])) {
            return 'callback_query';
        }
        
        if (isset($update['message']['photo'])) {
            return 'photo_message';
        }
        
        return 'other';
    }
} 
