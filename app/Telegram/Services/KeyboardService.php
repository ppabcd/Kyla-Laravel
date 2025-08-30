<?php

namespace App\Telegram\Services;

use App\Enums\BanExplicitTypeEnum;
use App\Enums\KeyboardButtonEnum;
use App\Helpers\CodeHelper;
use App\Services\ArxistService;

class KeyboardService
{
    private ArxistService $arxistService;

    public function __construct(ArxistService $arxistService)
    {
        $this->arxistService = $arxistService;
    }

    public function getArxistDonationKeyboard(array $data): array
    {
        $url = $this->arxistService->donation($data);

        return [
            'inline_keyboard' => [
                [
                    ['text' => '💰 Arxist', 'web_app' => ['url' => $url]],
                    ['text' => '💰 Arxist', 'url' => $url],
                ],
                [
                    ['text' => '₿ Cryptocurrencies ⟠', 'callback_data' => 'crypto-donation'],
                ],
            ],
        ];
    }

    public function getDonationKeyboard(array $translations = []): array
    {
        return [
            'inline_keyboard' => [
                [['text' => $translations['btn.donation'] ?? '💰 Donation', 'callback_data' => 'donasi']],
                [['text' => $translations['btn.top_up'] ?? '💎 Top Up', 'callback_data' => 'topup']],
                [['text' => $translations['btn.priority_search'] ?? '🚀 Priority Search', 'callback_data' => 'priority-search']],
            ],
        ];
    }

    public function getChangeToAllGenderKeyboard(array $translations = []): array
    {
        return [
            'inline_keyboard' => [
                [['text' => $translations['btn.search_general_gender'] ?? '🔍 Search All', 'callback_data' => 'general-search']],
            ],
        ];
    }

    public function getGenderKeyboard(): array
    {
        return [
            'inline_keyboard' => [
                [
                    KeyboardButtonEnum::GENDER_MALE->toButton(),
                    KeyboardButtonEnum::GENDER_FEMALE->toButton(),
                ],
            ],
        ];
    }

    public function getInterestKeyboard($user = null): array
    {
        $keyboard = [];

        // Only show opposite gender option if user gender is set, in one row
        if ($user && $user->gender) {
            if ($user->gender === 'male') {
                $keyboard[] = [
                    KeyboardButtonEnum::INTEREST_FEMALE->toButton(),
                    KeyboardButtonEnum::INTEREST_ALL->toButton(),
                ];
            } elseif ($user->gender === 'female') {
                $keyboard[] = [
                    KeyboardButtonEnum::INTEREST_MALE->toButton(),
                    KeyboardButtonEnum::INTEREST_ALL->toButton(),
                ];
            }
        } else {
            // Fallback to both options if user gender is not set (shouldn't normally happen)
            $keyboard[] = [
                KeyboardButtonEnum::INTEREST_MALE->toButton(),
                KeyboardButtonEnum::INTEREST_FEMALE->toButton(),
                KeyboardButtonEnum::INTEREST_ALL->toButton(),
            ];
        }

        return [
            'inline_keyboard' => $keyboard,
        ];
    }

    public function getInterestAllKeyboard(array $translations = []): array
    {
        return [
            'inline_keyboard' => [
                [['text' => $translations['btn.cross_gender'] ?? '👥 Cross Gender', 'callback_data' => 'change_interest_all']],
                [['text' => $translations['btn.location'] ?? '📍 Location', 'callback_data' => 'location']],
            ],
        ];
    }

    public function getSearchKeyboard(): array
    {
        return [
            'inline_keyboard' => [
                [
                    KeyboardButtonEnum::SEARCH->toButton(),
                    KeyboardButtonEnum::SETTINGS->toButton(),
                ],
                [
                    KeyboardButtonEnum::BALANCE->toButton(),
                    KeyboardButtonEnum::HELP->toButton(),
                ],
            ],
        ];
    }

    public function getSearchWithReportKeyboard(): array
    {
        return [
            'inline_keyboard' => [
                [
                    ['text' => '🔍 Search Again', 'callback_data' => 'search'],
                    ['text' => '📝 Report Last Chat', 'callback_data' => 'report_last'],
                ],
                [
                    ['text' => '⭐ Rate Last Chat', 'callback_data' => 'rate_last'],
                    ['text' => '⚙️ Settings', 'callback_data' => 'settings'],
                ],
            ],
        ];
    }

    public function getSafeModeKeyboard(array $translations = []): array
    {
        return [
            'inline_keyboard' => [
                [['text' => $translations['btn.ask_enable_media'] ?? '📸 Enable Media', 'callback_data' => 'enable_media']],
            ],
        ];
    }

    public function getConfirmationKeyboardEnableMedia(array $translations = []): array
    {
        return [
            'inline_keyboard' => [
                [['text' => $translations['btn.confirm_enable_media'] ?? '✅ Confirm Enable Media', 'callback_data' => 'enable_media_confirm']],
                [['text' => $translations['btn.activate_unsafe'] ?? '⚠️ Activate Unsafe', 'callback_data' => 'toggle_safe_mode']],
            ],
        ];
    }

