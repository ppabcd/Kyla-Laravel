<?php

namespace App\Telegram\Middleware;

use App\Telegram\Contracts\TelegramContextInterface;

interface MiddlewareInterface
{
    /**
     * Handle the middleware
     */
    public function handle(TelegramContextInterface $context, callable $next): void;
}
