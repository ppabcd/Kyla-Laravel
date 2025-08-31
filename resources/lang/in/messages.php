<?php

return [
    'donation' => [
        'message' => 'Namaste :name! Aap is bot ke vikas mein keval Rp1000 se yogdaan dekar madad kar sakte hain! Har Rp1000 ke daan ko hum 10 💎 mein parivartit kar denge jise aap is bot mein uplabdh kuchh premium suvidhaon ke liye upyog kar sakte hain. Daan karke, aap is bot ki gunvatta mein sudhaar ke liye developer ki madad kar rahe hain. Aap neeche diye gaye button ka upyog karke Arxist aur Crypto Currency ke madhyam se daan kar sakte hain. Dhanyavaad',
        'crypto' => 'Crypto currency ke saath daan karen. Kripya daan bhejne ke baad @KylaSupportBot ko nimnlikhit praroop mein apna len-den pushthi karen.

Network: <network>
Address: <address>
Amount: <amount>
TXID: <txid>

Kripya <network> ko chune gaye jaal se, <address> ko aapka pata, <amount> ko daan ki raashi jaise ki 0.01BNB ya 0.01$BNB aur <txid> ko len-den ke TXID se badlein.

Hum galat daan praroop ya pate aur jaal ki galatiyon ke karan hone wale nuksan ya fund ke kho jane ki jimmedari nahi lete.
Kripya daan bhejne se pehle pata aur jaal ko jaanch lein.
Dhanyavaad',
    ],

    'rules' => 'Kyla Chat mein aapka swagat hai. Aap /interest mein chune gaye apne ichhaanusar doston ko khoj sakte hain.

Is bot ke kuch niyam hain jo aapko paalan karne honge:
- Kisi bhi prakar ka prachar karna mana hai
- Ashlil ya pornografik samagri post karna ya bhejna mana hai.
- Bot par spam karna mana hai

Yadi aap in niyamon ka ullanghan karte hain, to hum aapko prati-bandhit kar sakte hain aur ullanghan ke aadhar par unban karne ke liye jurmana lagaya ja sakta hai.
Dhanyavaad aur Kyla Chat ka aanand uthayein',

    'interest' => [
        'not_set' => '💬 Aap kis se baat karna chahte hain?',
        'warning' => 'Aapne abhi purush ya mahila dono ke sath ruchi ka chayan kiya hai. Is prakar ki ruchi se khojne mein samay lag sakta hai. Aap apni ruchi ko sabhi ke liye badal kar jaldi dost khoj sakte hain.
Ruchi badalne ke liye /interest likhe',
        'set' => 'Ruchi safaltapurvak badal di gayi hai.
Aap ise phir se /interest likhkar badal sakte hain',
    ],

    'gender' => [
        'not_set' => '👤 Aapka ling kya hai?',
        'set_basic' => 'Aapka ling safaltapurvak badal diya gaya hai.',
        'set' => 'Aapka ling safaltapurvak badal diya gaya hai. Pehle yukti ke anusaar sahi dost chunne ki koshish karen.
Yadi aap keval vipreet ling se baat karna chahte hain to bayi taraf chune.
Yadi aap sabhi se baat karna chahte hain to daayi taraf chune.',
        'set_without_interest' => 'Aapka ling safaltapurvak badal diya gaya hai.',
    ],

    'btn' => [
        'gender_male' => '👦',
        'gender_female' => '👧',
        'gender_all' => '👦👧',
        'cross_gender' => '👦 Lintas Ling 👧',
        'search' => '🔍 Khoj',
        'confirm_enable_media' => '📷 Media Saksham Karein',
        'ask_enable_media' => '📷 Media Saksham Karne ke Liye Puchhein',
        'report' => '🚩 Report Karein',
        'porn' => '🔞 Ashleelta',
        'ads' => '📰 Vigyapan',
        'cancel' => 'Radd Karein',
        'stop' => '❌ Khojna Band Karein',
        'stop_dialog' => '❌ Varta Band Karein',
        'stop_simple' => '❌ Rokna',
        'next_simple' => '⏩ Agla',
        'priority_search' => 'Prathamikta se khoj (💎10)',
        'cross_gender_search' => '👦 Lintas Ling Khoj 👧',
        'interest_change' => '👦 Ruchi Badlein 👧',
        'gender_change' => '👱 Ling Badlein 👱‍♀️',
        'activate_safe' => '🔒 Surakshit Mode Saksham Karein',
        'activate_unsafe' => '🔓 Surakshit Mode Nishkriya Karein',
        'check_queue' => '🕑 Katara Jaanchen',
        'unban' => 'Mera Account Unban Karein',
        'unban_high' => 'Mera Account Unban Karein (💎100)',
        'unban_low' => 'Mera Account Unban Karein (💎50)',
        'ban_reason' => '📝 Ban Karne Ka Karan',
        'pay_arxist' => '💰 Arxist Ke Saath Bhugtaan Karein',
        'ask_enable_media' => '📷 Media Saksham Karne Ke Liye Puchhein',
        'enable_media' => '📷 Media Saksham Karein',
        'donation' => 'Daan 💎',
        'official_group' => '💬 Official Samuh',
        'playground' => '🎲 Playground',
        'fess' => '💬 KylFess',
        'announcement' => '📢 Ghoshna',
        'contribute_language' => '🌐 Bhasha Mein Yogdaan Karein',
    ],

    'help' => 'Agar koi samasya hai to kripya is ID ko @KylaSupportBot par likhen aur karan batayein.
ID: `:id`',

    'pair' => [
        'created' => 'Saathi mil gaya :genderIcon
/next - Naye saathi ki khoj karein
/stop - Baatcheet ko rokein

⚠️ Chat mein yaun samagri bhejna mana hai',
        'deleted' => 'Baatcheet samapt ho gayi hai
/search likh kar doosre baatcheet shuru karein',
        'inactive' => 'Aapka saathi nishkriya lag raha hai. Aap /next likhkar naya saathi khoj sakte hain ya pratiksha kar sakte hain.',
        'exists' => 'Aap vartamaan mein baatcheet mein hain',
    ],

    'pending_pair' => [
        'created' => 'Naye saathi ki khoj jaari hai
/stop likh kar khojna rokein',
        'exists' => 'Naye saathi ki khoj jaari hai
/stop likh kar khojna rokein',
        'deleted' => 'Khoj radd kar di gayi hai',
    ],

    'balance' => [
        'amount' => 'Aapke paas :balance 💎 hain',
        'insufficient_balance' => 'Aapke paas paryapt 💎 nahi hain',
    ],

    'safe_mode' => [
        'message' => 'Aap :mode mode mein hain. Surakshit mode ko saksham karne se aapko pornografi samagri prapt hone ki sambhavna kam ho jayegi. Lekin, yeh mode aapko 100% suraksha nahi deta hai.
Agar aapko lagta hai ki aapka samvad pornografi ki disha mein nahi ja raha hai, to aap /mode mein jaakar ashtayi roop se surakshit mode ko nishkriya kar sakte hain aur apne saathi se media fir se bhejne ko keh sakte hain.',
        'unsafe' => 'Asurakshit',
        'safe' => 'Surakshit',
        'restricted' => 'Aapka saathi vartamaan mein surakshit mode mein hai. Aap photo, sticker, aur video nahi bhej sakte jab tak ki aapka saathi ise anumati na de ya surakshit mode ko nishkriya na kare.',
        'enable_media' => 'Aapka saathi media prapti ko saksham karne ka anurodh kar raha hai. Kripya neeche diye gaye button ka upyog karke ise saksham karein aur apne saathi se media fir se bhejne ko keh sakte hain.',
        'change_success' => 'Mode safaltapurvak badal diya gaya hai',
        'enable_media_confirmed' => 'Aap ab media bhej sakte hain',
    ],

    'conversation' => [
        'not_exists' => 'Baatcheet mein nahi hain
/search likh kar naye saathi ki khoj karein',
        'locked' => 'Kripya doosra sandesh bhejne se pehle pratiksha karein.',
        'start' => 'Saathi ki khoj ke liye /search likhein',
        'priority_search' => 'Aap prathamikta khoj mode ka upyog kar rahe hain. Kripya khojna na rokein ya aap apni khoj prathamikta kho denge.',
    ],

    'banned' => [
        'message' => 'Aap spam/pornografi/vigyaapan bhejne ke karan pratibandhit kiye gaye hain',
    ],

    'soft_banned' => [
        'message' => 'Aap spam/pornografi/vigyaapan bhejne ke karan :durationInMinutes minute ke liye pratibandhit kiye gaye hain.',
    ],

    'request' => [
        'sent_to_partner' => 'Aapka anurodh aapke saathi ko bhej diya gaya hai.',
    ],

    'language' => [
        'selector' => 'Aap jo bhasha chahate hain chunav karein',
        'indonesia' => '🇮🇩',
        'english' => '🇬🇧',
        'malaysia' => '🇲🇾',
        'hindi' => '🇮🇳',
        'changed' => 'Bhaasha badalakar hindee kar dee gaee hai',
        'contribute' => '🌐 Bhasha uplabdh nahi hai?',
        'contribute_text' => 'Aaiye humari madad karein is bot ko anya bhashaon mein anuvadit karne mein. Hum aapki madad ki aavashyakta hai taki yeh bot duniya bhar ke logon dwara upyog kiya ja sake.
Aap [Github](https://github.com/Kyla-Chat/Kyla-Translations) par jaakar humari anuvad mein sahayata kar sakte hain.',
    ],

    'captcha' => [
        'message' => 'Kripya captcha se sahi text neeche diye gaye button ka upyog karke chunen',
        'success' => 'Captcha safaltapurvak paarit kiya gaya',
        'failed' => 'Captcha mein asafalta',
    ],

    'report' => [
        'reason' => 'Is upyogkarta ko report karne ka karan chunav karein',
        'cancel' => 'Report radd ki gayi',
        'expired' => 'Baatcheet samapt hone ke 2 ghante ke andar hi aap is upyogkarta ko report kar sakte hain.',
        'thank_you' => 'Aapki report ke liye dhanyavaad. Hum ise shighr hi samiksha karenge.',
    ],

    'unban' => [
        'success' => 'Aapka account safaltapurvak unban kar diya gaya hai.',
    ],

    'group' => [
        'join_invitation' => '🙈 Hamare samuh mein shaamil ho jaayein',
    ],

    'user' => [
        'inactive' => 'Hi :name, Kyla aapko yaad kar rahi hai kyunki aap kafi samay se online nahi aaye 😞.
Aap aakhiri baar online aaye the :month mahine pehle.
Naye doston ki khoj karein kyunki ve aapka intezar kar rahe hain.',
    ],

    'location' => [
        'ask' => 'Krpaya apana sthaan bhejen aur apane saathee se dooree gyaat karen. Sthaan anurodh radd karane ke lie /cancel karen dabaen.',
        'ask_city' => 'Apane saathee ko apana sthaan bataane ke lie krpaya apane shahar ka naam bhejen',
        'cancel' => 'Sthaan anurodh radd karane ke lie neeche diya gaya batan dabaen',
        'cancelled' => 'Sthaan anurodh radd kar diya gaya hai',
        'confirm' => 'Sthaan ko :city par set kar diya gaya hai',
        'error' => 'Sthaan nahin mila',
        'invalid' => 'Mujhe yah samajh nahin aaya. krpaya shahar ka naam darj karen ya apana sthaan saajha karen.',
    ],

    'subscribe' => [
        'announcement' => 'Is bot ka upyog karne se pehle, aapko pehle channel ko subscribe karna hoga. Subscribe karke, aapko bot ke sabhi visheshataon tak poorn pahunch mil jayegi. Hum niyamit roop se channel par bot ki sthiti aur naye karyakshamataon ke baare mein updates aur ghoshnaein pradaan karte hain, isliye subscribe karna sunishchit karta hai ki aap suchit rahein.',
    ],

    'queue' => [
        'long_queue' => 'Vartamaan mein kataar vyast hai. Kripaya kuch samay pratiksha karein.',
        'check_queue' => 'Kripaya thoda intezaar karein, hum kataar ki jaanch kar rahe hain.',
        'overcrowded_message' => '🚦 Kataar vartamaan mein :count upyogkartaon ke saath vyast hai!\n\n⚖️ Ling santulan abhi uchit nahi hai.\n\n🎲 Aap tez matching ke liye kisi bhi ling ke saath chat karne ka vikalp chun sakte hain, ya phir apne pasandida ling ka intezaar kar sakte hain.',
        'continue_waiting' => '⏳ Aapne apne pasandida ling ka intezaar karne ka chayan kiya hai. Jab match mil jayega to hum aapko suchit kar denge.',
    ],
];
