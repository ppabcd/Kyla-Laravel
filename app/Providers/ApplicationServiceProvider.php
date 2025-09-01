<?php

namespace App\Providers;

use App\Application\Services\BannedService;
use App\Application\Services\MatchingService;
use App\Application\Services\UserService;
use App\Domain\Repositories\PairRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Repositories\PairRepository;
use App\Infrastructure\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class ApplicationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register repositories
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(PairRepositoryInterface::class, PairRepository::class);

        // Register application services
        $this->app->singleton(UserService::class);
        $this->app->singleton(BannedService::class);
        $this->app->singleton(MatchingService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
