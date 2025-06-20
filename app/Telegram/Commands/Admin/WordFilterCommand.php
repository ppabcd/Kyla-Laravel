<?php

namespace App\Telegram\Commands\Admin;

use App\Telegram\Core\BaseCommand;
use App\Telegram\Contracts\TelegramContextInterface;

class WordFilterCommand extends BaseCommand
{
    protected string $name = 'wordfilter';
    protected string $description = 'Kelola filter kata-kata';
    protected bool $adminOnly = true;

    public function handle(TelegramContextInterface $context): void
    {
        $chatId = $context->getMessage()->chat->id;
        if (!$this->isAdmin($chatId)) {
            $context->reply(__('errors.permission_denied'));
            return;
        }
        // Implementasi kelola filter kata-kata
        $context->reply(__('commands.wordfilter.info'));
    }
    private function isAdmin(int $chatId): bool
    {
        $adminIds = config('telegram.admin_ids', []);
        return in_array($chatId, $adminIds);
    }
} 
