<?php

namespace App\Telegram\Middleware;

use App\Telegram\Contracts\TelegramContextInterface;
use Illuminate\Support\Facades\Cache;

class CheckAnnouncementMiddleware implements MiddlewareInterface
{
    public function __construct() {}

    public function handle(TelegramContextInterface $context, callable $next): void
    {
        $user = $context->getUserModel();

        if (! $user) {
            $context->sendMessage('❌ User not found');

            return;
        }

        // Check if user should receive announcements
        if ($user->is_get_announcement) {
            $announcementKey = "announcement_shown:{$user->id}";
            if (! Cache::has($announcementKey)) {
                // Show announcement message
                $this->sendAnnouncementMessage($context, $user);

                // Mark announcement as shown (cache for 24 hours)
                Cache::put($announcementKey, true, 86400);

                return;
            }
        }

        // Announcement not needed or already shown, continue to next middleware
        $next($context);
    }

    private function sendAnnouncementMessage(TelegramContextInterface $context, $user): void
    {
        $message = __('messages.announcement.message', [], $user->language_code ?? 'en');
        $context->sendMessage($message);
    }
}
