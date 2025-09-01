<?php

namespace App\Telegram\Callbacks;

use App\Telegram\Contracts\TelegramContextInterface;
use App\Telegram\Core\BaseCallback;
use Illuminate\Support\Facades\Log;

class ProfileCallback extends BaseCallback
{
    protected string|array $callbackName = 'profile';

    protected string $description = 'Callback untuk mengatur profil pengguna';

    public function handle(TelegramContextInterface $context): void
    {
        $callbackData = $context->getCallbackData();
        $parts = explode(':', $callbackData, 2);
        $action = $parts[1] ?? 'view';

        switch ($action) {
            case 'view':
                $this->showProfile($context);
                break;
            case 'edit':
                $this->showEditProfile($context);
                break;
            case 'photo':
                $this->showPhotoUpload($context);
                break;
            case 'settings':
                $this->showProfileSettings($context);
                break;
            case 'edit_name':
                $this->showEditName($context);
                break;
            case 'edit_bio':
                $this->showEditBio($context);
                break;
            case 'edit_location':
                $this->showEditLocation($context);
                break;
            case 'edit_interests':
                $this->showEditInterests($context);
                break;
            case 'edit_language':
                $this->showEditLanguage($context);
                break;
            case 'upload_photo':
                $this->showUploadPhoto($context);
                break;
            case 'view_photo':
                $this->showViewPhoto($context);
                break;
            case 'settings_privacy':
                $this->showPrivacySettings($context);
                break;
            case 'settings_notifications':
                $this->showNotificationSettings($context);
                break;
            case 'settings_location':
                $this->showLocationSettings($context);
                break;
            default:
                $this->showProfile($context);
                break;
        }

        // Answer callback query
        $context->answerCallbackQuery();

        Log::info('Profile callback executed', [
            'user_id' => $context->getUserId(),
            'chat_id' => $context->getChatId(),
            'action' => $action,
        ]);
    }

    protected function showProfile(TelegramContextInterface $context): void
    {
        $user = $context->getUserModel();
        $userName = $user ? $user->first_name : 'Pengguna';

        $profileMessage = "👤 **Profil Pengguna**\n\n";
        $profileMessage .= "**Nama:** {$userName}\n";
        $profileMessage .= '**Username:** @'.($user->username ?? 'tidak ada')."\n";
        $profileMessage .= '**Bahasa:** '.($user->language_code ?? 'en')."\n";
        $profileMessage .= '**Status:** '.($user->is_bot ? 'Bot' : 'User')."\n";
        $profileMessage .= '**Bergabung:** '.($user->created_at?->format('d/m/Y H:i') ?? 'N/A')."\n\n";
        $profileMessage .= 'Pilih opsi di bawah untuk mengatur profil:';

        $keyboard = [
            [
                ['text' => '✏️ Edit Profil', 'callback_data' => 'profile:edit'],
                ['text' => '📷 Upload Foto', 'callback_data' => 'profile:photo'],
            ],
            [
                ['text' => '⚙️ Pengaturan', 'callback_data' => 'profile:settings'],
                ['text' => '🔙 Kembali', 'callback_data' => 'start:main'],
            ],
        ];

        $context->editMessageText($profileMessage, [
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
            'parse_mode' => 'Markdown',
        ]);
    }

    protected function showEditProfile(TelegramContextInterface $context): void
    {
        $editMessage = "✏️ **Edit Profil**\n\n";
        $editMessage .= "Silakan pilih bagian yang ingin Anda edit:\n\n";
        $editMessage .= "• **Nama** - Ubah nama depan dan belakang\n";
        $editMessage .= "• **Bio** - Tambahkan deskripsi tentang diri Anda\n";
        $editMessage .= "• **Lokasi** - Set lokasi Anda\n";
        $editMessage .= "• **Minat** - Pilih minat dan hobi\n";
        $editMessage .= '• **Bahasa** - Ubah bahasa preferensi';

        $keyboard = [
            [
                ['text' => '👤 Nama', 'callback_data' => 'profile:edit_name'],
                ['text' => '📝 Bio', 'callback_data' => 'profile:edit_bio'],
            ],
            [
                ['text' => '📍 Lokasi', 'callback_data' => 'profile:edit_location'],
                ['text' => '🎯 Minat', 'callback_data' => 'profile:edit_interests'],
            ],
            [
                ['text' => '🌐 Bahasa', 'callback_data' => 'profile:edit_language'],
                ['text' => '🔙 Kembali', 'callback_data' => 'profile:view'],
            ],
        ];

        $context->editMessageText($editMessage, [
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
            'parse_mode' => 'Markdown',
        ]);
    }

