<?php

namespace App\Telegram\Callbacks;

use App\Telegram\Core\BaseCallback;
use App\Telegram\Contracts\TelegramContextInterface;
use Illuminate\Support\Facades\Log;

class HelpCallback extends BaseCallback
{
    protected string|array $callbackName = 'help';
    protected string $description = 'Callback untuk menampilkan bantuan';

    public function handle(TelegramContextInterface $context): void
    {
        $callbackData = $context->getCallbackData();
        $parts = explode(':', $callbackData, 2);
        $action = $parts[1] ?? 'main';

        switch ($action) {
            case 'commands':
                $this->showCommandsHelp($context);
                break;
            case 'callbacks':
                $this->showCallbacksHelp($context);
                break;
            case 'settings':
                $this->showSettingsHelp($context);
                break;
            default:
                $this->showMainHelp($context);
                break;
        }

        // Answer callback query
        $context->answerCallbackQuery();

        Log::info('Help callback executed', [
            'user_id' => $context->getUserId(),
            'chat_id' => $context->getChatId(),
            'action' => $action
        ]);
    }

    protected function showMainHelp(TelegramContextInterface $context): void
    {
        $helpMessage = "📚 **Bantuan Kyla Bot**\n\n";
        $helpMessage .= "Berikut adalah kategori bantuan yang tersedia:\n\n";
        $helpMessage .= "🔹 **Perintah Dasar**\n";
        $helpMessage .= "• /start - Memulai bot\n";
        $helpMessage .= "• /help - Menampilkan bantuan ini\n";
        $helpMessage .= "• /profile - Mengatur profil pengguna\n";
        $helpMessage .= "• /settings - Pengaturan bot\n\n";
        $helpMessage .= "🔹 **Fitur Utama**\n";
        $helpMessage .= "• /match - Mencari pasangan\n";
        $helpMessage .= "• /chat - Mulai percakapan\n";
        $helpMessage .= "• /report - Laporkan masalah\n\n";
        $helpMessage .= "Pilih kategori di bawah untuk informasi lebih detail:";

        $keyboard = [
            [
                ['text' => '🔹 Perintah', 'callback_data' => 'help:commands'],
                ['text' => '🔹 Callback', 'callback_data' => 'help:callbacks']
            ],
            [
                ['text' => '🔹 Pengaturan', 'callback_data' => 'help:settings'],
                ['text' => '🔙 Kembali', 'callback_data' => 'start:main']
            ]
        ];

        $context->editMessageText($helpMessage, [
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
            'parse_mode' => 'Markdown'
        ]);
    }

    protected function showCommandsHelp(TelegramContextInterface $context): void
    {
        $helpMessage = "🔹 **Daftar Perintah**\n\n";
        $helpMessage .= "**Perintah Dasar:**\n";
        $helpMessage .= "• `/start` - Memulai bot\n";
        $helpMessage .= "• `/help` - Menampilkan bantuan\n";
        $helpMessage .= "• `/profile` - Mengatur profil\n";
        $helpMessage .= "• `/settings` - Pengaturan\n\n";
        $helpMessage .= "**Fitur Matching:**\n";
        $helpMessage .= "• `/match` - Mencari pasangan\n";
        $helpMessage .= "• `/next` - Pasangan berikutnya\n";
        $helpMessage .= "• `/like` - Menyukai pasangan\n";
        $helpMessage .= "• `/pass` - Melewati pasangan\n\n";
        $helpMessage .= "**Fitur Chat:**\n";
        $helpMessage .= "• `/chat` - Mulai percakapan\n";
        $helpMessage .= "• `/stop` - Hentikan percakapan\n";
        $helpMessage .= "• `/block` - Blokir pengguna\n\n";
        $helpMessage .= "**Fitur Admin:**\n";
        $helpMessage .= "• `/admin` - Panel admin\n";
        $helpMessage .= "• `/stats` - Statistik bot\n";
        $helpMessage .= "• `/broadcast` - Kirim pesan ke semua user";

        $keyboard = [
            [
                ['text' => '🔙 Kembali', 'callback_data' => 'help:main']
            ]
        ];

        $context->editMessageText($helpMessage, [
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
            'parse_mode' => 'Markdown'
        ]);
    }

    protected function showCallbacksHelp(TelegramContextInterface $context): void
    {
        $helpMessage = "🔹 **Daftar Callback**\n\n";
        $helpMessage .= "**Navigasi:**\n";
        $helpMessage .= "• `start:main` - Menu utama\n";
        $helpMessage .= "• `help:main` - Bantuan utama\n";
        $helpMessage .= "• `help:commands` - Bantuan perintah\n";
        $helpMessage .= "• `help:callbacks` - Bantuan callback\n\n";
        $helpMessage .= "**Profil:**\n";
        $helpMessage .= "• `profile:view` - Lihat profil\n";
        $helpMessage .= "• `profile:edit` - Edit profil\n";
        $helpMessage .= "• `profile:photo` - Upload foto\n\n";
        $helpMessage .= "**Pengaturan:**\n";
        $helpMessage .= "• `settings:main` - Menu pengaturan\n";
        $helpMessage .= "• `settings:language` - Bahasa\n";
        $helpMessage .= "• `settings:notifications` - Notifikasi\n\n";
        $helpMessage .= "**Matching:**\n";
        $helpMessage .= "• `match:like` - Suka pasangan\n";
        $helpMessage .= "• `match:pass` - Lewati pasangan\n";
        $helpMessage .= "• `match:report` - Laporkan pasangan";

        $keyboard = [
            [
                ['text' => '🔙 Kembali', 'callback_data' => 'help:main']
            ]
        ];

        $context->editMessageText($helpMessage, [
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
            'parse_mode' => 'Markdown'
        ]);
    }

    protected function showSettingsHelp(TelegramContextInterface $context): void
    {
        $helpMessage = "🔹 **Pengaturan Bot**\n\n";
        $helpMessage .= "**Bahasa:**\n";
        $helpMessage .= "Bot mendukung bahasa Indonesia dan Inggris.\n";
        $helpMessage .= "Gunakan `/settings language` untuk mengubah bahasa.\n\n";
        $helpMessage .= "**Notifikasi:**\n";
        $helpMessage .= "Atur notifikasi yang ingin Anda terima:\n";
        $helpMessage .= "• Notifikasi match baru\n";
        $helpMessage .= "• Notifikasi pesan baru\n";
        $helpMessage .= "• Notifikasi sistem\n\n";
        $helpMessage .= "**Privasi:**\n";
        $helpMessage .= "• Siapa yang dapat melihat profil Anda\n";
        $helpMessage .= "• Siapa yang dapat mengirim pesan\n";
        $helpMessage .= "• Data yang dibagikan\n\n";
        $helpMessage .= "**Keamanan:**\n";
        $helpMessage .= "• Blokir pengguna\n";
        $helpMessage .= "• Laporkan penyalahgunaan\n";
        $helpMessage .= "• Hapus akun";

        $keyboard = [
            [
                ['text' => '🔙 Kembali', 'callback_data' => 'help:main']
            ]
        ];

        $context->editMessageText($helpMessage, [
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
            'parse_mode' => 'Markdown'
        ]);
    }
} 
