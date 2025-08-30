<?php

namespace App\Enums;

enum KeyboardButtonEnum: string
{
    // Gender buttons
    case GENDER_MALE = 'gender-male';
    case GENDER_FEMALE = 'gender-female';

    // Interest buttons
    case INTEREST_MALE = 'interest-male';
    case INTEREST_FEMALE = 'interest-female';
    case INTEREST_ALL = 'interest-all';

    // Language buttons
    case LANGUAGE_ENGLISH = 'lang-en';
    case LANGUAGE_INDONESIA = 'lang-id';
    case LANGUAGE_MALAYSIA = 'lang-my';
    case LANGUAGE_HINDI = 'lang-in';
    case LANGUAGE_CONTRIBUTE = 'lang-contribute';

    // Main action buttons
    case SEARCH = 'search';
    case START_SEARCH = 'start_search';
    case STOP = 'stop';
    case NEXT = 'next';
    case SETTINGS = 'settings';
    case HELP = 'help';
    case BALANCE = 'balance';
    case REPORT = 'report';
    case REPORT_LAST = 'report_last';
    case RATE_LAST = 'rate_last';

    // Settings buttons
    case SETTINGS_PROFILE = 'settings:profile';
    case SETTINGS_LANGUAGE = 'settings:language';
    case SETTINGS_PRIVACY = 'settings:privacy';
    case SETTINGS_PREFERENCES = 'settings:preferences';
    case MENU_MAIN = 'menu:main';

    // Report buttons
    case REPORT_INAPPROPRIATE = 'report:inappropriate';
    case REPORT_SPAM = 'report:spam';
    case REPORT_HARASSMENT = 'report:harassment';
    case REPORT_OTHER = 'report:other';
    case REPORT_CANCEL = 'report:cancel';
    case REPORT_ACTION_PORN = 'report-action-porn';
    case REPORT_ACTION_ADS = 'report-action-ads';
    case REPORT_ACTION_CANCEL = 'report-action-cancel';

    // Rating buttons
    case RATING_1 = 'rating:1';
    case RATING_2 = 'rating:2';
    case RATING_3 = 'rating:3';
    case RATING_4 = 'rating:4';
    case RATING_5 = 'rating:5';
    case RATING_SKIP = 'rating:skip';

    // Donation and payment buttons
    case DONATION = 'donasi';
    case TOPUP = 'topup';
    case CRYPTO_DONATION = 'crypto-donation';
    case PRIORITY_SEARCH = 'priority-search';

    // Safety and moderation buttons
    case ENABLE_MEDIA = 'enable_media';
    case ENABLE_MEDIA_CONFIRM = 'enable_media_confirm';
    case TOGGLE_SAFE_MODE = 'toggle_safe_mode';
    case BAN_MEDIA = 'ban-media';
    case BAN_TEXT = 'ban-text';
    case REJECT_MEDIA = 'reject-media';
    case REJECT_TEXT = 'reject-text';
    case SELF_UNBAN = 'self-unban';
    case BAN_REASON = 'ban-reason';

    // Queue and search status
    case QUEUE_STATUS = 'queue_status';
    case PENDING = 'pending';
    case GENERAL_SEARCH = 'general-search';
    case CHANGE_INTEREST_ALL = 'change_interest_all';

    // Utility buttons
    case CANCEL = 'cancel';
    case RETRY = 'retry';
    case CAPTCHA = 'captcha';
    case CAPTCHA_FALSE = 'captcha-false';
    case SEND_GIFT = 'send_gift';
    case LOCATION = 'location';

