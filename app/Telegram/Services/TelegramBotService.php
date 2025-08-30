<?php

namespace App\Telegram\Services;

use App\Telegram\Contracts\CallbackInterface;
use App\Telegram\Contracts\CommandInterface;
use App\Telegram\Contracts\TelegramContextInterface;
use App\Telegram\Core\TelegramContext;
use App\Telegram\Listeners\MessageListener;
use App\Telegram\Middleware\MiddlewareInterface;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use ReflectionClass;

class TelegramBotService
{
    protected array $commands = [];

    protected array $callbacks = [];

    protected array $globalMiddlewares = [];

    protected array $commandMiddlewares = [];

    protected array $callbackMiddlewares = [];

    public function __construct()
    {
        $this->loadGlobalMiddlewares();
        $this->autoRegisterCommands();
        $this->autoRegisterCallbacks();
    }

    /**
     * Load global middlewares from config
     */
    protected function loadGlobalMiddlewares(): void
    {
        $globalMiddlewares = config('telegram.middleware.global', []);

        foreach ($globalMiddlewares as $middlewareClass) {
            if (class_exists($middlewareClass)) {
                $this->globalMiddlewares[] = app($middlewareClass);
            }
        }

        $commandMiddlewares = config('telegram.middleware.commands', []);
        foreach ($commandMiddlewares as $middlewareClass) {
            if (class_exists($middlewareClass)) {
                $this->commandMiddlewares[] = app($middlewareClass);
            }
        }

        $callbackMiddlewares = config('telegram.middleware.callbacks', []);
        foreach ($callbackMiddlewares as $middlewareClass) {
            if (class_exists($middlewareClass)) {
                $this->callbackMiddlewares[] = app($middlewareClass);
            }
        }
    }

    /**
     * Auto register commands from the commands directory
     */
    protected function autoRegisterCommands(): void
    {
        if (! config('telegram.auto_registration.commands.enabled', true)) {
            return;
        }

        $commandsPath = config('telegram.auto_registration.commands.path', app_path('Telegram/Commands'));
        $namespace = config('telegram.auto_registration.commands.namespace', 'App\\Telegram\\Commands');

        if (! File::exists($commandsPath)) {
            Log::warning('Telegram commands directory does not exist', ['path' => $commandsPath]);

            return;
        }

        $files = File::allFiles($commandsPath);

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $className = $namespace.'\\'.$file->getBasename('.php');

            if (! class_exists($className)) {
                continue;
            }

            $reflection = new ReflectionClass($className);

            if ($reflection->isAbstract() || $reflection->isInterface()) {
                continue;
            }

            if (! $reflection->implementsInterface(CommandInterface::class)) {
                continue;
            }

            $command = app($className);

            if (! $command->isEnabled()) {
                continue;
            }

            $this->registerCommand($command);
        }

