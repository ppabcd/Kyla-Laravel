<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;

class EncryptionService
{
    private string $key;

    public function __construct()
    {
        $appKey = config('app.key');
        if (!$appKey) {
            throw new \Exception('APP_KEY is not set');
        }

        // Laravel app key format: base64:... or direct key
        if (str_starts_with($appKey, 'base64:')) {
            $this->key = base64_decode(substr($appKey, 7));
        } else {
            $this->key = $appKey;
        }
    }

    public function encrypt(string $text): string
    {
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($text, 'aes-256-cbc', $this->key, OPENSSL_RAW_DATA, $iv);

        if ($encrypted === false) {
            throw new \Exception('Encryption failed');
        }

        return base64_encode($iv . $encrypted);
    }

    public function decrypt(string $encryptedText): string
    {
        $data = base64_decode($encryptedText);
        $iv = substr($data, 0, 16);
        $ciphertext = substr($data, 16);

        $decrypted = openssl_decrypt($ciphertext, 'aes-256-cbc', $this->key, OPENSSL_RAW_DATA, $iv);

        if ($decrypted === false) {
            throw new \Exception('Decryption failed');
        }

        return $decrypted;
    }

    /**
     * Alternative using Laravel's Crypt facade for simpler usage
     */
    public function encryptLaravel(string $text): string
    {
        return Crypt::encryptString($text);
    }

    public function decryptLaravel(string $encryptedText): string
    {
        return Crypt::decryptString($encryptedText);
    }
}