    /**
     * Get the display text for the button
     */
    public function getText(): string
    {
        return match ($this) {
            // Gender
            self::GENDER_MALE => '♂️ Male',
            self::GENDER_FEMALE => '♀️ Female',

            // Interest
            self::INTEREST_MALE => '👨 Male',
            self::INTEREST_FEMALE => '👩 Female',
            self::INTEREST_ALL => '🎲 Random',

            // Language
            self::LANGUAGE_ENGLISH => '🇺🇸 English',
            self::LANGUAGE_INDONESIA => '🇮🇩 Indonesia',
            self::LANGUAGE_MALAYSIA => '🇲🇾 Malaysia',
            self::LANGUAGE_HINDI => '🇮🇳 Hindi',
            self::LANGUAGE_CONTRIBUTE => '🤝 Contribute',

            // Main actions
            self::SEARCH => '🔍 Search',
            self::START_SEARCH => '🔍 Search',
            self::STOP => '⏹️ Stop',
            self::NEXT => '⏭️ Next',
            self::SETTINGS => '⚙️ Settings',
            self::HELP => '📞 Help',
            self::BALANCE => '💰 Balance',
            self::REPORT => '📝 Report',
            self::REPORT_LAST => '📝 Report Last Chat',
            self::RATE_LAST => '⭐ Rate Last Chat',

            // Settings
            self::SETTINGS_PROFILE => '👤 Profile',
            self::SETTINGS_LANGUAGE => '🌐 Language',
            self::SETTINGS_PRIVACY => '🔒 Privacy',
            self::SETTINGS_PREFERENCES => '🔧 Preferences',
            self::MENU_MAIN => '🔙 Back to Menu',

            // Report
            self::REPORT_INAPPROPRIATE => '🔞 Inappropriate Content',
            self::REPORT_SPAM => '🤖 Spam/Bot',
            self::REPORT_HARASSMENT => '😡 Harassment',
            self::REPORT_OTHER => '🚫 Other',
            self::REPORT_CANCEL => '❌ Cancel',
            self::REPORT_ACTION_PORN => '🔞 Porn',
            self::REPORT_ACTION_ADS => '📢 Ads',
            self::REPORT_ACTION_CANCEL => '❌ Cancel',

            // Rating
            self::RATING_1 => '⭐',
            self::RATING_2 => '⭐⭐',
            self::RATING_3 => '⭐⭐⭐',
            self::RATING_4 => '⭐⭐⭐⭐',
            self::RATING_5 => '⭐⭐⭐⭐⭐',
            self::RATING_SKIP => '⏭️ Skip Rating',

            // Donation and payment
            self::DONATION => '💰 Donation',
            self::TOPUP => '💎 Top Up',
            self::CRYPTO_DONATION => '₿ Cryptocurrencies ⟠',
            self::PRIORITY_SEARCH => '🚀 Priority Search',

            // Safety and moderation
            self::ENABLE_MEDIA => '📸 Enable Media',
            self::ENABLE_MEDIA_CONFIRM => '✅ Confirm Enable Media',
            self::TOGGLE_SAFE_MODE => '🛡️ Toggle Safe Mode',
            self::BAN_MEDIA => '🚫 Ban',
            self::BAN_TEXT => '🚫 Ban',
            self::REJECT_MEDIA => '❌ Reject',
            self::REJECT_TEXT => '❌ Reject',
            self::SELF_UNBAN => '🔓 Unban',
            self::BAN_REASON => '❓ Ban Reason',

            // Queue and search status
            self::QUEUE_STATUS => '📊 Queue Status',
            self::PENDING => '📊 Check Queue',
            self::GENERAL_SEARCH => '🔍 Search All',
            self::CHANGE_INTEREST_ALL => '👥 Cross Gender',

            // Utility
            self::CANCEL => '❌ Cancel',
            self::RETRY => '🔄 Try Again',
            self::CAPTCHA => 'Captcha',
            self::CAPTCHA_FALSE => 'Wrong',
            self::SEND_GIFT => '🎁 Send Gift',
            self::LOCATION => '📍 Location',
        };
    }

    /**
     * Get callback data string
     */
    public function getCallbackData(): string
    {
        return $this->value;
    }

    /**
     * Create inline keyboard button array
     */
    public function toButton(): array
    {
        return [
            'text' => $this->getText(),
            'callback_data' => $this->getCallbackData(),
        ];
    }

    /**
     * Get button by callback data
     */
    public static function fromCallbackData(string $callbackData): ?self
    {
        return self::tryFrom($callbackData);
    }
}
