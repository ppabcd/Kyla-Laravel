<?php

return [
    'donation' => [
        'message' => 'Hi :name, did you know you could help develop this bot by donating as low as $0.5? Every IDR 1000 we will convert your donation with 10 💎. With your help, we could make this bot more awesome. You can donate using Arxist and Cryptocurrency. You can use the button below to make a donation. Thank you',
        'crypto' => 'Donation with crypto currency. Please confirm your transaction with format below to @KylaSupportBot after you sent the donation.

Network: <network>
Address: <address>
Amount: <amount>
TXID: <txid>

Please change <network> with selected network, <address> with your address, <amount> with amount of donation for example 0.01BNB or 0.01$BNB and <txid> with TXID the transaction

We are not responsible for any loss of funds due to incorrect format of donation or incorrect address and network. Please check the address and network before sending donation.

Thank you',
    ],

    'rules' => 'Welcome to Kyla Chat.
With this bot, you can look for friends based on the interests you\'ve chosen in /interest.

The rules in this bot include the following.
- Promotion and advertisement of any kind and form are prohibited
- Do not post or send anything in the form of sexual nudity or pornography.
- Do not spam on the bot.

The sanctions given are in the form of being banned according to the violation(s) committed.
Thank you',

    'interest' => [
        'not_set' => 'Please pick your interests.
Pick the left side if you only want to chat with the opposite sex.
Pick the right side if you want to chat with all sexes',
        'warning' => 'Picking the opposite sex as an interest may result in a longer search time.
Type /interest to change your interest',
        'set' => 'Interest successfully changed.
You can change it again by typing /interest',
        'warning_referral' => 'You can change your interest to male/female by inviting :numberReferral friends to use this bot.
Type /referral to see the number of friends you have invited.

Please use the link below to invite your friends.
:referralLink',
    ],

    'referral' => [
        'total_referral' => 'You have :totalReferral referral so far 🎉!
Let\'s invite more friends to use this bot using the link below.
:referralLink',
    ],

    'gender' => [
        'not_set' => 'Choose your gender so that others can know it.
(Gender cannot be changed after this process)',
        'set_basic' => 'Gender has been set.',
        'set' => 'Gender has been set. Please pick your interests.
Pick the left side if you only want to chat with the opposite sex.
Pick the right side if you want to chat with all sexes',
        'set_without_interest' => 'Gender has been set.',
    ],

    'btn' => [
        'gender_male' => '👦',
        'gender_female' => '👧',
        'gender_male_locked' => '🔒👦',
        'gender_female_locked' => '🔒👧',
        'gender_all' => '👦👧',
        'cross_gender' => '👦 Cross-Gender 👧',
        'search' => '🔍 Search',
        'confirm_enable_media' => '📷 Enable Media',
        'ask_enable_media' => '📷 Ask to Enable Media',
        'report' => '🚩Report',
        'porn' => '🔞 Pornography',
        'ads' => '📰 Advertising',
        'cancel' => 'Cancel',
        'stop' => '❌ Stop Search',
        'stop_dialog' => '❌ Stop Conversation',
        'stop_simple' => '❌ Stop',
        'next_simple' => '⏩ Next',
        'priority_search' => 'Priority search (💎10)',
        'cross_gender_search' => '👦 Cross-Gender Search 👧',
        'interest_change' => '👦 Change Interest 👧',
        'gender_change' => '👱 Change Gender 👱‍♀️',
        'activate_safe' => '🔒 Enable Safe Mode',
        'activate_unsafe' => '🔓 Disable Safe Mode',
        'check_queue' => '🕑 Check queue',
        'unban' => 'Unban my account',
        'unban_high' => 'Unban my account (💎100)',
        'unban_low' => 'Unban my account (💎50)',
        'ban_reason' => '📝 Ban Reason',
        'pay_arxist' => '💰 Pay with Arxist',
        'ban_action' => '✅ Banned',
        'reject_action' => '❌ Reject',
        'donation' => 'Donation 💎',
        'top_up' => 'Top Up ⭐️',
        'official_group' => '💬 Official Group',
        'playground' => '🎲 Playground',
        'fess' => '💬 KylFess',
        'announcement' => '📢 Announcement',
        'send_location' => '📍 Send Location',
        'location' => '📍 Location',
        'age' => '🎂 Age',
        'contribute_language' => '🌐 Contribute Language',
        'promote_tiktok' => '📸 Share in Tiktok',
        'search_general_gender' => '🔍 Cross-Gender Search 👦👧',
        'retry' => '🔃 Retry',
        'thumbs_up' => '👍',
        'thumbs_down' => '👎',
        'my_rating' => '🌟 My Rating',
        'change_visibility' => '🙈 Change Visibility 🙉',
        'update_picture' => '📸 Update Picture',
        'upload_picture' => '📸 Upload Picture',
        'update_profile' => '👤 Update Profile',
        'visibility_hide_yes' => '🙈 Yes',
        'visibility_hide_no' => '🙉 No',
        'visibility_show_yes' => '🙉 Yes',
        'visibility_show_no' => '🙈 No',
        'all_correct' => '✅ All Correct',
        're_enter_details' => '🔄 Re-enter Details',
        'my_profile' => '👀 My Profile',
        'auto_search' => '🔍 Auto Search',
        'back_to_settings' => '« Back to Settings',
        'back' => '« Back',
        'privacy' => '🔒 Privacy Policy',
    ],

    'help' => 'For assistance, please copy and paste your ID to @KylaSupportBot state your problems clearly.
ID: `:id`',

    'pair' => [
        'created' => 'Partner found :genderIcon
/next - Looking for a new partner
/stop - Stop the chat

⚠️ Prohibited sending sexual content in the chat',
        'deleted' => 'This chat has been concluded
Type /search to look for more chats',
        'exists' => 'You are already in a chat
Type /stop to stop the chat',
    ],

    'pending_pair' => [
        'created' => 'Attempting to look for a new partner
Type /stop to stop looking for more chats',
        'exists' => 'Getting your new partner
Type /stop to stop looking for more chats',
        'deleted' => 'Search has been cancelled
Type /search to look for more chats',
    ],

    'balance' => [
        'amount' => 'You have :balance 💎',
        'insufficient_balance' => 'You don\'t have enough 💎',
    ],

    'safe_mode' => [
        'message' => 'You are currently in :mode mode. Enabling safe mode will minimize you getting pornographic content. However, this mode does not provide 100% protection from this.
If you think your conversation is not directed towards pornography, you can temporarily disable safe mode in /mode and ask your partner to send it back.',
        'unsafe' => 'Unsafe',
        'safe' => 'Safe',
        'restricted' => 'Your partner is currently in safe mode. You cannot send photos, stickers, and videos until your partner deactivates safe mode.',
        'enable_media' => 'Your partner has asked you to enable media reception. Please activate it using the button below and ask your partner to resend the media.',
        'change_success' => 'Successfully change mode',
        'enable_media_confirmed' => 'You\'re able to send media now',
    ],

    'request' => [
        'sent_to_partner' => 'Your request has been sent to your partner.',
    ],

    'conversation' => [
        'not_exists' => 'Not in chat
Type /search to look for more partners',
        'locked' => 'Please wait some time before sending another message.',
        'start' => 'Type /search to look for partners',
        'priority_search' => 'You are currently in priority search mode. Please don\'t stop the search or you will lose your priority search.',
        'general_search' => 'You are currently in cross-gender search mode. Please wait for a partner to be found.',
    ],

    'banned' => [
        'message' => 'You\'ve been blocked for sending spams/pornography/advertising',
    ],

    'soft_banned' => [
        'message' => 'You\'ve been blocked for sending spams/pornography/advertising for :durationInMinutes minutes.',
    ],

    'language' => [
        'selector' => 'Please select your language.',
        'indonesia' => '🇮🇩',
        'english' => '🇬🇧',
        'malaysia' => '🇲🇾',
        'hindi' => '🇮🇳',
        'changed' => 'Language has been changed to English.',
        'contribute' => '🌐 Language not available?',
        'contribute_text' => 'Join us in breaking language barriers! We need your help to expand the reach of our bot by contributing translations. Your expertise can make our tool accessible to users worldwide. Interested? Let\'s make a difference together!
Check out our translation project on [GitHub](https://github.com/Kyla-Chat/Kyla-Translations)',
    ],

    'captcha' => [
        'message' => 'Please select the correct text from the captcha using the button below',
        'success' => 'Captcha successfully passed',
        'failed' => 'Captcha failed',
    ],

    'report' => [
        'reason' => 'Please choose the reason for reporting this user',
        'cancel' => 'Report cancelled',
        'expired' => 'You only have 2 hours to report this user after the conversation has ended.',
        'thank_you' => 'Thank you for your report. We will review it as soon as possible.',
    ],

    'unban' => [
        'success' => 'Your account has been unbanned.',
    ],

    'group' => [
        'join_invitation' => '🙈 Join our group',
    ],

    'user' => [
        'inactive' => 'Hi :name, Kyla miss you because I never see you online 😞.
It already :month months since the last time you online.
Let\'s search new partner because they are waiting for you right now.',
    ],

    'location' => [
        'ask' => 'Please send your location get distance with your partner. Press /cancel to cancel the location request.',
        'ask_match' => 'Send your location to find nearest match',
        'ask_city' => 'Please send your city name to make your partner know your location',
        'ask_button' => 'Please use the button below to send your location',
        'cancel' => 'Press the button below to cancel the location request',
        'cancelled' => 'Location request has been cancelled',
        'confirm' => 'The location has been set to :city',
        'error' => 'Location not found',
        'only_button' => 'Currently, only location with coordinates is supported. Please use the location button.',
        'invalid' => 'I didn\'t understand that. Please enter a city name or share your location.',
        'invalid_location_response' => 'Sorry, I can\'t find your location. Please try again.',
        'invalid_text_location' => 'Only able use location button',
        'success' => 'Your location has been set successfully',
    ],

    'settings' => [
        'title' => '⚙️ Settings',
    ],

    'keyboard' => [
        'cancel' => 'Successfully cancelled',
    ],

    'tiktok' => [
        'promotion' => 'Share your experiences with Kyla Chat in Tiktok
#kylachat
📸😍 https://tiktok.com/tag/kylachat',
    ],

    'announcement' => [
        'notice' => 'This announcement is issued by our bot. For additional information, please click the button below.',
    ],

    'age' => [
        'ask' => 'How old are you? 🤔',
        'invalid' => 'Please enter a valid age 😉
Write /cancel to cancel',
        'confirm' => 'Your age has been set to :age',
        'cancelled' => 'Age request has been cancelled',
    ],

    'ramadhan' => [
        'notice' => 'During ramadhan, you are not able to send an image and videos to prevent any inappropriate content. Please respect the holy month of Ramadhan.
You can still send a sticker and text message.

Thank you for your understanding.',
    ],

    'subscribe' => [
        'announcement' => 'Before using this bot, you must first subscribe to the channel. By subscribing, you will gain full access to the bot\'s features. We regularly provide updates and announcements regarding the bot\'s status and new functionalities on the channel, so subscribing ensures that you stay informed.',
        'done' => 'You have successfully subscribed to the channel. You can now use the bot\'s features.',
    ],

    'queue' => [
        'long_queue' => 'Currently, the queue is busy. Please wait a moment.',
        'check_queue' => 'Please wait a moment, we are checking the queue.',
    ],

    'rating' => [
        'expired' => 'You only have 2 hours to rating this user after the conversation has ended.',
        'thank_you' => 'Thank you for your rating',
        'my_rating' => 'My Rating
:starRating
Rating :rating⭐️/5.00⭐️ from :totalRating ratings',
    ],

    'visibility' => [
        'visible' => 'Currently your profile is visible to others.
Do you want to hide your profile?',
        'hidden' => 'Currently your profile is hidden from others.
Do you want to show your profile?',
        'status' => 'Your profile is now :status',
        'visible_short' => 'visible 🙉',
        'hidden_short' => 'hidden 🙈',
        'username_and_forward' => 'You need to set your username or disable the forward privacy settings to ensure your profile is visible to others.',
    ],

    'match' => [
        'welcome' => 'Welcome to Kyla Match!
Here you can find a partner based on your love profile.',
        'search_ask' => 'What do you think about this partner?',
        'found_match' => 'Someone like your profile!',
        'blocked' => 'Your partner has blocked the bot',
        'found_match_mutual' => 'Congratulations🎉🎉! You both liked each other\'s profile. You can contact your partner :contact',
        'find_partner' => 'Find Partner',
        'feature_not_available' => 'This feature is not available for you at this time',
    ],

    'not_available' => [
        'partner' => 'No partner available for you at this time. Please try again later.',
    ],

    'short' => [
        'like' => '❤️',
        'dislike' => '👎',
    ],

    'match_profile' => [
        'reset' => 'We reset your profile. Please update your profile now.',
    ],

    'onboarding' => [
        'ask_name' => 'What is your name?',
        'ask_age' => 'How old are you?',
    ],
];
