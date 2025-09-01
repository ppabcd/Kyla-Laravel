<?php

namespace Tests\Feature;

use App\Infrastructure\Repositories\UserRepository;
use App\Services\CaptchaService;
use App\Services\ConversationService;
use App\Telegram\Middleware\CheckAnnouncementMiddleware;
use App\Telegram\Middleware\CheckPromotionMiddleware;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CacheRefactoringTest extends TestCase
{
    public function test_cache_operations_work_without_redis()
    {
        // Test basic cache operations
        Cache::put('test_key', 'test_value', 60);
        $this->assertEquals('test_value', Cache::get('test_key'));

        Cache::forget('test_key');
        $this->assertNull(Cache::get('test_key'));
    }

    public function test_cache_lock_works_without_redis()
    {
        $lock = Cache::lock('test_lock', 10);
        $this->assertTrue($lock->get());

        $lock->release();

        // Should be able to acquire again after release
        $newLock = Cache::lock('test_lock', 10);
        $this->assertTrue($newLock->get());
        $newLock->release();
    }

    public function test_captcha_service_can_be_instantiated()
    {
        $keyboardService = $this->app->make(\App\Telegram\Services\KeyboardService::class);
        $captchaService = new CaptchaService($keyboardService);
        $this->assertInstanceOf(CaptchaService::class, $captchaService);
    }

    public function test_promotion_middleware_can_be_instantiated()
    {
        $middleware = new CheckPromotionMiddleware;
        $this->assertInstanceOf(CheckPromotionMiddleware::class, $middleware);
    }

    public function test_announcement_middleware_can_be_instantiated()
    {
        $middleware = new CheckAnnouncementMiddleware;
        $this->assertInstanceOf(CheckAnnouncementMiddleware::class, $middleware);
    }

    public function test_user_repository_can_be_instantiated()
    {
        $repository = new UserRepository;
        $this->assertInstanceOf(UserRepository::class, $repository);
    }

    public function test_conversation_service_can_be_instantiated()
    {
        $pairRepo = $this->app->make(\App\Domain\Repositories\PairRepositoryInterface::class);
        $pairPendingRepo = $this->app->make(\App\Domain\Repositories\PairPendingRepositoryInterface::class);
        $conversationLogRepo = $this->app->make(\App\Domain\Repositories\ConversationLogRepositoryInterface::class);
        $ratingRepo = $this->app->make(\App\Domain\Repositories\RatingRepositoryInterface::class);
        $keyboardService = $this->app->make(\App\Telegram\Services\KeyboardService::class);
        $pendingService = $this->app->make(\App\Services\PendingService::class);

        $service = new ConversationService(
            $pairRepo,
            $pairPendingRepo,
            $conversationLogRepo,
            $ratingRepo,
            $keyboardService,
            $pendingService
        );

        $this->assertInstanceOf(ConversationService::class, $service);
    }

    public function test_cache_remember_works_in_user_repository()
    {
        // Run migrations for test database
        $this->artisan('migrate');

        $repository = new UserRepository;

        // This should not throw an exception even without Redis
        try {
            $totalUsers = $repository->getTotalUsers();
            $this->assertIsInt($totalUsers);
        } catch (\Exception $e) {
            $this->fail('UserRepository cache operations should work without Redis: '.$e->getMessage());
        }
    }

    public function test_cache_table_exists_and_cache_works()
    {
        // Run migrations to ensure cache table exists
        $this->artisan('migrate');

        // Verify cache table exists and is accessible
        $this->assertTrue(\Schema::hasTable('cache'));

        // Test cache operations work (regardless of storage backend in tests)
        Cache::put('db_test', 'database_cache_works', 60);
        $this->assertEquals('database_cache_works', Cache::get('db_test'));

        // Test that cache can be forgotten
        Cache::forget('db_test');
        $this->assertNull(Cache::get('db_test'));
    }
}
