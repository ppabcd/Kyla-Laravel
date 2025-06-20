<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Domain Repository Interfaces
use App\Domain\Repositories\UserRepositoryInterface;
use App\Domain\Repositories\PairRepositoryInterface;
use App\Domain\Repositories\ConversationLogRepositoryInterface;
use App\Domain\Repositories\BalanceTransactionRepositoryInterface;
use App\Domain\Repositories\ReportRepositoryInterface;
use App\Domain\Repositories\WordFilterRepositoryInterface;
use App\Domain\Repositories\PairPendingRepositoryInterface;
use App\Domain\Repositories\RatingRepositoryInterface;

// Infrastructure Repository Implementations
use App\Infrastructure\Repositories\UserRepository;
use App\Infrastructure\Repositories\PairRepository;
use App\Infrastructure\Repositories\ConversationLogRepository;
use App\Infrastructure\Repositories\BalanceTransactionRepository;
use App\Infrastructure\Repositories\ReportRepository;
use App\Infrastructure\Repositories\WordFilterRepository;
use App\Infrastructure\Repositories\PairPendingRepository;
use App\Infrastructure\Repositories\RatingRepository;

/**
 * Repository Service Provider
 * 
 * Binds repository interfaces to their implementations
 * Following Dependency Inversion Principle and Clean Architecture
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // User Repository Binding
        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );

        // Pair Repository Binding
        $this->app->bind(
            PairRepositoryInterface::class,
            PairRepository::class
        );

        // Conversation Log Repository Binding
        $this->app->bind(
            ConversationLogRepositoryInterface::class,
            ConversationLogRepository::class
        );

        // Balance Transaction Repository Binding
        $this->app->bind(
            BalanceTransactionRepositoryInterface::class,
            BalanceTransactionRepository::class
        );

        // Report Repository Binding
        $this->app->bind(
            ReportRepositoryInterface::class,
            ReportRepository::class
        );

        // Word Filter Repository Binding
        $this->app->bind(
            WordFilterRepositoryInterface::class,
            WordFilterRepository::class
        );

        // Pair Pending Repository Binding
        $this->app->bind(
            PairPendingRepositoryInterface::class,
            PairPendingRepository::class
        );

        // Rating Repository Binding
        $this->app->bind(
            RatingRepositoryInterface::class,
            RatingRepository::class
        );

        // Bind Telegram services
        $this->app->singleton(\App\Telegram\Services\KeyboardService::class);
        $this->app->singleton(\App\Listeners\MessageListener::class);
        $this->app->singleton(\App\Telegram\Middleware\CheckBannedUserMiddleware::class);

        // Register command handlers
        $this->registerCommandHandlers();

        // Register callback handlers
        $this->registerCallbackHandlers();
    }

    /**
     * Register command handlers
     */
    private function registerCommandHandlers(): void
    {
        $commands = [
            \App\Telegram\Commands\StartCommand::class,
            \App\Telegram\Commands\BalanceCommand::class,
            \App\Telegram\Commands\HelpCommand::class,
            \App\Telegram\Commands\StopCommand::class,
            \App\Telegram\Commands\NextCommand::class,
        ];

        foreach ($commands as $command) {
            $this->app->bind($command);
        }
    }

    /**
     * Register callback handlers
     */
    private function registerCallbackHandlers(): void
    {
        $callbacks = [
            \App\Telegram\Callbacks\GenderCallback::class,
            \App\Telegram\Callbacks\InterestCallback::class,
        ];

        foreach ($callbacks as $callback) {
            $this->app->bind($callback);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            UserRepositoryInterface::class,
            PairRepositoryInterface::class,
            ConversationLogRepositoryInterface::class,
            BalanceTransactionRepositoryInterface::class,
            ReportRepositoryInterface::class,
            WordFilterRepositoryInterface::class,
            PairPendingRepositoryInterface::class,
            RatingRepositoryInterface::class,
        ];
    }
}
