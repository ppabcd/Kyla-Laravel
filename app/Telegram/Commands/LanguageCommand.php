<?php

namespace App\Telegram\Commands;

use App\Telegram\Contracts\CommandInterface;
use App\Telegram\Core\BaseCommand;

class LanguageCommand extends BaseCommand implements CommandInterface
{
    protected string|array $commandName = 'language';

    public function handle(\App\Telegram\Contracts\TelegramContextInterface $context): void
    {
        $message = __('language.selector');
        $keyboard = [
            [
                ['text' => '🇺🇸 English', 'callback_data' => 'lang-en'],
                ['text' => '🇮🇩 Bahasa Indonesia', 'callback_data' => 'lang-id'],
            ],
            [
                ['text' => '🇲🇾 Bahasa Melayu', 'callback_data' => 'lang-my'],
                ['text' => '🇮🇳 हिन्दी', 'callback_data' => 'lang-in'],
            ],
            [
                ['text' => '🌍 Contribute Translation', 'callback_data' => 'lang-contribute'],
            ],
            [
                ['text' => '🔙 Back', 'callback_data' => 'profile-back'],
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
