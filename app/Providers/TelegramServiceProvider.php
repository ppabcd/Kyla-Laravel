<?php

namespace App\Providers;

use App\Telegram\Callbacks\AgeCallback;
use App\Telegram\Callbacks\BalanceCallback;
use App\Telegram\Callbacks\BannedActionMediaCallback;
use App\Telegram\Callbacks\BannedActionTextCallback;
use App\Telegram\Callbacks\BannedCallback;
use App\Telegram\Callbacks\CancelKeyboardCallback;
use App\Telegram\Callbacks\CaptchaCallback;
use App\Telegram\Callbacks\ConversationCallback;
use App\Telegram\Callbacks\CryptoDonationCallback;
use App\Telegram\Callbacks\DonationCallback;
use App\Telegram\Callbacks\EnableMediaCallback;
use App\Telegram\Callbacks\GenderCallback;
use App\Telegram\Callbacks\HelpCallback;
use App\Telegram\Callbacks\InterestCallback;
use App\Telegram\Callbacks\LanguageCallback;
use App\Telegram\Callbacks\LocationCallback;
use App\Telegram\Callbacks\NextCallback;
use App\Telegram\Callbacks\PendingCallback;
use App\Telegram\Callbacks\PrivacyCallback;
use App\Telegram\Callbacks\ProfileCallback;
use App\Telegram\Callbacks\QueueStatusCallback;
use App\Telegram\Callbacks\RatingCallback;
use App\Telegram\Callbacks\RejectActionMediaCallback;
// Admin Commands
use App\Telegram\Callbacks\RejectActionTextCallback;
use App\Telegram\Callbacks\ReportCallback;
use App\Telegram\Callbacks\RetrySubscribeCheckCallback;
use App\Telegram\Callbacks\SafeModeCallback;
use App\Telegram\Callbacks\SearchCallback;
use App\Telegram\Callbacks\SettingsCallback;
use App\Telegram\Callbacks\StopCallback;
use App\Telegram\Callbacks\TopUpCallback;
use App\Telegram\Commands\Admin\AnnouncementCommand;
use App\Telegram\Commands\Admin\BanCommand;
use App\Telegram\Commands\Admin\BanHistoryCommand;
use App\Telegram\Commands\Admin\BannedCommand;
use App\Telegram\Commands\Admin\ClaimCommand;
use App\Telegram\Commands\Admin\CommandsCommand;
use App\Telegram\Commands\Admin\CountCommand;
use App\Telegram\Commands\Admin\DebugCommand;
use App\Telegram\Commands\Admin\EncryptDecryptCommand;
// Callbacks
use App\Telegram\Commands\Admin\FindCommand;
use App\Telegram\Commands\Admin\MessageCommand;
use App\Telegram\Commands\Admin\PartnerCommand;
use App\Telegram\Commands\Admin\ResetAccountCommand;
use App\Telegram\Commands\Admin\StatsCommand;
use App\Telegram\Commands\Admin\UnbanCommand;
use App\Telegram\Commands\Admin\UnmuteCommand;
use App\Telegram\Commands\Admin\WordFilterCommand;
use App\Telegram\Commands\BalanceCommand;
use App\Telegram\Commands\DonasiCommand;
use App\Telegram\Commands\FeedbackCommand;
use App\Telegram\Commands\HelpCommand;
use App\Telegram\Commands\InterestCommand;
use App\Telegram\Commands\InvalidateSessionCommand;
use App\Telegram\Commands\LanguageCommand;
use App\Telegram\Commands\ModeCommand;
use App\Telegram\Commands\NextCommand;
use App\Telegram\Commands\PendingCommand;
use App\Telegram\Commands\PingCommand;
use App\Telegram\Commands\PrivacyCommand;
use App\Telegram\Commands\ProfileCommand;
use App\Telegram\Commands\ReferralCommand;
use App\Telegram\Commands\RulesCommand;
use App\Telegram\Commands\SettingsCommand;
use App\Telegram\Commands\StartCommand;
use App\Telegram\Commands\StopCommand;
use App\Telegram\Commands\TestCommand;
use App\Telegram\Commands\TestMiddlewareCommand;
use App\Telegram\Commands\TransferCommand;
use App\Telegram\Middleware\CheckBannedUserMiddleware;
use App\Telegram\Middleware\CheckUserMiddleware;
// Middleware
use App\Telegram\Middleware\LoggingMiddleware;
use App\Telegram\Services\TelegramBotService;
use Illuminate\Support\ServiceProvider;

class TelegramServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(TelegramBotService::class, function ($app) {
            return new TelegramBotService;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCallbacks();
        $this->registerMiddleware();
    }

    private function registerCommands(): void
    {
        $telegramService = $this->app->make(TelegramBotService::class);

        // Basic Commands
        $telegramService->registerCommandByClass(StartCommand::class);
        $telegramService->registerCommandByClass(HelpCommand::class);
        $telegramService->registerCommandByClass(NextCommand::class);
        $telegramService->registerCommandByClass(StopCommand::class);
        $telegramService->registerCommandByClass(ProfileCommand::class);
        $telegramService->registerCommandByClass(SettingsCommand::class);
        $telegramService->registerCommandByClass(LanguageCommand::class);
        $telegramService->registerCommandByClass(PingCommand::class);
        $telegramService->registerCommandByClass(RulesCommand::class);
        $telegramService->registerCommandByClass(FeedbackCommand::class);
        $telegramService->registerCommandByClass(TestCommand::class);
        $telegramService->registerCommandByClass(PrivacyCommand::class);
        $telegramService->registerCommandByClass(BalanceCommand::class);
        $telegramService->registerCommandByClass(DonasiCommand::class);
        $telegramService->registerCommandByClass(InterestCommand::class);
        $telegramService->registerCommandByClass(ModeCommand::class);
        $telegramService->registerCommandByClass(ReferralCommand::class);
        $telegramService->registerCommandByClass(TransferCommand::class);
        $telegramService->registerCommandByClass(PendingCommand::class);
        $telegramService->registerCommandByClass(InvalidateSessionCommand::class);
        $telegramService->registerCommandByClass(TestMiddlewareCommand::class);

        // Admin Commands
        $telegramService->registerCommandByClass(AnnouncementCommand::class);
        $telegramService->registerCommandByClass(BanCommand::class);
        $telegramService->registerCommandByClass(UnbanCommand::class);
        $telegramService->registerCommandByClass(StatsCommand::class);
        $telegramService->registerCommandByClass(BanHistoryCommand::class);
        $telegramService->registerCommandByClass(BannedCommand::class);
        $telegramService->registerCommandByClass(ClaimCommand::class);
        $telegramService->registerCommandByClass(CommandsCommand::class);
        $telegramService->registerCommandByClass(CountCommand::class);
        $telegramService->registerCommandByClass(DebugCommand::class);
        $telegramService->registerCommandByClass(EncryptDecryptCommand::class);
        $telegramService->registerCommandByClass(FindCommand::class);
        $telegramService->registerCommandByClass(MessageCommand::class);
        $telegramService->registerCommandByClass(PartnerCommand::class);
        $telegramService->registerCommandByClass(ResetAccountCommand::class);
        $telegramService->registerCommandByClass(WordFilterCommand::class);
        $telegramService->registerCommandByClass(UnmuteCommand::class);
    }

    private function registerCallbacks(): void
    {
        $telegramService = $this->app->make(TelegramBotService::class);

        // System Callbacks
        $telegramService->registerCallbackByName('help', HelpCallback::class);
        $telegramService->registerCallbackByName('profile', ProfileCallback::class);
        $telegramService->registerCallbackByName('search', SearchCallback::class);
        $telegramService->registerCallbackByName('next', NextCallback::class);
        $telegramService->registerCallbackByName('stop', StopCallback::class);
        $telegramService->registerCallbackByName('balance', BalanceCallback::class);
        $telegramService->registerCallbackByName('queue_status', QueueStatusCallback::class);

        // User Callbacks
        $telegramService->registerCallbackByName('gender', GenderCallback::class);
        $telegramService->registerCallbackByName('interest', InterestCallback::class);
        $telegramService->registerCallbackByName('language', LanguageCallback::class);
        $telegramService->registerCallbackByName('settings', SettingsCallback::class);
        $telegramService->registerCallbackByName('age', AgeCallback::class);
        $telegramService->registerCallbackByName('location', LocationCallback::class);
        $telegramService->registerCallbackByName('safe_mode', SafeModeCallback::class);
        $telegramService->registerCallbackByName('privacy', PrivacyCallback::class);
        $telegramService->registerCallbackByName('report', ReportCallback::class);
        $telegramService->registerCallbackByName('rating', RatingCallback::class);
        $telegramService->registerCallbackByName('pending', PendingCallback::class);

        // Media Callbacks
        $telegramService->registerCallbackByName('enable_media', EnableMediaCallback::class);
        $telegramService->registerCallbackByName('enable_media_confirm', EnableMediaCallback::class);
        $telegramService->registerCallbackByName('reject_action_media', RejectActionMediaCallback::class);
        $telegramService->registerCallbackByName('reject_action_text', RejectActionTextCallback::class);
        $telegramService->registerCallbackByName('top_up', TopUpCallback::class);
        $telegramService->registerCallbackByName('banned_action_media', BannedActionMediaCallback::class);
        $telegramService->registerCallbackByName('banned_action_text', BannedActionTextCallback::class);
        $telegramService->registerCallbackByName('banned', BannedCallback::class);

        // System Callbacks
        $telegramService->registerCallbackByName('cancel_keyboard', CancelKeyboardCallback::class);
        $telegramService->registerCallbackByName('captcha', CaptchaCallback::class);
        $telegramService->registerCallbackByName('conversation', ConversationCallback::class);
        $telegramService->registerCallbackByName('crypto_donation', CryptoDonationCallback::class);
        $telegramService->registerCallbackByName('donation', DonationCallback::class);
        $telegramService->registerCallbackByName('retry_subscribe_check', RetrySubscribeCheckCallback::class);
    }

    private function registerMiddleware(): void
    {
        $telegramService = $this->app->make(TelegramBotService::class);

        // Global Middleware (applied to all updates)
        $telegramService->registerGlobalMiddleware(LoggingMiddleware::class);
        $telegramService->registerGlobalMiddleware(CheckUserMiddleware::class);
        $telegramService->registerGlobalMiddleware(CheckBannedUserMiddleware::class);
    }
}
