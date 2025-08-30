<?php

namespace App\Telegram\Commands;

use App\Telegram\Contracts\CommandInterface;
use App\Telegram\Core\BaseCommand;
use Illuminate\Support\Facades\Log;

class RulesCommand extends BaseCommand implements CommandInterface
{
    protected string|array $commandName = 'rules';

    public function handle(\App\Telegram\Contracts\TelegramContextInterface $context): void
    {
        try {
            $rules = $this->getRules();
            $context->reply($rules, ['parse_mode' => 'Markdown']);

        } catch (\Exception $e) {
            Log::error('Error in RulesCommand', [
                'error' => $e->getMessage(),
                'user_id' => $context->getUser()['id'] ?? null,
            ]);

            $context->reply('❌ An error occurred while loading rules.');
        }
    }

    private function getRules(): string
    {
        return "📋 **Community Rules**\n\n".
               "To ensure a safe and enjoyable experience for everyone, please follow these rules:\n\n".
               "✅ **Do's:**\n".
               "• Be respectful and kind to others\n".
               "• Use appropriate language\n".
               "• Share genuine information about yourself\n".
               "• Report inappropriate behavior\n".
               "• Respect others' privacy\n\n".
               "❌ **Don'ts:**\n".
               "• Send spam or promotional content\n".
               "• Harass or bully other users\n".
               "• Share inappropriate or offensive content\n".
               "• Use fake profiles or impersonate others\n".
               "• Share personal information of others\n".
               "• Engage in illegal activities\n\n".
               "⚠️ **Consequences:**\n".
               "• First violation: Warning\n".
               "• Second violation: Temporary ban\n".
               "• Third violation: Permanent ban\n\n".
               "If you encounter someone breaking these rules, please use the report feature.\n\n".
               'Thank you for helping us maintain a positive community! 🙏';
    }
}
