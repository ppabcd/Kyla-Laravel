# Kyla Bot - Laravel Version

Telegram Bot untuk pencarian pasangan dan networking dengan arsitektur bersih (Clean Architecture) menggunakan Laravel.

## 🚀 Fitur Utama

- **Multi-language Support**: Indonesia, English, Melayu, Hindi
- **User Matching**: Sistem pencarian pasangan berdasarkan lokasi dan minat
- **Media Sharing**: Berbagi foto dan video dengan moderasi
- **Payment System**: Sistem donasi dan top-up saldo
- **Admin Panel**: Manajemen user dan bot melalui Telegram
- **Auto Registration**: Commands dan callbacks otomatis terdaftar
- **Clean Architecture**: Pemisahan yang jelas antara layers

## 📋 Requirements

- PHP 8.1+
- Laravel 10+
- MySQL/PostgreSQL/SQLite
- Composer
- Telegram Bot Token

## 🛠️ Installation

### 1. Clone Repository
```bash
git clone <repository-url>
cd KylaLaravel
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Environment Setup
```bash
cp .env.example .env
```

Edit `.env` file:
```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kyla_bot
DB_USERNAME=root
DB_PASSWORD=

# Telegram Bot
TELEGRAM_BOT_TOKEN=your_bot_token_here
TELEGRAM_WEBHOOK_URL=https://yourdomain.com/api/telegram/webhook
TELEGRAM_ADMIN_IDS=123456789,987654321
TELEGRAM_BOT_USERNAME=@your_bot_username
TELEGRAM_DEFAULT_LANGUAGE=en

# App
APP_NAME="Kyla Bot"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
```

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Run Migrations
```bash
php artisan migrate
```

### 6. Setup Webhook
```bash
php artisan telegram:setup-webhook
```

### 7. Start Server
```bash
php artisan serve
```

## 🏗️ Architecture

### Clean Architecture Layers

```
┌─────────────────────────────────────────────────────────────────┐
│                        Presentation Layer                       │
├─────────────────────────────────────────────────────────────────┤
│  Controllers:                                                   │
│  - TelegramWebhookController                                    │
│  - SetupTelegramWebhook Command                                 │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                         Application Layer                       │
├─────────────────────────────────────────────────────────────────┤
│  Services:                                                      │
│  - TelegramBotService (Main orchestrator)                      │
│  - UserService, BannedService, etc.                            │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                          Domain Layer                           │
├─────────────────────────────────────────────────────────────────┤
│  Entities:                                                      │
│  - User, Pair, ConversationLog, etc.                           │
│                                                                  │
│  Repositories:                                                   │
│  - UserRepositoryInterface, etc.                                │
│                                                                  │
│  Commands & Callbacks:                                          │
│  - StartCommand, HelpCommand, etc.                              │
│  - GenderCallback, LanguageCallback, etc.                       │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                        Infrastructure Layer                     │
├─────────────────────────────────────────────────────────────────┤
│  Repositories:                                                  │
│  - UserRepository, PairRepository, etc.                        │
│                                                                  │
│  Middleware:                                                    │
│  - CheckUserMiddleware, CheckBannedUserMiddleware, etc.        │
│                                                                  │
│  Jobs:                                                          │
│  - ProcessTelegramUpdateJob                                     │
└─────────────────────────────────────────────────────────────────┘
```

## 📁 Directory Structure

```
KylaLaravel/
├── app/
│   ├── Application/
│   │   └── Services/           # Application services
│   ├── Console/
│   │   └── Commands/          # Artisan commands
│   ├── Domain/
│   │   ├── Entities/          # Domain entities
│   │   └── Repositories/      # Repository interfaces
│   ├── Infrastructure/
│   │   └── Repositories/      # Repository implementations
│   ├── Telegram/
│   │   ├── Commands/          # Telegram commands
│   │   ├── Callbacks/         # Telegram callbacks
│   │   ├── Middleware/        # Telegram middleware
│   │   ├── Services/          # Telegram services
│   │   └── Contracts/         # Telegram interfaces
│   └── Providers/             # Service providers
├── config/
│   └── telegram.php           # Telegram configuration
├── database/
│   ├── migrations/            # Database migrations
│   └── seeders/              # Database seeders
├── resources/
│   └── lang/                 # Translation files
│       ├── en.json
│       ├── id.json
│       ├── ms.json
│       └── in.json
└── routes/
    └── api.php               # API routes