    public function getSafeModeToggleKeyboard(array $translations, string $type): array
    {
        $buttonName = $type === 'SAFE' ? 'btn.activate_unsafe' : 'btn.activate_safe';
        $buttonText = $translations[$buttonName] ?? ($type === 'SAFE' ? '⚠️ Activate Unsafe' : '🛡️ Activate Safe');

        return [
            'inline_keyboard' => [
                [['text' => $buttonText, 'callback_data' => 'toggle_safe_mode']],
            ],
        ];
    }

    public function getNextSearchKeyboard(): array
    {
        return [
            'inline_keyboard' => [
                [
                    ['text' => '⏭️ Next', 'callback_data' => 'next'],
                    ['text' => '⏹️ Stop', 'callback_data' => 'stop'],
                ],
                [
                    ['text' => '🎁 Send Gift', 'callback_data' => 'send_gift'],
                    ['text' => '📝 Report', 'callback_data' => 'report'],
                ],
            ],
        ];
    }

    public function getBannedMessageKeyboard(array $translations, array $user): array
    {
        $type = 'btn.unban_low';
        if (isset($user['banType']) && $user['banType'] === BanExplicitTypeEnum::EXPLICIT->value) {
            $type = 'btn.unban_high';
        }

        return [
            'inline_keyboard' => [
                [['text' => $translations[$type] ?? '🔓 Unban', 'callback_data' => 'self-unban']],
                [['text' => $translations['btn.ban_reason'] ?? '❓ Ban Reason', 'callback_data' => 'ban-reason']],
            ],
        ];
    }

    public function getLanguageKeyboard(array $translations = []): array
    {
        return [
            'inline_keyboard' => [
                [
                    [
                        'text' => $translations['language.english'] ?? KeyboardButtonEnum::LANGUAGE_ENGLISH->getText(),
                        'callback_data' => KeyboardButtonEnum::LANGUAGE_ENGLISH->getCallbackData(),
                    ],
                    [
                        'text' => $translations['language.indonesia'] ?? KeyboardButtonEnum::LANGUAGE_INDONESIA->getText(),
                        'callback_data' => KeyboardButtonEnum::LANGUAGE_INDONESIA->getCallbackData(),
                    ],
                ],
                [
                    [
                        'text' => $translations['language.malaysia'] ?? KeyboardButtonEnum::LANGUAGE_MALAYSIA->getText(),
                        'callback_data' => KeyboardButtonEnum::LANGUAGE_MALAYSIA->getCallbackData(),
                    ],
                    [
                        'text' => $translations['language.hindi'] ?? KeyboardButtonEnum::LANGUAGE_HINDI->getText(),
                        'callback_data' => KeyboardButtonEnum::LANGUAGE_HINDI->getCallbackData(),
                    ],
                ],
                [
                    [
                        'text' => $translations['language.contribute'] ?? KeyboardButtonEnum::LANGUAGE_CONTRIBUTE->getText(),
                        'callback_data' => KeyboardButtonEnum::LANGUAGE_CONTRIBUTE->getCallbackData(),
                    ],
                ],
            ],
        ];
    }

    public function getBanMediaActionKeyboard(): array
    {
        return [
            'inline_keyboard' => [
                [
                    ['text' => '🚫 Ban', 'callback_data' => 'ban-media'],
                    ['text' => '❌ Reject', 'callback_data' => 'reject-media'],
                ],
            ],
        ];
    }

    public function getBanTextActionKeyboard(): array
    {
        return [
            'inline_keyboard' => [
                [
                    ['text' => '🚫 Ban', 'callback_data' => 'ban-text'],
                    ['text' => '❌ Reject', 'callback_data' => 'reject-text'],
                ],
            ],
        ];
    }

    public function getStopPendingPairKeyboard(array $translations = []): array
    {
        return [
            'inline_keyboard' => [
                [['text' => $translations['btn.stop'] ?? '⏹️ Stop', 'callback_data' => 'stop']],
                [
                    ['text' => $translations['btn.donation'] ?? '💰 Donation', 'callback_data' => 'donasi'],
                    ['text' => $translations['btn.priority_search'] ?? '🚀 Priority Search', 'callback_data' => 'priority-search'],
                ],
                [['text' => $translations['btn.check_queue'] ?? '📊 Check Queue', 'callback_data' => 'pending']],
            ],
        ];
    }

    public function getCaptchaKeyboard(array $translations, string $code): array
    {
        $keyboard = [];
        $random = CodeHelper::createRandomNumber(0, 4);

        $row = [];
        for ($i = 0; $i < 5; $i++) {
            $isCaptcha = $i === $random;
            $text = $isCaptcha ? $code : CodeHelper::createRandomText(5);
            $row[] = ['text' => $text, 'callback_data' => $isCaptcha ? 'captcha' : 'captcha-false'];
        }
        $keyboard[] = $row;

        return ['inline_keyboard' => $keyboard];
    }

