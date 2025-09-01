<?php

namespace App\Telegram\Commands\Admin;

use App\Domain\Repositories\UserRepositoryInterface;
use App\Telegram\Contracts\TelegramContextInterface;
use App\Telegram\Core\BaseCommand;

class FindCommand extends BaseCommand
{
    protected string $name = 'find';

    protected string $description = 'Cari user berdasarkan kriteria';

    protected bool $adminOnly = true;

    public function __construct(private UserRepositoryInterface $userRepository) {}

    public function handle(TelegramContextInterface $context): void
    {
        $chatId = $context->getChatId();
        if (! $this->isAdmin($chatId)) {
            $context->reply(__('errors.permission_denied'));

            return;
        }
        // Implementasi pencarian user sesuai kebutuhan
        $context->reply(__('commands.find.info'));
    }

    private function isAdmin(int $chatId): bool
    {
        $adminIds = config('telegram.admin_ids', []);

        return in_array($chatId, $adminIds);
    }
}
