<?php

namespace App\Telegram\Commands\Admin;

use App\Telegram\Core\BaseCommand;
use App\Telegram\Contracts\TelegramContextInterface;
use App\Domain\Repositories\UserRepositoryInterface;

class ResetAccountCommand extends BaseCommand
{
    protected string $name = 'resetaccount';
    protected string $description = 'Reset akun user';
    protected bool $adminOnly = true;

    public function __construct(private UserRepositoryInterface $userRepository) {}

    public function handle(TelegramContextInterface $context): void
    {
        $chatId = $context->getMessage()->chat->id;
        if (!$this->isAdmin($chatId)) {
            $context->reply(__('errors.permission_denied'));
            return;
        }
        // Implementasi reset akun user
        $context->reply(__('commands.resetaccount.info'));
    }
    private function isAdmin(int $chatId): bool
    {
        $adminIds = config('telegram.admin_ids', []);
        return in_array($chatId, $adminIds);
    }
} 