    public function getReportReasonKeyboard(array $translations = []): array
    {
        return [
            'inline_keyboard' => [
                [['text' => $translations['btn.porn'] ?? '🔞 Porn', 'callback_data' => 'report-action-porn']],
                [['text' => $translations['btn.ads'] ?? '📢 Ads', 'callback_data' => 'report-action-ads']],
                [['text' => $translations['btn.cancel'] ?? '❌ Cancel', 'callback_data' => 'report-action-cancel']],
            ],
        ];
    }

    public function getSettingsKeyboard(): array
    {
        return [
            'inline_keyboard' => [
                [
                    KeyboardButtonEnum::SETTINGS_PROFILE->toButton(),
                    KeyboardButtonEnum::SETTINGS_LANGUAGE->toButton(),
                ],
                [
                    KeyboardButtonEnum::SETTINGS_PRIVACY->toButton(),
                    KeyboardButtonEnum::SETTINGS_PREFERENCES->toButton(),
                ],
                [
                    KeyboardButtonEnum::MENU_MAIN->toButton(),
                ],
            ],
        ];
    }

    public function getCancelKeyboard(array $translations = []): array
    {
        return [
            'inline_keyboard' => [
                [['text' => $translations['btn.cancel'] ?? '❌ Cancel', 'callback_data' => 'cancel']],
            ],
        ];
    }

    public function getConversationKeyboard(): array
    {
        return [
            'inline_keyboard' => [
                [
                    ['text' => '⏭️ Next', 'callback_data' => 'next'],
                    ['text' => '⏹️ Stop', 'callback_data' => 'stop'],
                ],
                [
                    ['text' => '📝 Report', 'callback_data' => 'report'],
                    ['text' => '⚙️ Settings', 'callback_data' => 'settings'],
                ],
            ],
        ];
    }

    public function getSearchingKeyboard(): array
    {
        return [
            'inline_keyboard' => [
                [
                    ['text' => '⏹️ Stop Search', 'callback_data' => 'stop'],
                    ['text' => '📊 Queue Status', 'callback_data' => 'queue_status'],
                ],
                [
                    ['text' => '⚙️ Settings', 'callback_data' => 'settings'],
                    ['text' => '📞 Help', 'callback_data' => 'help'],
                ],
            ],
        ];
    }

    public function getRetryKeyboard(): array
    {
        return [
            'inline_keyboard' => [
                [
                    ['text' => '🔄 Try Again', 'callback_data' => 'retry'],
                    ['text' => '⚙️ Settings', 'callback_data' => 'settings'],
                ],
                [
                    ['text' => '💰 Balance', 'callback_data' => 'balance'],
                    ['text' => '📞 Help', 'callback_data' => 'help'],
                ],
            ],
        ];
    }

    public function getRatingKeyboard(): array
    {
        return [
            'inline_keyboard' => [
                [
                    KeyboardButtonEnum::RATING_1->toButton(),
                    KeyboardButtonEnum::RATING_2->toButton(),
                    KeyboardButtonEnum::RATING_3->toButton(),
                ],
                [
                    KeyboardButtonEnum::RATING_4->toButton(),
                    KeyboardButtonEnum::RATING_5->toButton(),
                ],
                [
                    KeyboardButtonEnum::RATING_SKIP->toButton(),
                ],
            ],
        ];
    }

    public function getReportKeyboard(): array
    {
        return [
            'inline_keyboard' => [
                [
                    KeyboardButtonEnum::REPORT_INAPPROPRIATE->toButton(),
                    KeyboardButtonEnum::REPORT_SPAM->toButton(),
                ],
                [
                    KeyboardButtonEnum::REPORT_HARASSMENT->toButton(),
                ],
                [
                    KeyboardButtonEnum::REPORT_OTHER->toButton(),
                    KeyboardButtonEnum::REPORT_CANCEL->toButton(),
                ],
            ],
        ];
    }

    /**
     * Helper method to create single button keyboard
     */
    public function createSingleButtonKeyboard(KeyboardButtonEnum $button): array
    {
        return [
            'inline_keyboard' => [
                [$button->toButton()],
            ],
        ];
    }

    /**
     * Helper method to create horizontal button row keyboard
     */
    public function createHorizontalKeyboard(KeyboardButtonEnum ...$buttons): array
    {
        return [
            'inline_keyboard' => [
                array_map(fn ($button) => $button->toButton(), $buttons),
            ],
        ];
    }

    /**
     * Helper method to create vertical button keyboard
     */
    public function createVerticalKeyboard(KeyboardButtonEnum ...$buttons): array
    {
        return [
            'inline_keyboard' => array_map(
                fn ($button) => [$button->toButton()],
                $buttons
            ),
        ];
    }
}
