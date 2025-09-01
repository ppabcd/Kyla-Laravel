<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SetupTelegramWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:setup-webhook {url?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup Telegram webhook URL';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $token = config('telegram.bot_token');
        $webhookUrl = $this->argument('url') ?? config('app.url').'/api/telegram/webhook';

        if (! $token) {
            $this->error('Telegram bot token not configured!');

            return 1;
        }

        $this->info("Setting up webhook to: {$webhookUrl}");

        try {
            $response = Http::post("https://api.telegram.org/bot{$token}/setWebhook", [
                'url' => $webhookUrl,
                'allowed_updates' => [
                    'message',
                    'callback_query',
                    'pre_checkout_query',
                    'edited_message',
                    'poll',
                    'poll_answer',
                    'chat_member',
                ],
            ]);

            if ($response->successful()) {
                $result = $response->json();
                if ($result['ok']) {
                    $this->info('Webhook setup successful!');
                    $this->info("Webhook URL: {$webhookUrl}");

                    return 0;
                } else {
                    $this->error('Webhook setup failed: '.($result['description'] ?? 'Unknown error'));

                    return 1;
                }
            } else {
                $this->error('Failed to communicate with Telegram API');

                return 1;
            }
        } catch (\Exception $e) {
            $this->error('Exception occurred: '.$e->getMessage());
            Log::error('Telegram webhook setup failed', [
                'error' => $e->getMessage(),
                'webhook_url' => $webhookUrl,
            ]);

            return 1;
        }
    }
}
