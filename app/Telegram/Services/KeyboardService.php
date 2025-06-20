<?php

namespace App\Telegram\Services;

use App\Services\ArxistService;
use App\Enums\GenderEnum;
use App\Enums\BanExplicitTypeEnum;
use App\Helpers\CodeHelper;
use App\Enums\InterestEnum;

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
                    ['text' => '💰 Arxist', 'url' => $url]
                ],
                [
                    ['text' => '₿ Cryptocurrencies ⟠', 'callback_data' => 'crypto-donation']
                ]
            ]
        ];
    }

    public function getDonationKeyboard(array $translations = []): array
    {
        return [
            'inline_keyboard' => [
                [['text' => $translations['btn.donation'] ?? '💰 Donation', 'callback_data' => 'donasi']],
                [['text' => $translations['btn.top_up'] ?? '💎 Top Up', 'callback_data' => 'topup']],
                [['text' => $translations['btn.priority_search'] ?? '🚀 Priority Search', 'callback_data' => 'priority-search']]
            ]
        ];
    }

    public function getChangeToAllGenderKeyboard(array $translations = []): array
    {
        return [
            'inline_keyboard' => [
                [['text' => $translations['btn.search_general_gender'] ?? '🔍 Search All', 'callback_data' => 'general-search']]
            ]
        ];
    }

    public function getGenderKeyboard(): array
    {
        return [
            'inline_keyboard' => [
                [
                    ['text' => '♂️ Male', 'callback_data' => 'gender:' . GenderEnum::MALE->value],
                    ['text' => '♀️ Female', 'callback_data' => 'gender:' . GenderEnum::FEMALE->value]
                ]
            ]
        ];
    }

    public function getInterestKeyboard(): array
    {
        return [
            'inline_keyboard' => [
                [
                    ['text' => '♂️ Interested in Male', 'callback_data' => 'interest:' . InterestEnum::MALE->value],
                    ['text' => '♀️ Interested in Female', 'callback_data' => 'interest:' . InterestEnum::FEMALE->value]
                ],
                [
                    ['text' => '👥 Interested in All', 'callback_data' => 'interest:' . InterestEnum::ALL->value]
                ]
            ]
        ];
    }

    public function getInterestAllKeyboard(array $translations = []): array
    {
        return [
            'inline_keyboard' => [
                [['text' => $translations['btn.cross_gender'] ?? '👥 Cross Gender', 'callback_data' => 'change_interest_all']],
                [['text' => $translations['btn.location'] ?? '📍 Location', 'callback_data' => 'location']]
            ]
        ];
    }

    public function getSearchKeyboard(): array
    {
        return [
            'keyboard' => [
                [
                    ['text' => '🔍 Search'],
                    ['text' => '⚙️ Settings']
                ],
                [
                    ['text' => '💰 Balance'],
                    ['text' => '📞 Help']
                ]
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ];
    }

    public function getSearchWithReportKeyboard(): array
    {
        return [
            'keyboard' => [
                [
                    ['text' => '🔍 Search Again'],
                    ['text' => '📝 Report Last Chat']
                ],
                [
                    ['text' => '⭐ Rate Last Chat'],
                    ['text' => '⚙️ Settings']
                ]
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ];
    }

    public function getSafeModeKeyboard(array $translations = []): array
    {
        return [
            'inline_keyboard' => [
                [['text' => $translations['btn.ask_enable_media'] ?? '📸 Enable Media', 'callback_data' => 'enable_media']]
            ]
        ];
    }

    public function getConfirmationKeyboardEnableMedia(array $translations = []): array
    {
        return [
            'inline_keyboard' => [
                [['text' => $translations['btn.confirm_enable_media'] ?? '✅ Confirm Enable Media', 'callback_data' => 'enable_media_confirm']],
                [['text' => $translations['btn.activate_unsafe'] ?? '⚠️ Activate Unsafe', 'callback_data' => 'toggle_safe_mode']]
            ]
        ];
    }

    public function getSafeModeToggleKeyboard(array $translations, string $type): array
    {
        $buttonName = $type === 'SAFE' ? 'btn.activate_unsafe' : 'btn.activate_safe';
        $buttonText = $translations[$buttonName] ?? ($type === 'SAFE' ? '⚠️ Activate Unsafe' : '🛡️ Activate Safe');

        return [
            'inline_keyboard' => [
                [['text' => $buttonText, 'callback_data' => 'toggle_safe_mode']]
            ]
        ];
    }

    public function getNextSearchKeyboard(): array
    {
        return [
            'keyboard' => [
                [
                    ['text' => '⏭️ Next'],
                    ['text' => '⏹️ Stop']
                ],
                [
                    ['text' => '🎁 Send Gift'],
                    ['text' => '📝 Report']
                ]
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => false
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
                [['text' => $translations['btn.ban_reason'] ?? '❓ Ban Reason', 'callback_data' => 'ban-reason']]
            ]
        ];
    }

    public function getLanguageKeyboard(array $translations = []): array
    {
        return [
            'inline_keyboard' => [
                [
                    ['text' => $translations['language.english'] ?? '🇺🇸 English', 'callback_data' => 'lang-en'],
                    ['text' => $translations['language.indonesia'] ?? '🇮🇩 Indonesia', 'callback_data' => 'lang-id']
                ],
                [
                    ['text' => $translations['language.malaysia'] ?? '🇲🇾 Malaysia', 'callback_data' => 'lang-my'],
                    ['text' => $translations['language.hindi'] ?? '🇮🇳 Hindi', 'callback_data' => 'lang-in']
                ],
                [['text' => $translations['language.contribute'] ?? '🤝 Contribute', 'callback_data' => 'lang-contribute']]
            ]
        ];
    }

    public function getBanMediaActionKeyboard(): array
    {
        return [
            'inline_keyboard' => [
                [
                    ['text' => '🚫 Ban', 'callback_data' => 'ban-media'],
                    ['text' => '❌ Reject', 'callback_data' => 'reject-media']
                ]
            ]
        ];
    }

    public function getBanTextActionKeyboard(): array
    {
        return [
            'inline_keyboard' => [
                [
                    ['text' => '🚫 Ban', 'callback_data' => 'ban-text'],
                    ['text' => '❌ Reject', 'callback_data' => 'reject-text']
                ]
            ]
        ];
    }

    public function getStopPendingPairKeyboard(array $translations = []): array
    {
        return [
            'inline_keyboard' => [
                [['text' => $translations['btn.stop'] ?? '⏹️ Stop', 'callback_data' => 'stop']],
                [
                    ['text' => $translations['btn.donation'] ?? '💰 Donation', 'callback_data' => 'donasi'],
                    ['text' => $translations['btn.priority_search'] ?? '🚀 Priority Search', 'callback_data' => 'priority-search']
                ],
                [['text' => $translations['btn.check_queue'] ?? '📊 Check Queue', 'callback_data' => 'pending']]
            ]
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
                [['text' => $translations['btn.cancel'] ?? '❌ Cancel', 'callback_data' => 'report-action-cancel']]
            ]
        ];
    }

    public function getSettingsKeyboard(): array
    {
        return [
            'inline_keyboard' => [
                [
                    ['text' => '👤 Profile', 'callback_data' => 'settings:profile'],
                    ['text' => '🌐 Language', 'callback_data' => 'settings:language']
                ],
                [
                    ['text' => '🔒 Privacy', 'callback_data' => 'settings:privacy'],
                    ['text' => '🔧 Preferences', 'callback_data' => 'settings:preferences']
                ],
                [
                    ['text' => '🔙 Back to Menu', 'callback_data' => 'menu:main']
                ]
            ]
        ];
    }

    public function getCancelKeyboard(array $translations = []): array
    {
        return [
            'inline_keyboard' => [
                [['text' => $translations['btn.cancel'] ?? '❌ Cancel', 'callback_data' => 'cancel']]
            ]
        ];
    }

    public function getConversationKeyboard(): array
    {
        return [
            'keyboard' => [
                [
                    ['text' => '⏭️ Next'],
                    ['text' => '⏹️ Stop']
                ],
                [
                    ['text' => '📝 Report'],
                    ['text' => '⚙️ Settings']
                ]
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ];
    }

    public function getSearchingKeyboard(): array
    {
        return [
            'keyboard' => [
                [
                    ['text' => '⏹️ Stop Search'],
                    ['text' => '📊 Queue Status']
                ],
                [
                    ['text' => '⚙️ Settings'],
                    ['text' => '📞 Help']
                ]
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ];
    }

    public function getRetryKeyboard(): array
    {
        return [
            'keyboard' => [
                [
                    ['text' => '🔄 Try Again'],
                    ['text' => '⚙️ Settings']
                ],
                [
                    ['text' => '💰 Balance'],
                    ['text' => '📞 Help']
                ]
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ];
    }

    public function getRatingKeyboard(): array
    {
        return [
            'inline_keyboard' => [
                [
                    ['text' => '⭐', 'callback_data' => 'rating:1'],
                    ['text' => '⭐⭐', 'callback_data' => 'rating:2'],
                    ['text' => '⭐⭐⭐', 'callback_data' => 'rating:3']
                ],
                [
                    ['text' => '⭐⭐⭐⭐', 'callback_data' => 'rating:4'],
                    ['text' => '⭐⭐⭐⭐⭐', 'callback_data' => 'rating:5']
                ],
                [
                    ['text' => '⏭️ Skip Rating', 'callback_data' => 'rating:skip']
                ]
            ]
        ];
    }

    public function getReportKeyboard(): array
    {
        return [
            'inline_keyboard' => [
                [
                    ['text' => '🔞 Inappropriate Content', 'callback_data' => 'report:inappropriate'],
                    ['text' => '🤖 Spam/Bot', 'callback_data' => 'report:spam']
                ],
                [
                    ['text' => '😡 Harassment', 'callback_data' => 'report:harassment'],
                    ['text' => '💔 Fake Profile', 'callback_data' => 'report:fake']
                ],
                [
                    ['text' => '🚫 Other', 'callback_data' => 'report:other'],
                    ['text' => '❌ Cancel', 'callback_data' => 'report:cancel']
                ]
            ]
        ];
    }
}
