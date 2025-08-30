<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessTelegramUpdateJob;
use App\Telegram\Services\TelegramBotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    protected TelegramBotService $telegramService;

    public function __construct(TelegramBotService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Handle incoming Telegram webhook
     */
    public function handle(Request $request): Response
    {
        try {
            // Verify webhook secret if configured
            $webhookSecret = config('telegram.webhook_secret');
            if ($webhookSecret) {
                $signature = $request->header('X-Telegram-Bot-Api-Secret-Token');
                if ($signature !== $webhookSecret) {
                    Log::warning('Invalid webhook signature', [
                        'received' => $signature,
                        'expected' => $webhookSecret,
                    ]);

                    return response('Unauthorized', 401);
                }
            }

            // Get update data
            $update = $request->all();

            if (empty($update)) {
                Log::warning('Empty update received from Telegram');

                return response('OK', 200);
            }

            // Log incoming update
            Log::info('Telegram webhook received', [
                'update_id' => $update['update_id'] ?? null,
                'has_message' => isset($update['message']),
                'has_callback_query' => isset($update['callback_query']),
                'chat_id' => $update['message']['chat']['id'] ?? $update['callback_query']['message']['chat']['id'] ?? null,
                'user_id' => $update['message']['from']['id'] ?? $update['callback_query']['from']['id'] ?? null,
            ]);

            // Process update asynchronously if queue is configured
            if (config('telegram.queue.enabled', false)) {
                dispatch(new ProcessTelegramUpdateJob($update));
            } else {
                // Process update synchronously
                $this->telegramService->handleUpdate($update);
            }

            return response('OK', 200);

        } catch (\Exception $e) {
            Log::error('Error processing Telegram webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return response('Internal Server Error', 500);
        }
    }

    /**
     * Set webhook URL
     */
    public function setWebhook(Request $request): JsonResponse
    {
        try {
            $url = $request->input('url');

            if (! $url) {
                return response()->json(['error' => 'URL is required'], 400);
            }

            $result = $this->telegramService->setWebhook($url, [
                'allowed_updates' => [
                    'message',
                    'callback_query',
                    'pre_checkout_query',
                    'edited_message',
                    'poll',
                    'poll_answer',
                    'chat_member',
                ],
                'drop_pending_updates' => true,
            ]);

            if ($result) {
                Log::info('Webhook set successfully', ['url' => $url]);

                return response()->json(['success' => true, 'result' => $result]);
            } else {
                Log::error('Failed to set webhook', ['url' => $url]);

                return response()->json(['error' => 'Failed to set webhook'], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error setting webhook: '.$e->getMessage());

            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Delete webhook
     */
    public function deleteWebhook(): JsonResponse
    {
        try {
            $result = $this->telegramService->deleteWebhook(['drop_pending_updates' => true]);

            if ($result) {
                Log::info('Webhook deleted successfully');

                return response()->json(['success' => true, 'result' => $result]);
            } else {
                Log::error('Failed to delete webhook');

                return response()->json(['error' => 'Failed to delete webhook'], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error deleting webhook: '.$e->getMessage());

            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Get webhook info
     */
    public function info(): JsonResponse
    {
        try {
            $info = $this->telegramService->getWebhookInfo();

            return response()->json([
                'success' => true,
                'data' => $info,
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting webhook info', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get bot info
     */
    public function botInfo(): JsonResponse
    {
        try {
            $info = $this->telegramService->getMe();

            return response()->json([
                'success' => true,
                'data' => $info,
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting bot info', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get registered commands
     */
    public function commands(): JsonResponse
    {
        try {
            $commands = $this->telegramService->getCommands();
            $callbacks = $this->telegramService->getCallbacks();

            $commandList = [];
            foreach ($commands as $name => $command) {
                $commandList[] = [
                    'name' => $name,
                    'description' => $command->getDescription(),
                    'usage' => $command->getUsage(),
                    'enabled' => $command->isEnabled(),
                ];
            }

            $callbackList = [];
            foreach ($callbacks as $name => $callback) {
                $callbackList[] = [
                    'name' => $name,
                    'description' => $callback->getDescription(),
                    'enabled' => $callback->isEnabled(),
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'commands' => $commandList,
                    'callbacks' => $callbackList,
                    'total_commands' => count($commands),
                    'total_callbacks' => count($callbacks),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting commands info', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test webhook endpoint
     */
    public function test(): JsonResponse
    {
        try {
            $botInfo = $this->telegramService->getMe();

            if ($botInfo) {
                return response()->json([
                    'success' => true,
                    'message' => 'Telegram bot is working correctly',
                    'bot_info' => $botInfo,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to get bot info',
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error testing webhook: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error testing webhook: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Health check endpoint
     */
    public function health(): JsonResponse
    {
        try {
            $botInfo = $this->telegramService->getMe();
            $webhookInfo = $this->telegramService->getWebhookInfo();

            $status = [
                'status' => 'healthy',
                'timestamp' => now()->toISOString(),
                'bot_info' => $botInfo,
                'webhook_info' => $webhookInfo,
                'services' => [
                    'telegram_api' => $botInfo ? 'connected' : 'disconnected',
                    'webhook' => $webhookInfo ? 'configured' : 'not_configured',
                ],
            ];

            return response()->json($status);

        } catch (\Exception $e) {
            Log::error('Health check failed: '.$e->getMessage());

            return response()->json([
                'status' => 'unhealthy',
                'timestamp' => now()->toISOString(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process update synchronously (for testing)
     */
    public function processUpdate(Request $request): JsonResponse
    {
        try {
            $update = $request->all();

            if (empty($update)) {
                return response()->json(['error' => 'No update data provided'], 400);
            }

            // Process update synchronously
            $this->telegramService->processUpdate($update);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Error processing update', [
                'error' => $e->getMessage(),
                'update' => $request->all(),
            ]);

            return response()->json(['error' => 'Failed to process update'], 500);
        }
    }
}
