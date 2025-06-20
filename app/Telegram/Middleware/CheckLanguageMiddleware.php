<?php

namespace App\Telegram\Middleware;

use App\Telegram\Contracts\TelegramContextInterface;
use Illuminate\Support\Facades\Log;

class CheckLanguageMiddleware implements MiddlewareInterface
{
    public function handle(TelegramContextInterface $context, callable $next): void
    {
        $user = $context->getUserModel();
        
        if (!$user) {
            $next($context);
            return;
        }

        // Check if user has language preference set
        if (!$user->language_code || $user->language_code === 'en') {
            // Set default language based on Telegram user language
            $telegramUser = $context->getUser();
            $languageCode = $telegramUser['language_code'] ?? 'en';
            
            // Map Telegram language codes to supported languages
            $supportedLanguages = [
                'id' => 'id', // Indonesian
                'ms' => 'ms', // Malaysian
                'in' => 'in', // Hindi
                'en' => 'en', // English
            ];
            
            $languageCode = $supportedLanguages[$languageCode] ?? 'en';
            
            $user->language_code = $languageCode;
            $user->save();
            
            Log::info('User language set', [
                'user_id' => $user->id,
                'telegram_language' => $telegramUser['language_code'] ?? 'unknown',
                'set_language' => $languageCode
            ]);
        }

        $next($context);
    }
} 
