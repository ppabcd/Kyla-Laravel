<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Application\Services\UserService;
use App\Application\Services\BannedService;
use App\Application\Services\MatchingService;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Domain\Repositories\PairRepositoryInterface;
use App\Infrastructure\Repositories\UserRepository;
use App\Infrastructure\Repositories\PairRepository;

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
