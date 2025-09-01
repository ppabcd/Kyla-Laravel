<?php

use App\Http\Controllers\TelegramWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Telegram Bot Routes
|--------------------------------------------------------------------------
|
| Here are the routes for the Telegram bot webhook and related endpoints.
|
*/

// Telegram webhook endpoint
Route::post('/webhook', [TelegramWebhookController::class, 'webhook'])
    ->name('telegram.webhook');

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'service' => 'telegram-bot',
    ]);
})->name('telegram.health');

// Set webhook endpoint (for development/deployment)
Route::post('/set-webhook', function () {
    // This would set the webhook URL with Telegram
    // Implementation depends on your Telegram API client
    return response()->json(['message' => 'Webhook setup endpoint']);
})->name('telegram.set-webhook');
