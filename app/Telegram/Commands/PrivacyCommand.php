<?php

namespace App\Telegram\Commands;

use App\Telegram\Core\BaseCommand;
use App\Telegram\Contracts\CommandInterface;
use App\Telegram\Core\TelegramContext;
use App\Application\Services\UserService;
use App\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Log;

class PrivacyCommand extends BaseCommand implements CommandInterface
{
    protected string|array $commandName = 'privacy';

    public function __construct(
        private UserService $userService,
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(\App\Telegram\Contracts\TelegramContextInterface $context): void

    {
        try {
            $telegramUser = $context->getFrom();
            if (!$telegramUser) {
                $context->reply('❌ Unable to identify user');
                return;
            }

            $languageCode = $telegramUser['language_code'] ?? 'en';
            $privacyText = $this->getPrivacyText($languageCode);
            
            $context->reply($privacyText, ['parse_mode' => 'Markdown']);

        } catch (\Exception $e) {
            Log::error('Error in PrivacyCommand', [
                'error' => $e->getMessage(),
                'user_id' => $telegramUser['id'] ?? null
            ]);
            
            $context->reply('❌ An error occurred while loading privacy policy.');
        }
    }

    private function getPrivacyText(string $languageCode): string
    {
        $privacyTexts = [
            'en' => $this->getEnglishPrivacyText(),
            'id' => $this->getIndonesianPrivacyText(),
            'ms' => $this->getMalayPrivacyText(),
            'in' => $this->getHindiPrivacyText()
        ];

        return $privacyTexts[$languageCode] ?? $privacyTexts['en'];
    }

    private function getEnglishPrivacyText(): string
    {
        return "🔒 **Privacy Policy**\n\n" .
               "**Last updated:** " . now()->format('F j, Y') . "\n\n" .
               "**Information We Collect:**\n" .
               "• Your Telegram ID and basic profile information\n" .
               "• Messages and conversations within the bot\n" .
               "• Usage data and preferences\n" .
               "• Device information and IP address\n\n" .
               "**How We Use Your Information:**\n" .
               "• To provide matching and chat services\n" .
               "• To improve our algorithms and user experience\n" .
               "• To ensure community safety and prevent abuse\n" .
               "• To provide customer support\n\n" .
               "**Data Sharing:**\n" .
               "We do not sell, trade, or rent your personal information to third parties.\n\n" .
               "**Data Security:**\n" .
               "We implement appropriate security measures to protect your data.\n\n" .
               "**Your Rights:**\n" .
               "• Access your personal data\n" .
               "• Request data deletion\n" .
               "• Control privacy settings\n" .
               "• Opt out of data collection\n\n" .
               "**Contact Us:**\n" .
               "For privacy concerns: privacy@kyla.my.id";
    }

    private function getIndonesianPrivacyText(): string
    {
        return "🔒 **Kebijakan Privasi**\n\n" .
               "**Terakhir diperbarui:** " . now()->format('j F Y') . "\n\n" .
               "**Informasi yang Kami Kumpulkan:**\n" .
               "• ID Telegram dan informasi profil dasar Anda\n" .
               "• Pesan dan percakapan dalam bot\n" .
               "• Data penggunaan dan preferensi\n" .
               "• Informasi perangkat dan alamat IP\n\n" .
               "**Cara Kami Menggunakan Informasi Anda:**\n" .
               "• Untuk menyediakan layanan matching dan chat\n" .
               "• Untuk meningkatkan algoritma dan pengalaman pengguna\n" .
               "• Untuk memastikan keamanan komunitas dan mencegah penyalahgunaan\n" .
               "• Untuk menyediakan dukungan pelanggan\n\n" .
               "**Berbagi Data:**\n" .
               "Kami tidak menjual, memperdagangkan, atau menyewakan informasi pribadi Anda kepada pihak ketiga.\n\n" .
               "**Keamanan Data:**\n" .
               "Kami menerapkan langkah-langkah keamanan yang tepat untuk melindungi data Anda.\n\n" .
               "**Hak Anda:**\n" .
               "• Mengakses data pribadi Anda\n" .
               "• Meminta penghapusan data\n" .
               "• Mengontrol pengaturan privasi\n" .
               "• Menolak pengumpulan data\n\n" .
               "**Hubungi Kami:**\n" .
               "Untuk masalah privasi: privacy@kyla.my.id";
    }

    private function getMalayPrivacyText(): string
    {
        return "🔒 **Dasar Privasi**\n\n" .
               "**Terakhir dikemas kini:** " . now()->format('j F Y') . "\n\n" .
               "**Maklumat yang Kami Kumpul:**\n" .
               "• ID Telegram dan maklumat profil asas anda\n" .
               "• Mesej dan perbualan dalam bot\n" .
               "• Data penggunaan dan keutamaan\n" .
               "• Maklumat peranti dan alamat IP\n\n" .
               "**Cara Kami Menggunakan Maklumat Anda:**\n" .
               "• Untuk menyediakan perkhidmatan matching dan chat\n" .
               "• Untuk meningkatkan algoritma dan pengalaman pengguna\n" .
               "• Untuk memastikan keselamatan komuniti dan mencegah penyalahgunaan\n" .
               "• Untuk menyediakan sokongan pelanggan\n\n" .
               "**Perkongsian Data:**\n" .
               "Kami tidak menjual, memperdagangkan, atau menyewakan maklumat peribadi anda kepada pihak ketiga.\n\n" .
               "**Keselamatan Data:**\n" .
               "Kami melaksanakan langkah-langkah keselamatan yang sesuai untuk melindungi data anda.\n\n" .
               "**Hak Anda:**\n" .
               "• Mengakses data peribadi anda\n" .
               "• Meminta penghapusan data\n" .
               "• Mengawal tetapan privasi\n" .
               "• Menolak pengumpulan data\n\n" .
               "**Hubungi Kami:**\n" .
               "Untuk isu privasi: privacy@kyla.my.id";
    }

    private function getHindiPrivacyText(): string
    {
        return "🔒 **गोपनीयता नीति**\n\n" .
               "**अंतिम अपडेट:** " . now()->format('j F Y') . "\n\n" .
               "**जानकारी जो हम एकत्र करते हैं:**\n" .
               "• आपका टेलीग्राम ID और बुनियादी प्रोफ़ाइल जानकारी\n" .
               "• बॉट के भीतर संदेश और बातचीत\n" .
               "• उपयोग डेटा और प्राथमिकताएं\n" .
               "• डिवाइस जानकारी और IP पता\n\n" .
               "**हम आपकी जानकारी का उपयोग कैसे करते हैं:**\n" .
               "• मैचिंग और चैट सेवाएं प्रदान करने के लिए\n" .
               "• हमारे एल्गोरिदम और उपयोगकर्ता अनुभव में सुधार के लिए\n" .
               "• समुदाय की सुरक्षा सुनिश्चित करने और दुरुपयोग को रोकने के लिए\n" .
               "• ग्राहक सहायता प्रदान करने के लिए\n\n" .
               "**डेटा साझाकरण:**\n" .
               "हम आपकी व्यक्तिगत जानकारी को तृतीय पक्षों को नहीं बेचते, व्यापार नहीं करते या किराए पर नहीं देते।\n\n" .
               "**डेटा सुरक्षा:**\n" .
               "हम आपके डेटा की सुरक्षा के लिए उचित सुरक्षा उपाय लागू करते हैं।\n\n" .
               "**आपके अधिकार:**\n" .
               "• अपने व्यक्तिगत डेटा तक पहुंच\n" .
               "• डेटा हटाने का अनुरोध\n" .
               "• गोपनीयता सेटिंग्स नियंत्रित करें\n" .
               "• डेटा संग्रह से बाहर निकलें\n\n" .
               "**संपर्क करें:**\n" .
               "गोपनीयता संबंधी चिंताओं के लिए: privacy@kyla.my.id";
    }
} 