        Log::info('Telegram commands auto-registered', ['count' => count($this->commands)]);
    }

    /**
     * Auto register callbacks from the callbacks directory
     */
    protected function autoRegisterCallbacks(): void
    {
        if (! config('telegram.auto_registration.callbacks.enabled', true)) {
            return;
        }

        $callbacksPath = config('telegram.auto_registration.callbacks.path', app_path('Telegram/Callbacks'));
        $namespace = config('telegram.auto_registration.callbacks.namespace', 'App\\Telegram\\Callbacks');

        if (! File::exists($callbacksPath)) {
            Log::warning('Telegram callbacks directory does not exist', ['path' => $callbacksPath]);

            return;
        }

        $files = File::allFiles($callbacksPath);

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $className = $namespace.'\\'.$file->getBasename('.php');

            if (! class_exists($className)) {
                continue;
            }

            $reflection = new ReflectionClass($className);

            if ($reflection->isAbstract() || $reflection->isInterface()) {
                continue;
            }

            if (! $reflection->implementsInterface(CallbackInterface::class)) {
                continue;
            }

            $callback = app($className);

            if (! $callback->isEnabled()) {
                continue;
            }

            $this->registerCallback($callback);
        }

        Log::info('Telegram callbacks auto-registered', ['count' => count($this->callbacks)]);
    }

    /**
     * Register a command
     */
    public function registerCommand(CommandInterface $command): void
    {
        $commandNames = (array) $command->getCommandName();

        foreach ($commandNames as $commandName) {
            $commandName = Str::lower($commandName);

            // Add global middlewares
            foreach ($this->globalMiddlewares as $middleware) {
                $command->addMiddleware($middleware);
            }

            // Add general command middlewares
            foreach ($this->commandMiddlewares as $middleware) {
                $command->addMiddleware($middleware);
            }

            // Add per-command specific middlewares
            $perCommandMiddlewares = config("telegram.middleware.per_command.{$commandName}", []);
            foreach ($perCommandMiddlewares as $middlewareClass) {
                if (class_exists($middlewareClass)) {
                    $middleware = app($middlewareClass);
                    if ($middleware instanceof MiddlewareInterface) {
                        $command->addMiddleware($middleware);
                    }
                }
            }

            $this->commands[$commandName] = $command;
        }

        Log::debug('Telegram command registered', [
            'command' => $commandNames,
            'description' => $command->getDescription(),
        ]);
    }

    /**
     * Register a callback
     */
    public function registerCallback(CallbackInterface $callback): void
    {
        $callbackNames = (array) $callback->getCallbackName();

        foreach ($callbackNames as $callbackName) {
            $callbackName = Str::lower($callbackName);

            // Add global and callback middlewares
            foreach ($this->globalMiddlewares as $middleware) {
                $callback->addMiddleware($middleware);
            }

            foreach ($this->callbackMiddlewares as $middleware) {
                $callback->addMiddleware($middleware);
            }

            $this->callbacks[$callbackName] = $callback;
        }

        Log::debug('Telegram callback registered', [
            'callback' => $callbackNames,
            'description' => $callback->getDescription(),
        ]);
    }

    /**
     * Register a callback by name and class
     */
    public function registerCallbackByName(string $callbackName, string $callbackClass): void
    {
        if (! class_exists($callbackClass)) {
            Log::error('Telegram callback class not found', ['class' => $callbackClass]);

            return;
        }

        $callback = app($callbackClass);

        if (! $callback instanceof CallbackInterface) {
            Log::error('Telegram callback class does not implement CallbackInterface', ['class' => $callbackClass]);

            return;
        }

        $this->registerCallback($callback);
    }

    /**
     * Register a command by class name
     */
    public function registerCommandByClass(string $commandClass): void
    {
        if (! class_exists($commandClass)) {
            Log::error('Telegram command class not found', ['class' => $commandClass]);

            return;
        }

        $command = app($commandClass);

        if (! $command instanceof CommandInterface) {
            Log::error('Telegram command class does not implement CommandInterface', ['class' => $commandClass]);

            return;
        }

        $this->registerCommand($command);
    }

    /**
     * Register global middleware
     */
    public function registerGlobalMiddleware(string $middlewareClass): void
    {
        if (! class_exists($middlewareClass)) {
            Log::error('Telegram middleware class not found', ['class' => $middlewareClass]);

            return;
        }

        $middleware = app($middlewareClass);

        if (! $middleware instanceof MiddlewareInterface) {
            Log::error('Telegram middleware class does not implement MiddlewareInterface', ['class' => $middlewareClass]);

            return;
        }

        $this->globalMiddlewares[] = $middleware;

        Log::debug('Telegram global middleware registered', ['middleware' => $middlewareClass]);
    }

    /**
     * Register command middleware
     */
    public function registerCommandMiddleware(string $middlewareClass): void
    {
        if (! class_exists($middlewareClass)) {
            Log::error('Telegram middleware class not found', ['class' => $middlewareClass]);

            return;
        }

        $middleware = app($middlewareClass);

        if (! $middleware instanceof MiddlewareInterface) {
            Log::error('Telegram middleware class does not implement MiddlewareInterface', ['class' => $middlewareClass]);

            return;
        }

        $this->commandMiddlewares[] = $middleware;

        Log::debug('Telegram command middleware registered', ['middleware' => $middlewareClass]);
    }

    /**
     * Register callback middleware
     */
    public function registerCallbackMiddleware(string $middlewareClass): void
    {
        if (! class_exists($middlewareClass)) {
            Log::error('Telegram middleware class not found', ['class' => $middlewareClass]);

            return;
        }

        $middleware = app($middlewareClass);

        if (! $middleware instanceof MiddlewareInterface) {
            Log::error('Telegram middleware class does not implement MiddlewareInterface', ['class' => $middlewareClass]);

            return;
        }

        $this->callbackMiddlewares[] = $middleware;

        Log::debug('Telegram callback middleware registered', ['middleware' => $middlewareClass]);
    }

    /**
     * Handle incoming Telegram update
     */
    public function handleUpdate(array $update): void
    {
        try {
            $context = new TelegramContext($update, config('telegram.bot_token'));

            // Check if it's a callback query
            if (isset($update['callback_query'])) {
                $this->handleCallbackQuery($context);

                return;
            }

            // Check if it's a message with text
            if (isset($update['message']['text'])) {
                $this->handleMessage($context);

                return;
            }

            // Handle other types of updates
            $this->handleOtherUpdate($context);

        } catch (\Exception $e) {
            Log::error('Error handling Telegram update', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'update' => $update,
            ]);
        }
    }

    /**
     * Handle message with text
     */
    protected function handleMessage(TelegramContextInterface $context): void
    {
        // Run global middlewares first
        $this->runMiddlewares($this->globalMiddlewares, $context, function () use ($context) {
            $this->handleMessageWithCommandMiddlewares($context);
        });
    }

    /**
     * Handle message with command middlewares
     */
    protected function handleMessageWithCommandMiddlewares(TelegramContextInterface $context): void
    {
        // Run command middlewares
        $this->runMiddlewares($this->commandMiddlewares, $context, function () use ($context) {
            $this->processMessage($context);
        });
    }

    /**
     * Process message (actual command handling)
     */
    protected function processMessage(TelegramContextInterface $context): void
    {
        $text = $context->getText();

        if (! $text) {
            return;
        }

        // Check if it's a command (starts with /)
        if (Str::startsWith($text, '/')) {
            $commandName = Str::lower(Str::after($text, '/'));

            // Remove bot username if present
            $commandName = Str::before($commandName, '@');

            // Remove arguments
            $commandName = Str::before($commandName, ' ');

            if (isset($this->commands[$commandName])) {
                $command = $this->commands[$commandName];
                $command->execute($context);

                return;
            }

            // Command not found
            Log::warning('Telegram command not found', ['command' => $commandName]);
            $context->sendMessage(__('errors.command_not_found'));

            return;
        }

        // Handle regular text messages using MessageListener
        $messageListener = app(\App\Telegram\Listeners\MessageListener::class);
        $messageListener->handleTextMessage($context);
    }

    /**
     * Handle callback query
     */
    protected function handleCallbackQuery(TelegramContextInterface $context): void
    {
        // Run global middlewares first
        $this->runMiddlewares($this->globalMiddlewares, $context, function () use ($context) {
            $this->handleCallbackQueryWithMiddlewares($context);
        });
    }

    /**
     * Handle callback query with middlewares
     */
    protected function handleCallbackQueryWithMiddlewares(TelegramContextInterface $context): void
    {
        // Run callback middlewares
        $this->runMiddlewares($this->callbackMiddlewares, $context, function () use ($context) {
            $this->processCallbackQuery($context);
        });
    }

    /**
     * Process callback query (actual callback handling)
     */
    protected function processCallbackQuery(TelegramContextInterface $context): void
    {
        $callbackData = $context->getCallbackData();

        if (! $callbackData) {
            Log::debug('No callback data found');

            return;
        }

        Log::debug('Processing callback query', ['original_data' => $callbackData]);

        $callbackData = Str::lower($callbackData);
        Log::debug('Lowercase callback data', ['callback_data' => $callbackData]);

        // First, try exact match for full callback data
        if (isset($this->callbacks[$callbackData])) {
            Log::debug('Found exact match callback', ['callback' => $callbackData, 'class' => get_class($this->callbacks[$callbackData])]);
            $callback = $this->callbacks[$callbackData];
            $callback->execute($context);

            return;
        }

        // If not found, try parsing as action:data format
        $parts = explode(':', $callbackData, 2);
        $action = $parts[0];
        Log::debug('Trying action parsing', ['action' => $action, 'parts' => $parts]);

        if (isset($this->callbacks[$action])) {
            Log::debug('Found action match callback', ['action' => $action, 'class' => get_class($this->callbacks[$action])]);
            $callback = $this->callbacks[$action];
            $callback->execute($context);

            return;
        }

        // Callback not found
        Log::warning('Telegram callback not found', [
            'callback' => $callbackData,
            'action' => $action,
            'available_callbacks' => array_keys($this->callbacks),
        ]);
        $context->answerCallbackQuery(__('errors.callback_not_found'));
    }

    /**
     * Run middlewares
     */
    protected function runMiddlewares(array $middlewares, TelegramContextInterface $context, callable $next): void
    {
        if (empty($middlewares)) {
            $next($context);

            return;
        }

        $index = 0;
        $runNext = function () use (&$index, $middlewares, $context, $next, &$runNext) {
            if ($index >= count($middlewares)) {
                $next($context);

                return;
            }

            $middleware = $middlewares[$index];
            $index++;

            $middleware->handle($context, $runNext);
        };

        $runNext();
    }

    /**
     * Handle other types of updates
     */
    protected function handleOtherUpdate(TelegramContextInterface $context): void
    {
        // Use MessageListener to handle media and other message types
        $messageListener = app(\App\Telegram\Listeners\MessageListener::class);

        $update = $context->getUpdate();
        $message = $update['message'] ?? null;

        if (! $message) {
            Log::debug('No message found in update', ['update' => $update]);

            return;
        }

        // Handle media messages
        $hasMedia = isset($message['photo']) ||
            isset($message['video']) ||
            isset($message['document']) ||
            isset($message['audio']) ||
            isset($message['voice']) ||
            isset($message['sticker']) ||
            isset($message['animation']);

        if ($hasMedia) {
            $messageListener->handleMediaMessage($context);

            return;
        }

        // Handle other message types (location, contact, etc.)
        if (isset($message['location']) || isset($message['contact'])) {
            $messageListener->handleOtherMessage($context);

            return;
        }

        // Log unknown update types
        Log::debug('Telegram unknown update type received', [
            'message_keys' => array_keys($message),
            'update_keys' => array_keys($update),
        ]);
    }

    /**
     * Get registered commands
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    /**
     * Get registered callbacks
     */
    public function getCallbacks(): array
    {
        return $this->callbacks;
    }

    /**
     * Set webhook
     */
    public function setWebhook(string $url, array $options = []): array
    {
        $context = new TelegramContext([], config('telegram.bot_token'));

        return $context->setWebhook($url, $options);
    }

    /**
     * Get webhook info
     */
    public function getWebhookInfo(): array
    {
        $context = new TelegramContext([], config('telegram.bot_token'));

        return $context->getWebhookInfo();
    }

    /**
     * Delete webhook
     */
    public function deleteWebhook(array $options = []): array
    {
        $context = new TelegramContext([], config('telegram.bot_token'));

        return $context->deleteWebhook($options);
    }

    /**
     * Get bot info
     */
    public function getMe(): array
    {
        $context = new TelegramContext([], config('telegram.bot_token'));

        return $context->getMe();
    }
}
