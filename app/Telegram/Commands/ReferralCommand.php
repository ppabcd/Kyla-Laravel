<?php

namespace App\Telegram\Commands;

use App\Telegram\Core\BaseCommand;
use App\Telegram\Contracts\CommandInterface;
use App\Telegram\Core\TelegramContext;
use App\Application\Services\UserService;
use App\Domain\Repositories\UserRepositoryInterface;

class ReferralCommand extends BaseCommand implements CommandInterface
{
    protected string|array $commandName = 'referral';

    public function __construct(
        private UserService $userService,
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(\App\Telegram\Contracts\TelegramContextInterface $context): void
    {
        $telegramUser = $context->getFrom();
        if (!$telegramUser) {
            $context->reply('❌ Unable to identify user');
            return;
        }
        $user = $this->userService->findOrCreateUser($telegramUser);
        $totalReferral = $user->total_referral ?? 0;
        $referralToken = $user->referral_token ?? $user->id;
        $referralLink = $this->getStartLink($context, $referralToken);
        $message = __('referral.total_referral', [
            'totalReferral' => $totalReferral,
            'referralLink' => $referralLink
        ]);
        $context->reply($message);
    }

    private function getBotUsername(TelegramContext $context): string
    {
        return $context->getMe()['username'] ?? 'kyla_bot';
    }

    private function getBotLink(TelegramContext $context): string
    {
        return 'https://t.me/' . $this->getBotUsername($context);
    }

    private function getStartLink(TelegramContext $context, string $token): string
    {
        return $this->getBotLink($context) . '?start=' . $token;
    }
} 
