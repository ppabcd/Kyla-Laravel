<?php

namespace App\Telegram\Commands;

use App\Telegram\Contracts\CommandInterface;
use App\Telegram\Contracts\TelegramContextInterface;
use App\Telegram\Core\BaseCommand;
use Illuminate\Support\Facades\Log;

class TestMiddlewareCommand extends BaseCommand implements CommandInterface
{
    protected string|array $commandName = 'testmiddleware';
    protected string $description = 'Test middleware functionality';

    public function handle(TelegramContextInterface $context): void
    {
        $user = $context->getUserModel();
        $chatId = $context->getChatId();
        $userId = $context->getUserId();

        $message = "🧪 **Test Middleware**\n\n";
        $message .= "✅ Middleware berhasil dijalankan!\n\n";
        $message .= "**Info:**\n";
        $message .= "• Chat ID: `{$chatId}`\n";
        $message .= "• User ID: `{$userId}`\n";
        $message .= "• User Model: " . ($user ? "✅ Ada" : "❌ Tidak ada") . "\n";
        
        if ($user) {
            $message .= "• User Language: `{$user->language_code}`\n";
            $message .= "• User Banned: " . ($user->is_banned ? "❌ Ya" : "✅ Tidak") . "\n";
        }

        $message .= "\n🎉 Semua middleware berfungsi dengan baik!";

        $context->sendMessage($message, ['parse_mode' => 'Markdown']);

        Log::info('Test middleware command executed', [
            'chat_id' => $chatId,
            'user_id' => $userId,
            'user_exists' => $user !== null
        ]);
    }

    public function isEnabled(): bool
    {
        return true;
    }
} 