    protected function showPhotoUpload(TelegramContextInterface $context): void
    {
        $photoMessage = "📷 **Upload Foto Profil**\n\n";
        $photoMessage .= "Untuk mengupload foto profil:\n\n";
        $photoMessage .= "1. 📤 Kirim foto yang ingin Anda gunakan\n";
        $photoMessage .= "2. 🖼️ Foto akan otomatis disimpan sebagai foto profil\n";
        $photoMessage .= "3. ✅ Foto akan ditampilkan di profil Anda\n\n";
        $photoMessage .= "**Tips:**\n";
        $photoMessage .= "• Gunakan foto yang jelas dan berkualitas baik\n";
        $photoMessage .= "• Pastikan foto menampilkan wajah Anda dengan jelas\n";
        $photoMessage .= '• Hindari foto yang terlalu gelap atau blur';

        $keyboard = [
            [
                ['text' => '📤 Upload Sekarang', 'callback_data' => 'profile:upload_photo'],
                ['text' => '🖼️ Lihat Foto Saat Ini', 'callback_data' => 'profile:view_photo'],
            ],
            [
                ['text' => '🔙 Kembali', 'callback_data' => 'profile:view'],
            ],
        ];

        $context->editMessageText($photoMessage, [
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
            'parse_mode' => 'Markdown',
        ]);
    }

    protected function showProfileSettings(TelegramContextInterface $context): void
    {
        $settingsMessage = "⚙️ **Pengaturan Profil**\n\n";
        $settingsMessage .= "Atur preferensi profil Anda:\n\n";
        $settingsMessage .= "🔒 **Privasi**\n";
        $settingsMessage .= "• Siapa yang dapat melihat profil Anda\n";
        $settingsMessage .= "• Siapa yang dapat mengirim pesan\n\n";
        $settingsMessage .= "🔔 **Notifikasi**\n";
        $settingsMessage .= "• Notifikasi match baru\n";
        $settingsMessage .= "• Notifikasi pesan baru\n";
        $settingsMessage .= "• Notifikasi sistem\n\n";
        $settingsMessage .= "🌍 **Lokasi**\n";
        $settingsMessage .= "• Radius pencarian\n";
        $settingsMessage .= '• Tampilkan lokasi di profil';

        $keyboard = [
            [
                ['text' => '🔒 Privasi', 'callback_data' => 'profile:settings_privacy'],
                ['text' => '🔔 Notifikasi', 'callback_data' => 'profile:settings_notifications'],
            ],
            [
                ['text' => '🌍 Lokasi', 'callback_data' => 'profile:settings_location'],
                ['text' => '🔙 Kembali', 'callback_data' => 'profile:view'],
            ],
        ];

        $context->editMessageText($settingsMessage, [
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
            'parse_mode' => 'Markdown',
        ]);
    }

    protected function showEditName(TelegramContextInterface $context): void
    {
        $message = "👤 **Edit Nama**\n\n";
        $message .= "Silakan kirim nama baru Anda dalam format:\n";
        $message .= "`Nama Depan Nama Belakang`\n\n";
        $message .= 'Contoh: `John Doe` atau `Jane Smith`';

        $keyboard = [
            [
                ['text' => '🔙 Kembali', 'callback_data' => 'profile:edit'],
            ],
        ];

        $context->editMessageText($message, [
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
            'parse_mode' => 'Markdown',
        ]);
    }

    protected function showEditBio(TelegramContextInterface $context): void
    {
        $message = "📝 **Edit Bio**\n\n";
        $message .= "Silakan kirim bio baru Anda.\n";
        $message .= "Bio akan ditampilkan di profil Anda.\n\n";
        $message .= "**Tips:**\n";
        $message .= "• Tulis sesuatu yang menarik tentang diri Anda\n";
        $message .= "• Maksimal 500 karakter\n";
        $message .= '• Hindari informasi pribadi yang sensitif';

        $keyboard = [
            [
                ['text' => '🔙 Kembali', 'callback_data' => 'profile:edit'],
            ],
        ];

        $context->editMessageText($message, [
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
            'parse_mode' => 'Markdown',
        ]);
    }

