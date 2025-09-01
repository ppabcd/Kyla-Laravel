<?php

namespace App\Telegram\Commands\Admin;

use App\Telegram\Contracts\TelegramContextInterface;
use App\Telegram\Core\BaseCommand;

class DebugCommand extends BaseCommand
{
    protected string $name = 'debug';

    protected string $description = 'Debug info bot';

    protected bool $adminOnly = true;

    public function handle(TelegramContextInterface $context): void
    {
        $chatId = $context->getChatId();
        if (! $this->isAdmin($chatId)) {
            $context->reply(__('errors.permission_denied'));

            return;
        }
        // Implementasi debug info sesuai kebutuhan
        $context->reply(__('commands.debug.info'));
    }

    private function isAdmin(int $chatId): bool
    {
        $adminIds = config('telegram.admin_ids', []);

        return in_array($chatId, $adminIds);
    }
}
