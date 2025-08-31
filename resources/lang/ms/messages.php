<?php

return [
    'donation' => [
        'message' => 'Halo :name! Anda boleh membantu mengembangkan bot ini mulai dari Rp1000 tau! Setiap sumbangan Rp1000 akan kami tukarkan menjadi 10 💎 yang boleh anda gunakan untuk beberapa ciri premium yang ada di bot ini. Dengan menyumbang, anda telah membantu pengembang untuk meningkatkan kualitas bot ini. Anda boleh menyumbang menggunakan Arxist dan Mata Wang Kripto dengan butang di bawah ini. Terima kasih',
        'crypto' => 'Derma dengan mata wang kripto. Sila sahkan transaksi anda dengan format berikut ke @KylaSupportBot selepas anda menghantar sumbangan.
Network: <network>
Address: <address>
Amount: <amount>
TXID: <txid>

Sila ganti <network> dengan rangkaian yang dipilih, <address> dengan alamat anda, <amount> dengan jumlah derma sebagai contoh 0.01BNB atau 0.01$BNB dan <txid> dengan TXID transaksi.

Kami tidak bertanggungjawab atas kerugian atau kehilangan dana kerana format derma atau alamat dan rangkaian yang salah.
Sila semak alamat dan rangkaian sebelum menghantar derma.
Terima kasih.',
    ],

    'rules' => 'Selamat datang ke Kyla Chat.
Di bot ini anda boleh mencari rakan berdasarkan minat yang telah dipilih pada /interest.
Terdapat peraturan yang ada di bot, antaranya yang berikut.
- Tidak ada  promosi dalam bentuk apapun
- Jangan menyiarkan atau menghantar apa-apa dalam bentuk pornografi.
- Jangan spam bot
Sekatan yang diberikan adalah secara banned mengikut pelanggaran yang dilakukan.
Terima kasih',

    'interest' => [
        'not_set' => '💬 Nak berbual dengan siapa?',
        'warning' => 'Anda baru saja memilih ketertarikan dengan gender lelaki atau perempuan. Jenis ketertarikan ini mempunyai kelemahan dalam waktu pencarian. Anda boleh mengubah ketertarikan anda ke semua orang untuk dapat mencari kawan lebih cepat.
Taip /interest untuk mengubah ketertarikan anda.',
        'set' => 'Ketertarikan berhasil diubah.
Anda boleh mengubahnya kembali dengan menaip /interest.',
    ],

    'gender' => [
        'not_set' => '👤 Apa jantina anda?',
        'set_basic' => 'Jantina anda telah berjaya diubah.',
        'set' => 'Jantina anda telah berjaya diubah. Mari pilih teman yang sesuai dengan kriteria kamu.
Pilih di sebelah kiri jika hanya ingin berbicara dengan lawan jenis.
Pilih di sebelah kanan jika ingin berbicara dengan semua orang.',
        'set_without_interest' => 'Jantina anda telah berjaya diubah.',
    ],

    'btn' => [
        'gender_male' => '👦',
        'gender_female' => '👧',
        'gender_all' => '👦👧',
        'cross_gender' => '👦 Lintas Jantina 👧',
        'search' => '🔍 Cari',
        'confirm_enable_media' => '📷 Aktifkan Media',
        'ask_enable_media' => '📷 Mintak Aktif Media',
        'report' => '🚩 Laporkan',
        'porn' => '🔞 Pornografi',
        'ads' => '📰 Iklan',
        'cancel' => 'Batal',
        'stop' => '❌ Hentikan Carian',
        'stop_dialog' => '❌ Hentikan Perbualan',
        'stop_simple' => '❌ Hentikan',
        'next_simple' => '⏩ Next',
        'priority_search' => 'Carian Keutamaan (💎10)',
        'cross_gender_search' => '👦 Carian Lintas Jantina 👧',
        'interest_change' => '👦 Tukar Minat 👧',
        'gender_change' => '👱 Tugas Jantina 👱‍♀️',
        'activate_safe' => '🔒 Aktifkan Mod Selamat',
        'activate_unsafe' => '🔓 Nyahaktif Mod Selamat',
        'check_queue' => '🕑 Semak Giliran',
        'unban' => 'Buka Sekatan Akaun Saya',
        'unban_high' => 'Buka Sekatan Akaun Saya (💎100)',
        'unban_low' => 'Buka Sekatan Akaun Saya (💎50)',
        'ban_reason' => '📝 Sebab Sekatan',
        'pay_arxist' => '💰 Bayar dengan Arxist',
        'ask_enable_media' => '📷 Mintak Aktif Media',
        'enable_media' => '📷 Aktifkan Media',
        'donation' => 'Derma 💎',
        'official_group' => '💬 Kumpulan Rasmi',
        'playground' => '🎲 Medan Permainan',
        'fess' => '💬 KylFess',
        'announcement' => '📢 Pengumuman',
        'contribute_language' => '🌐 Bantu Terjemah',
    ],

    'help' => 'Tuliskan ID ini ke @KylaSupportBot jika anda menghadapi masalah dan sertakan alasannya.
ID: `:id`',

    'pair' => [
        'created' => 'Rakan ditemukan :genderIcon
/next - Cari rakan baru
/stop - Hentikan perbualan

⚠️ Penghantaran kandungan seksual dilarang dalam chat',
        'deleted' => 'Perbualan telah dihentikan
Taip /search untuk memulai obrolan lain',
        'inactive' => 'Rakan anda kelihatan tidak aktif. Anda boleh taip /next untuk mencari rakan baru atau terus menunggu.',
        'exists' => 'Anda sedang berada dalam perbualan',
    ],

    'pending_pair' => [
        'created' => 'Mencuba mencari rakan baru
Taip /stop untuk menghentikan pencarian',
        'exists' => 'Sedang mencari rakan baru
Taip /stop untuk menghentikan pencarian',
        'deleted' => 'Pencarian telah dibatalkan',
    ],

    'balance' => [
        'amount' => 'Anda memiliki :balance 💎',
        'insufficient_balance' => 'Anda tidak memiliki cukup 💎',
    ],

    'safe_mode' => [
        'message' => 'Anda sedang dalam mod :mode. Mengaktifkan mod selamat akan mengurangkan risiko mendapat kandungan pornografi. Namun, mod ini tidak menjamin perlindungan 100%.
Jika anda merasa perbualan anda tidak menuju kepada pornografi, anda boleh menonaktifkan sementara mod selamat di /mode dan meminta rakan anda untuk menghantar media sekali lagi.',
        'unsafe' => 'Tidak Selamat',
        'safe' => 'Selamat',
        'restricted' => 'Rakan anda sedang dalam mod selamat. Anda tidak boleh menghantar foto, pelekat, dan video sehingga rakan anda mengizinkannya atau menonaktifkan mod selamat.',
        'enable_media' => 'Rakan anda meminta untuk mengaktifkan penerimaan media. Sila aktifkan menggunakan butang di bawah dan minta rakan anda untuk menghantar media sekali lagi.',
        'change_success' => 'Berjaya mengubah mod',
        'enable_media_confirmed' => 'Anda boleh menghantar media sekarang',
    ],

    'conversation' => [
        'not_exists' => 'Tidak dalam perbualan
Taip /search untuk mencari rakan lain',
        'locked' => 'Sila tunggu sebelum menghantar mesej lain.',
        'start' => 'Taip /search untuk mencari rakan',
        'priority_search' => 'Anda menggunakan pencarian prioritas. Sila jangan hentikan pencarian atau anda akan kehilangan prioritas pencarian anda.',
    ],

    'banned' => [
        'message' => 'Anda telah diblokir kerana menghantar spam/pornografi/iklan',
    ],

    'soft_banned' => [
        'message' => 'Anda telah diblokir kerana menghantar spam/pornografi/iklan selama :durationInMinutes minit.',
    ],

    'request' => [
        'sent_to_partner' => 'Permintaan anda telah dihantar ke rakan anda.',
    ],

    'language' => [
        'selector' => 'Pilih bahasa yang anda inginkan',
        'indonesia' => '🇮🇩',
        'english' => '🇬🇧',
        'malaysia' => '🇲🇾',
        'hindi' => '🇮🇳',
        'changed' => 'Bahasa telah berjaya ditukar ke Malaysia',
        'contribute' => '🌐 Bahasa tidak tersedia?',
        'contribute_text' => 'Mari bantu kami menterjemahkan bot ini ke bahasa lain. Kami memerlukan bantuan anda untuk menterjemahkan bot ini agar dapat digunakan oleh orang lain di seluruh dunia.
Anda boleh mengunjungi [Github](https://github.com/Kyla-Chat/Kyla-Translations) untuk membantu kami menterjemahkan bot ini.',
    ],

    'captcha' => [
        'message' => 'Sila pilih teks yang betul dari captcha menggunakan butang di bawah ini',
        'success' => 'Captcha berjaya dilewati',
        'failed' => 'Captcha gagal',
    ],

    'report' => [
        'reason' => 'Pilih sebab untuk melaporkan pengguna ini',
        'cancel' => 'Laporan dibatalkan',
        'expired' => 'Anda hanya memiliki 2 jam untuk melaporkan pengguna ini setelah perbualan berakhir.',
        'thank_you' => 'Terima kasih atas laporan anda. Kami akan menyiasatnya secepat mungkin.',
    ],

    'unban' => [
        'success' => 'Pemblokiran akun anda telah berhasil dibuka.',
    ],

    'group' => [
        'join_invitation' => '🙈 Sertai kumpulan kami',
    ],

    'user' => [
        'inactive' => 'Hai :name, Kyla merindui anda kerana sudah lama tidak melihat anda online 😞.
Sudah :month bulan sejak kali terakhir anda online.
Mari cari teman baru kerana mereka menunggu anda sekarang.',
    ],

    'location' => [
        'ask' => 'Sila hantar lokasi anda untuk mendapatkan jarak dengan pasangan anda. Tekan /cancel untuk membatalkan permintaan lokasi.',
        'ask_city' => 'Sila hantar nama bandar anda untuk memaklumkan lokasi anda kepada pasangan anda',
        'cancel' => 'Tekan butang di bawah untuk membatalkan permintaan lokasi',
        'cancelled' => 'Permintaan lokasi telah dibatalkan',
        'confirm' => 'Lokasi telah ditetapkan ke :city',
        'error' => 'Lokasi tidak ditemui',
        'invalid' => 'Saya tidak faham itu. Sila masukkan nama bandar atau kongsi lokasi anda.',
    ],

    'subscribe' => [
        'announcement' => 'Sebelum menggunakan bot ini, anda mesti melanggan saluran ini dahulu. Dengan melanggan, anda akan mendapat akses penuh kepada ciri-ciri bot. Kami secara berkala menyediakan kemas kini dan pengumuman mengenai status bot dan fungsi baru pada saluran ini, jadi melanggan memastikan anda sentiasa maklum.',
    ],

    'queue' => [
        'long_queue' => 'Pada masa ini barisan sedang sibuk. Sila tunggu sebentar.',
        'check_queue' => 'Sila tunggu sebentar, kami sedang memeriksa barisan.',
        'overcrowded_message' => '🚦 Barisan sedang sibuk dengan :count pengguna menunggu!\n\n⚖️ Keseimbangan jantina tidak optimum sekarang.\n\n🎲 Anda boleh memilih untuk berbual dengan jantina rawak untuk padanan yang lebih pantas, atau terus menunggu jantina pilihan anda.',
        'continue_waiting' => '⏳ Anda telah memilih untuk menunggu jantina pilihan anda. Kami akan memberitahu anda apabila padanan ditemui.',
    ],
];