    protected function showEditLocation(TelegramContextInterface $context): void
    {
        $message = "📍 **Edit Lokasi**\n\n";
        $message .= "Silakan kirim lokasi Anda dengan cara:\n\n";
        $message .= "1. 📍 Klik tombol 'Kirim Lokasi' di bawah\n";
        $message .= "2. 🗺️ Pilih lokasi Anda di peta\n";
        $message .= "3. ✅ Lokasi akan disimpan otomatis\n\n";
        $message .= 'Atau ketik nama kota/kabupaten Anda.';

        $keyboard = [
            [
                ['text' => '📍 Kirim Lokasi', 'callback_data' => 'profile:send_location'],
            ],
            [
                ['text' => '🔙 Kembali', 'callback_data' => 'profile:edit'],
            ],
        ];

        $context->editMessageText($message, [
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
            'parse_mode' => 'Markdown',
        ]);
    }

    protected function showEditInterests(TelegramContextInterface $context): void
    {
        $message = "🎯 **Edit Minat**\n\n";
        $message .= "Pilih minat yang sesuai dengan Anda:\n\n";
        $message .= 'Minat yang dipilih akan membantu menemukan pasangan yang cocok.';

        $keyboard = [
            [
                ['text' => '🎵 Musik', 'callback_data' => 'profile:interest_music'],
                ['text' => '🎬 Film', 'callback_data' => 'profile:interest_movie'],
            ],
            [
                ['text' => '📚 Buku', 'callback_data' => 'profile:interest_book'],
                ['text' => '🏃 Olahraga', 'callback_data' => 'profile:interest_sport'],
            ],
            [
                ['text' => '🍳 Memasak', 'callback_data' => 'profile:interest_cooking'],
                ['text' => '✈️ Travel', 'callback_data' => 'profile:interest_travel'],
            ],
            [
                ['text' => '🎨 Seni', 'callback_data' => 'profile:interest_art'],
                ['text' => '💻 Teknologi', 'callback_data' => 'profile:interest_tech'],
            ],
            [
                ['text' => '🔙 Kembali', 'callback_data' => 'profile:edit'],
            ],
        ];

        $context->editMessageText($message, [
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
            'parse_mode' => 'Markdown',
        ]);
    }

    protected function showEditLanguage(TelegramContextInterface $context): void
    {
        $message = "🌐 **Edit Bahasa**\n\n";
        $message .= "Pilih bahasa yang Anda inginkan:\n\n";
        $message .= 'Bahasa ini akan digunakan untuk semua pesan bot.';

        $keyboard = [
            [
                ['text' => '🇮🇩 Indonesia', 'callback_data' => 'profile:language_id'],
                ['text' => '🇺🇸 English', 'callback_data' => 'profile:language_en'],
            ],
            [
                ['text' => '🇲🇾 Malaysia', 'callback_data' => 'profile:language_ms'],
                ['text' => '🇮🇳 Hindi', 'callback_data' => 'profile:language_in'],
            ],
            [
                ['text' => '🔙 Kembali', 'callback_data' => 'profile:edit'],
            ],
        ];

        $context->editMessageText($message, [
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
            'parse_mode' => 'Markdown',
        ]);
    }

    protected function showUploadPhoto(TelegramContextInterface $context): void
    {
        $message = "📤 **Upload Foto**\n\n";
        $message .= "Silakan kirim foto yang ingin Anda gunakan sebagai foto profil.\n\n";
        $message .= "**Persyaratan:**\n";
        $message .= "• Format: JPG, PNG, atau GIF\n";
        $message .= "• Ukuran maksimal: 10MB\n";
        $message .= '• Rasio aspek: 1:1 (persegi) direkomendasikan';

        $keyboard = [
            [
                ['text' => '🔙 Kembali', 'callback_data' => 'profile:photo'],
            ],
        ];

        $context->editMessageText($message, [
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
            'parse_mode' => 'Markdown',
        ]);
    }

