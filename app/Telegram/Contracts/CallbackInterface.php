<?php

namespace App\Telegram\Contracts;

use App\Telegram\Middleware\MiddlewareInterface;

interface CallbackInterface
{
    /**
     * Get callback name(s)
     */
    public function getCallbackName(): string|array;

    /**
     * Get middlewares
     */
    public function getMiddlewares(): array;

    /**
     * Add middleware to the callback
     */
    public function addMiddleware(MiddlewareInterface $middleware): self;

    /**
     * Check if callback is enabled
     */
    public function isEnabled(): bool;

    /**
     * Get callback description
     */
    public function getDescription(): string;

    /**
     * Execute the callback
     */
    public function handle(TelegramContextInterface $context): void;

    /**
     * Execute callback with middleware
     */
    public function execute(TelegramContextInterface $context): void;
}
