<?php

namespace App\Telegram\Commands;

use App\Application\Services\UserService;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Telegram\Contracts\CommandInterface;
use App\Telegram\Core\BaseCommand;
use Illuminate\Support\Facades\Log;

class DonasiCommand extends BaseCommand implements CommandInterface
{
    protected string|array $commandName = 'donasi';

    public function __construct(
        private UserService $userService,
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(\App\Telegram\Contracts\TelegramContextInterface $context): void
    {
        try {
            $telegramUser = $context->getUser();
            if (! $telegramUser) {
                $context->reply('❌ Unable to identify user');

                return;
            }

            // Find or create user
            $user = $this->userService->findOrCreateUser($telegramUser);
            $firstName = $user->first_name ?? 'all';

            $message = "🙏 **Support Kyla Bot!**\n\n";
            $message .= "Hi {$firstName},\n";
            $message .= "If you enjoy using Kyla Bot, consider supporting us with a donation.\n";
            $message .= "Your support helps us keep the bot running and add new features!\n\n";
            $message .= "**How to donate:**\n";
            $message .= "• Transfer via bank or e-wallet\n";
            $message .= "• Use the buttons below for quick donation options\n\n";
            $message .= 'Thank you for your generosity! 🙏';

            $keyboard = [
                [
                    ['text' => '💳 Donate via Bank', 'url' => 'https://kyla.my.id/donate/bank'],
                    ['text' => '📱 Donate via E-Wallet', 'url' => 'https://kyla.my.id/donate/ewallet'],
                ],
                [
                    ['text' => '🌐 Donate via Arxist', 'url' => 'https://arxist.com/kyla'],
                ],
                [
                    ['text' => '🔙 Back to Menu', 'callback_data' => 'menu-back'],
                ],
            ];

            $context->reply($message, [
                'reply_markup' => [
                    'inline_keyboard' => $keyboard,
                ],
                'parse_mode' => 'Markdown',
            ]);

        } catch (\Exception $e) {
            Log::error('Error in DonasiCommand', [
                'error' => $e->getMessage(),
                'user_id' => $telegramUser['id'] ?? null,
            ]);

            $context->reply('❌ An error occurred while processing donation.');
        }
    }
}
