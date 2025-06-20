<?php

namespace App\Telegram\Callbacks;

use App\Telegram\Core\BaseCallback;
use App\Telegram\Contracts\CallbackInterface;
use App\Telegram\Core\TelegramContext;
use App\Application\Services\UserService;
use App\Domain\Repositories\UserRepositoryInterface;

class ReportCallback extends BaseCallback implements CallbackInterface
{
    protected string|array $callbackName = [
        'report-action',
        'report-action-porn',
        'report-action-ads',
        'report-action-cancel'
    ];

    public function __construct(
        private UserService $userService,
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(\App\Telegram\Contracts\TelegramContextInterface $context): void

    {
        $telegramUser = $context->getFrom();
        if (!$telegramUser) {
            $context->reply('❌ Unable to identify user');
            return;
        }
        $callbackData = $context->getCallbackQuery()['data'] ?? '';
        switch ($callbackData) {
            case 'report-action':
                $this->handleReportAction($context);
                break;
            case 'report-action-porn':
                $this->handleReportActionPorn($context);
                break;
            case 'report-action-ads':
                $this->handleReportActionAds($context);
                break;
            case 'report-action-cancel':
                $context->reply(__('report.cancel'));
                break;
        }
    }

    private function handleReportAction(TelegramContext $context): void
    {
        $message = __('report.reason');
        $keyboard = [
            [
                ['text' => __('report.reason_porn'), 'callback_data' => 'report-action-porn'],
                ['text' => __('report.reason_ads'), 'callback_data' => 'report-action-ads']
            ],
            [
                ['text' => __('report.cancel'), 'callback_data' => 'report-action-cancel']
            ]
        ];
        $context->reply($message, [
            'reply_markup' => [
                'inline_keyboard' => $keyboard
            ]
        ]);
    }

    private function handleReportActionPorn(TelegramContext $context): void
    {
        $context->reply(__('report.thank_you'));
    }

    private function handleReportActionAds(TelegramContext $context): void
    {
        $context->reply(__('report.thank_you'));
    }
} 
