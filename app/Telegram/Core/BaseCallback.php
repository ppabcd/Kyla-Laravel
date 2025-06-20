<?php

namespace App\Telegram\Core;

use App\Telegram\Contracts\CallbackInterface;
use App\Telegram\Contracts\TelegramContextInterface;
use App\Telegram\Middleware\MiddlewareInterface;
use Illuminate\Support\Facades\Log;

abstract class BaseCallback implements CallbackInterface
{
    /**
     * The callback name(s)
     */
    protected string|array $callbackName = '';

    /**
     * Middleware stack for this callback
     */
    protected array $middlewares = [];

    /**
     * Whether this callback is enabled
     */
    protected bool $enabled = true;

    /**
     * Callback description
     */
    protected string $description = '';

    /**
     * Execute the callback
     */
    abstract public function handle(TelegramContextInterface $context): void;

    /**
     * Get callback name(s)
     */
    public function getCallbackName(): string|array
    {
        return $this->callbackName;
    }

    /**
     * Get middlewares
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * Add middleware to the callback
     */
    public function addMiddleware(MiddlewareInterface $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    /**
     * Check if callback is enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Get callback description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Execute callback with middleware
     */
    public function execute(TelegramContextInterface $context): void
    {
        try {
            // Run through middleware stack
            $this->runMiddleware($context, 0);
        } catch (\Exception $e) {
            Log::error('Callback execution failed', [
                'callback' => $this->callbackName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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
            // All middleware passed, execute the callback
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

    /**
     * Edit message text
     */
    protected function editMessageText(TelegramContextInterface $context, string $text, array $options = []): void
    {
        $context->editMessageText($text, $options);
    }

    /**
     * Edit message reply markup
     */
    protected function editMessageReplyMarkup(TelegramContextInterface $context, array $replyMarkup, array $options = []): void
    {
        $context->editMessageReplyMarkup($replyMarkup, $options);
    }
}
