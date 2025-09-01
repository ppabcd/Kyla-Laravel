<?php

namespace App\Telegram\Core;

use App\Telegram\Contracts\CommandInterface;
use App\Telegram\Contracts\TelegramContextInterface;
use App\Telegram\Middleware\MiddlewareInterface;
use Illuminate\Support\Facades\Log;

abstract class BaseCommand implements CommandInterface
{
    /**
     * The command name(s)
     */
    protected string|array $commandName = '';

    /**
     * Middleware stack for this command
     */
    protected array $middlewares = [];

    /**
     * Whether this command is enabled
     */
    protected bool $enabled = true;

    /**
     * Command description
     */
    protected string $description = '';

    /**
     * Command usage
     */
    protected string $usage = '';

    /**
     * Execute the command
     */
    abstract public function handle(TelegramContextInterface $context): void;

    /**
     * Get command name(s)
     */
    public function getCommandName(): string|array
    {
        return $this->commandName;
    }

    /**
     * Get middlewares
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * Add middleware to the command
     */
    public function addMiddleware(MiddlewareInterface $middleware): self
    {
        $this->middlewares[] = $middleware;

        return $this;
    }

    /**
     * Check if command is enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Get command description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Get command usage
     */
    public function getUsage(): string
    {
        return $this->usage;
    }

    /**
     * Execute command with middleware
     */
    public function execute(TelegramContextInterface $context): void
    {
        try {
            // Run through middleware stack
            $this->runMiddleware($context, 0);
        } catch (\Exception $e) {
            Log::error('Command execution failed', [
                'command' => $this->commandName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Run middleware stack recursively
     */
    protected function runMiddleware(TelegramContextInterface $context, int $index): void
    {
        if ($index >= count($this->middlewares)) {
            // All middleware passed, execute the command
            $this->handle($context);

            return;
        }

        $middleware = $this->middlewares[$index];
        $next = function (TelegramContextInterface $context) use ($index) {
            $this->runMiddleware($context, $index + 1);
        };

        $middleware->handle($context, $next);
    }

    /**
     * Send text message
     */
    protected function sendMessage(TelegramContextInterface $context, string $text, array $options = []): void
    {
        $context->sendMessage($text, $options);
    }

    /**
     * Send keyboard
     */
    protected function sendKeyboard(TelegramContextInterface $context, string $text, array $keyboard, array $options = []): void
    {
        $context->sendKeyboard($text, $keyboard, $options);
    }

    /**
     * Send inline keyboard
     */
    protected function sendInlineKeyboard(TelegramContextInterface $context, string $text, array $keyboard, array $options = []): void
    {
        $context->sendInlineKeyboard($text, $keyboard, $options);
    }

    /**
     * Delete message
     */
    protected function deleteMessage(TelegramContextInterface $context, ?int $messageId = null): void
    {
        $context->deleteMessage($messageId);
    }

    /**
     * Answer callback query
     */
    protected function answerCallbackQuery(TelegramContextInterface $context, string $text = '', array $options = []): void
    {
        $context->answerCallbackQuery($text, $options);
    }
}
