<?php

namespace App\Telegram\Contracts;

use App\Telegram\Middleware\MiddlewareInterface;

interface CommandInterface
{
    /**
     * Get command name(s)
     */
    public function getCommandName(): string|array;

    /**
     * Get middlewares
     */
    public function getMiddlewares(): array;

    /**
     * Add middleware to the command
     */
    public function addMiddleware(MiddlewareInterface $middleware): self;

    /**
     * Check if command is enabled
     */
    public function isEnabled(): bool;

    /**
     * Get command description
     */
    public function getDescription(): string;

    /**
     * Get command usage
     */
    public function getUsage(): string;

    /**
     * Execute the command
     */
    public function handle(TelegramContextInterface $context): void;

    /**
     * Execute command with middleware
     */
    public function execute(TelegramContextInterface $context): void;
} 