    protected function showViewPhoto(TelegramContextInterface $context): void
    {
        $message = "🖼️ **Foto Profil Saat Ini**\n\n";
        $message .= "Anda belum mengupload foto profil.\n\n";
        $message .= 'Klik tombol di bawah untuk mengupload foto pertama Anda.';

        $keyboard = [
            [
                ['text' => '📤 Upload Foto', 'callback_data' => 'profile:upload_photo'],
            ],
            [
                ['text' => '🔙 Kembali', 'callback_data' => 'profile:photo'],
            ],
        ];

        $context->editMessageText($message, [
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
            'parse_mode' => 'Markdown',
        ]);
    }

    protected function showPrivacySettings(TelegramContextInterface $context): void
    {
        $message = "🔒 **Pengaturan Privasi**\n\n";
        $message .= "Atur siapa yang dapat melihat dan berinteraksi dengan Anda:\n\n";
        $message .= "**Visibilitas Profil:**\n";
        $message .= "• Semua pengguna\n";
        $message .= "• Hanya pengguna yang sudah match\n";
        $message .= "• Hanya teman\n\n";
        $message .= "**Pesan:**\n";
        $message .= "• Terima pesan dari semua orang\n";
        $message .= "• Hanya dari pengguna yang sudah match\n";
        $message .= '• Blokir semua pesan';

        $keyboard = [
            [
                ['text' => '👥 Visibilitas', 'callback_data' => 'profile:privacy_visibility'],
                ['text' => '💬 Pesan', 'callback_data' => 'profile:privacy_messages'],
            ],
            [
                ['text' => '🔙 Kembali', 'callback_data' => 'profile:settings'],
            ],
        ];

        $context->editMessageText($message, [
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
            'parse_mode' => 'Markdown',
        ]);
    }

    protected function showNotificationSettings(TelegramContextInterface $context): void
    {
        $message = "🔔 **Pengaturan Notifikasi**\n\n";
        $message .= "Atur notifikasi yang ingin Anda terima:\n\n";
        $message .= "**Notifikasi Aktif:**\n";
        $message .= "✅ Match baru\n";
        $message .= "✅ Pesan baru\n";
        $message .= "✅ Like dari pengguna lain\n";
        $message .= "❌ Notifikasi sistem\n";
        $message .= '❌ Promosi dan iklan';

        $keyboard = [
            [
                ['text' => '✅ Match Baru', 'callback_data' => 'profile:notif_match'],
                ['text' => '✅ Pesan Baru', 'callback_data' => 'profile:notif_message'],
            ],
            [
                ['text' => '✅ Like', 'callback_data' => 'profile:notif_like'],
                ['text' => '❌ Sistem', 'callback_data' => 'profile:notif_system'],
            ],
            [
                ['text' => '🔙 Kembali', 'callback_data' => 'profile:settings'],
            ],
        ];

        $context->editMessageText($message, [
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
            'parse_mode' => 'Markdown',
        ]);
    }

    protected function showLocationSettings(TelegramContextInterface $context): void
    {
        $message = "🌍 **Pengaturan Lokasi**\n\n";
        $message .= "Atur preferensi lokasi untuk pencarian:\n\n";
        $message .= "**Radius Pencarian:**\n";
        $message .= "• 5 km (dekat)\n";
        $message .= "• 25 km (sedang)\n";
        $message .= "• 100 km (jauh)\n";
        $message .= "• Seluruh dunia\n\n";
        $message .= "**Tampilkan Lokasi:**\n";
        $message .= "• Tampilkan di profil\n";
        $message .= '• Sembunyikan dari profil';

        $keyboard = [
            [
                ['text' => '5 km', 'callback_data' => 'profile:location_5km'],
                ['text' => '25 km', 'callback_data' => 'profile:location_25km'],
            ],
            [
                ['text' => '100 km', 'callback_data' => 'profile:location_100km'],
                ['text' => '🌍 Dunia', 'callback_data' => 'profile:location_world'],
            ],
            [
                ['text' => '🔙 Kembali', 'callback_data' => 'profile:settings'],
            ],
        ];

        $context->editMessageText($message, [
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
            'parse_mode' => 'Markdown',
        ]);
    }
}
