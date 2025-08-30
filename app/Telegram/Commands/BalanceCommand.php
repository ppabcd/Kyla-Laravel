<?php

namespace App\Telegram\Commands;

use App\Application\Services\UserService;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Telegram\Contracts\CommandInterface;
use App\Telegram\Contracts\TelegramContextInterface;
use App\Telegram\Core\BaseCommand;
use Illuminate\Support\Facades\Log;

class BalanceCommand extends BaseCommand implements CommandInterface
{
    protected string|array $commandName = 'balance';

    public function __construct(
        private UserService $userService,
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(TelegramContextInterface $context): void
    {
        try {
            $telegramUser = $context->getUser();
            if (! $telegramUser) {
                $context->reply('❌ Unable to identify user');

                return;
            }

            // Find or create user
            $user = $this->userService->findOrCreateUser($telegramUser);

            $this->showBalance($context, $user);

        } catch (\Exception $e) {
            Log::error('Error in BalanceCommand', [
                'error' => $e->getMessage(),
                'user_id' => $telegramUser['id'] ?? null,
            ]);

            $context->reply('❌ An error occurred while loading balance.');
        }
    }

    private function showBalance(TelegramContextInterface $context, $user): void
    {
        $balance = $user->balance ?? 0;

        $message = "💰 **Your Balance**\n\n";
        $message .= "Current balance: **{$balance} coins**\n\n";

        if ($balance > 0) {
            $message .= "You can use your coins to:\n";
            $message .= "• Get priority matching\n";
            $message .= "• Send gifts to matches\n";
            $message .= "• Access premium features\n";
        } else {
            $message .= "You don't have any coins yet.\n";
            $message .= "Get coins by:\n";
            $message .= "• Making purchases\n";
            $message .= "• Daily rewards\n";
            $message .= "• Referral bonuses\n";
        }

        $keyboard = [
            [
                ['text' => '💳 Buy Coins', 'callback_data' => 'balance-buy'],
                ['text' => '🎁 Send Gift', 'callback_data' => 'balance-gift'],
            ],
            [
                ['text' => '🎯 Priority Match', 'callback_data' => 'balance-priority'],
                ['text' => '📊 Transaction History', 'callback_data' => 'balance-history'],
            ],
            [
                ['text' => '🎁 Daily Reward', 'callback_data' => 'balance-daily'],
                ['text' => '👥 Refer Friends', 'callback_data' => 'balance-referral'],
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
    }
}
