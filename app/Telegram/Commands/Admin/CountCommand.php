<?php

namespace App\Telegram\Commands\Admin;

use App\Telegram\Core\BaseCommand;
use App\Telegram\Contracts\TelegramContextInterface;
use App\Domain\Repositories\UserRepositoryInterface;

class CountCommand extends BaseCommand
{
    protected string $name = 'count';
    protected string $description = 'Hitung total user';
    protected bool $adminOnly = true;

    public function __construct(private UserRepositoryInterface $userRepository) {}

    public function handle(TelegramContextInterface $context): void
    {
        $chatId = $context->getChatId();
        if (!$this->isAdmin($chatId)) {
            $context->reply(__('errors.permission_denied'));
            return;
        }
        $total = $this->userRepository->countAll();
        $context->reply(__('commands.count.result', ['total' => $total]));
    }
    private function isAdmin(int $chatId): bool
    {
        $adminIds = config('telegram.admin_ids', []);
        return in_array($chatId, $adminIds);
    }
} 
