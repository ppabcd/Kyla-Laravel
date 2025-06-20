<?php

namespace App\Telegram\Commands\Admin;

use App\Telegram\Core\BaseCommand;
use App\Telegram\Contracts\TelegramContextInterface;

class CommandsCommand extends BaseCommand
{
    protected string $name = 'commands';
    protected string $description = 'Daftar semua command admin';
    protected bool $adminOnly = true;

    public function handle(TelegramContextInterface $context): void
    {
        $chatId = $context->getMessage()->chat->id;
        if (!$this->isAdmin($chatId)) {
            $context->reply(__('errors.permission_denied'));
            return;
        }
        $text = __('commands.commands.list');
        $context->reply($text);
    }
    private function isAdmin(int $chatId): bool
    {
        $adminIds = config('telegram.admin_ids', []);
        return in_array($chatId, $adminIds);
    }
} 
