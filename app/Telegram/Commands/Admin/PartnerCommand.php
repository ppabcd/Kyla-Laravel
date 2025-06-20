<?php

namespace App\Telegram\Commands\Admin;

use App\Telegram\Core\BaseCommand;
use App\Telegram\Contracts\TelegramContextInterface;
use App\Domain\Repositories\PairRepositoryInterface;

class PartnerCommand extends BaseCommand
{
    protected string $name = 'partner';
    protected string $description = 'Kelola partner/pairing user';
    protected bool $adminOnly = true;

    public function __construct(private PairRepositoryInterface $pairRepository) {}

    public function handle(TelegramContextInterface $context): void
    {
        $chatId = $context->getChatId();
        if (!$this->isAdmin($chatId)) {
            $context->reply(__('errors.permission_denied'));
            return;
        }
        // Implementasi kelola partner/pairing
        $context->reply(__('commands.partner.info'));
    }
    private function isAdmin(int $chatId): bool
    {
        $adminIds = config('telegram.admin_ids', []);
        return in_array($chatId, $adminIds);
    }
} 
