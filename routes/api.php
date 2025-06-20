<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelegramWebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Telegram Webhook Routes
Route::prefix('telegram')->group(function () {
    Route::post('/webhook', [TelegramWebhookController::class, 'handle'])->name('telegram.webhook');
    Route::get('/webhook/info', [TelegramWebhookController::class, 'info'])->name('telegram.webhook.info');
    Route::get('/bot/info', [TelegramWebhookController::class, 'botInfo'])->name('telegram.bot.info');
    Route::get('/commands', [TelegramWebhookController::class, 'commands'])->name('telegram.commands');
}); 