```

## 🤖 Bot Commands

### User Commands
- `/start` - Memulai bot
- `/help` - Bantuan
- `/profile` - Lihat profil
- `/settings` - Pengaturan
- `/language` - Pilih bahasa
- `/ping` - Test koneksi
- `/rules` - Peraturan bot
- `/feedback` - Kirim feedback
- `/balance` - Lihat saldo
- `/donasi` - Donasi
- `/interest` - Pilih minat
- `/mode` - Pilih mode bot
- `/referral` - Program referral
- `/transfer` - Transfer kredit
- `/pending` - Lihat pending requests

### Admin Commands
- `/announcement` - Buat pengumuman
- `/ban` - Larang pengguna
- `/unban` - Buka larangan pengguna
- `/stats` - Lihat statistik bot

## 🌐 Translation System

Bot mendukung 4 bahasa:
- 🇮🇩 Bahasa Indonesia
- 🇺🇸 English
- 🇲🇾 Bahasa Melayu
- 🇮🇳 हिंदी

Translation files berada di `resources/lang/` dengan struktur JSON:

```json
{
  "commands": {
    "start": "Selamat datang ke Kyla Bot!"
  },
  "callbacks": {
    "gender": {
      "male": "Laki-laki",
      "female": "Perempuan"
    }
  },
  "errors": {
    "user_not_found": "User tidak ditemukan"
  }
}
```

## 🔧 Configuration

### Telegram Configuration (`config/telegram.php`)

```php
return [
    'bot_token' => env('TELEGRAM_BOT_TOKEN'),
    'webhook_url' => env('TELEGRAM_WEBHOOK_URL'),
    'admin_ids' => array_map('intval', explode(',', env('TELEGRAM_ADMIN_IDS', ''))),
    'default_language' => env('TELEGRAM_DEFAULT_LANGUAGE', 'en'),
    
    // Rate limiting
    'rate_limit' => [
        'enabled' => env('TELEGRAM_RATE_LIMIT_ENABLED', true),
        'max_requests_per_minute' => env('TELEGRAM_RATE_LIMIT_MAX_REQUESTS', 30),
    ],
    
    // Media settings
    'media' => [
        'max_file_size' => env('TELEGRAM_MAX_FILE_SIZE', 20971520), // 20MB
        'allowed_types' => ['photo', 'video', 'document', 'audio'],
        'auto_moderation' => env('TELEGRAM_AUTO_MODERATION', true),
    ],
];
```

## 🧪 Testing

### Unit Tests
```bash
php artisan test --filter=Telegram
```

### Feature Tests
```bash
php artisan test --filter=TelegramFeature
```

### Run All Tests
```bash
php artisan test
```

## 📊 Monitoring

### Logs
- Bot logs: `storage/logs/laravel.log`
- Log level: `config/telegram.php` → `logging.level`

### Statistics
Admin dapat melihat statistik bot dengan command `/stats`:
- Total users
- Active users
- Banned users
- Daily/weekly/monthly registrations

## 🔒 Security

### Rate Limiting
- Default: 30 requests per minute per user
- Konfigurasi di `config/telegram.php`

### Admin Protection
- Command admin hanya untuk user ID di `TELEGRAM_ADMIN_IDS`
- Middleware otomatis mengecek permission

### User Banned System
- Sistem banned user dengan alasan dan timestamp
- Middleware otomatis mengecek status banned

## 🚀 Deployment

### Production Setup
1. Set `APP_ENV=production`
2. Set `APP_DEBUG=false`
3. Configure webhook URL
4. Setup SSL certificate
5. Configure database
6. Run migrations
7. Setup webhook

### Environment Variables
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=your_db_host
DB_DATABASE=kyla_bot
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

TELEGRAM_BOT_TOKEN=your_bot_token
TELEGRAM_WEBHOOK_URL=https://yourdomain.com/api/telegram/webhook
TELEGRAM_ADMIN_IDS=123456789,987654321
```

## 🔄 Migration from TypeScript

Migrasi dari TypeScript (Grammy.js) ke Laravel telah selesai dengan:

- ✅ Clean Architecture implementation
- ✅ Auto-registration system
- ✅ Multi-language support
- ✅ Admin commands
- ✅ User management
- ✅ Media handling
- ✅ Payment system
- ✅ Middleware system

Lihat dokumentasi lengkap di `docs/TELEGRAM_MIGRATION.md`

## 📝 License

This project is licensed under the MIT License.

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📞 Support

- 📧 Email: support@kyla.my.id
- 📖 Documentation: `docs/`
- 🐛 Issues: GitHub Issues
- 💬 Telegram: @KylaSupport

## 🙏 Acknowledgments

- Laravel Team untuk framework yang luar biasa
- Telegram Bot API untuk platform yang powerful
- Community untuk kontribusi dan feedback
