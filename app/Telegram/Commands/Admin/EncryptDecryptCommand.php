<?php

namespace App\Telegram\Commands\Admin;

use App\Telegram\Contracts\TelegramContextInterface;
use App\Telegram\Core\BaseCommand;

class EncryptDecryptCommand extends BaseCommand
{
    protected string $name = 'encrypt';

    protected string $description = 'Encrypt/Decrypt text';

    protected bool $adminOnly = true;

    public function handle(TelegramContextInterface $context): void
    {
        $chatId = $context->getChatId();
        if (! $this->isAdmin($chatId)) {
            $context->reply(__('errors.permission_denied'));

            return;
        }
        // Implementasi encrypt/decrypt sesuai kebutuhan
        $context->reply(__('commands.encrypt.info'));
    }

    private function isAdmin(int $chatId): bool
    {
        $adminIds = config('telegram.admin_ids', []);

        return in_array($chatId, $adminIds);
    }
}
