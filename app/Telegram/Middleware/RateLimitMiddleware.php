<?php

namespace App\Telegram\Middleware;

use App\Telegram\Contracts\TelegramContextInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RateLimitMiddleware implements MiddlewareInterface
{
    protected int $maxRequestsPerSecond;
    protected int $burstLimit;

    public function __construct()
    {
        $this->maxRequestsPerSecond = config('telegram.rate_limit.max_requests_per_second', 30);
        $this->burstLimit = config('telegram.rate_limit.burst_limit', 100);
    }

    public function handle(TelegramContextInterface $context, callable $next): void
    {
        if (!config('telegram.rate_limit.enabled', true)) {
            $next($context);
            return;
        }

        $userId = $context->getUserId();
        $chatId = $context->getChatId();
        
        if (!$userId) {
            $next($context);
            return;
        }

        $key = "telegram_rate_limit:{$userId}";
        $now = time();

        // Get current rate limit data
        $rateLimitData = Cache::get($key, [
            'requests' => [],
            'burst_count' => 0,
            'last_reset' => $now
        ]);

        // Clean old requests (older than 1 second)
        $rateLimitData['requests'] = array_filter(
            $rateLimitData['requests'],
            fn($timestamp) => $timestamp > ($now - 1)
        );

        // Check if we're within the burst limit
        if ($rateLimitData['burst_count'] >= $this->burstLimit) {
            Log::warning('Telegram rate limit burst exceeded', [
                'user_id' => $userId,
                'chat_id' => $chatId,
                'burst_count' => $rateLimitData['burst_count'],
                'burst_limit' => $this->burstLimit
            ]);

            $context->sendMessage(__('errors.rate_limit_burst'));
            return;
        }

        // Check if we're within the per-second limit
        if (count($rateLimitData['requests']) >= $this->maxRequestsPerSecond) {
            Log::warning('Telegram rate limit per-second exceeded', [
                'user_id' => $userId,
                'chat_id' => $chatId,
                'requests_count' => count($rateLimitData['requests']),
                'max_requests_per_second' => $this->maxRequestsPerSecond
            ]);

            $context->sendMessage(__('errors.rate_limit_exceeded'));
            return;
        }

        // Add current request
        $rateLimitData['requests'][] = $now;
        $rateLimitData['burst_count']++;

        // Store updated rate limit data
        Cache::put($key, $rateLimitData, 60); // Cache for 1 minute

        // Execute next middleware/command/callback
        $next($context);
    }
} 
