<?php

namespace App\Services;

use App\Telegram\Services\KeyboardService;
use App\Services\CacheService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class CaptchaService
{
    private KeyboardService $keyboardService;
    private CacheService $cacheService;

    public function __construct(KeyboardService $keyboardService, CacheService $cacheService)
    {
        $this->keyboardService = $keyboardService;
        $this->cacheService = $cacheService;
    }

    public function generateCaptcha(array &$session): void
    {
        $code = $this->generateCaptchaCode();
        $imageData = $this->createCaptchaImage($code);

        $session['captcha'] = [
            'image' => $imageData,
            'code' => $code,
            'expiredAt' => time() + 60 // 60 seconds
        ];
    }

    public function checkCaptchaAvailable(array $session): bool
    {
        return isset($session['captcha']) &&
            $session['captcha']['expiredAt'] > time();
    }

    public function verifyCaptcha(array $session, string $userCode): bool
    {
        if (!$this->checkCaptchaAvailable($session)) {
            return false;
        }

        return strtolower($session['captcha']['code']) === strtolower($userCode);
    }

    public function generateCaptchaCode(int $length = 5): string
    {
        // Generate random string without confusing characters
        $characters = 'abcdefghjkmnpqrstuvwxyz23456789';
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }

        return strtoupper($code);
    }

    public function createCaptchaImage(string $code): string
    {
        // Simple captcha image generation
        $width = 150;
        $height = 50;

        // Create image
        $image = imagecreate($width, $height);

        // Colors
        $bgColor = imagecolorallocate($image, 255, 255, 255); // White background
        $textColor = imagecolorallocate($image, 0, 0, 0); // Black text
        $noiseColor = imagecolorallocate($image, 200, 200, 200); // Gray noise

        // Add noise
        for ($i = 0; $i < 100; $i++) {
            imagesetpixel($image, rand(0, $width), rand(0, $height), $noiseColor);
        }

        // Add text
        $fontSize = 20;
        $angle = 0;
        $x = ($width - strlen($code) * 15) / 2;
        $y = ($height + $fontSize) / 2;

        // Try to use TTF font if available, otherwise use built-in font
        if (function_exists('imagettftext') && file_exists(public_path('fonts/arial.ttf'))) {
            imagettftext($image, $fontSize, $angle, $x, $y, $textColor, public_path('fonts/arial.ttf'), $code);
        } else {
            imagestring($image, 5, $x, $y - 10, $code, $textColor);
        }

        // Convert to base64
        ob_start();
        imagepng($image);
        $imageData = ob_get_contents();
        ob_end_clean();
        imagedestroy($image);

        return base64_encode($imageData);
    }

    public function getCaptchaKeyboard(array $translations, string $code): array
    {
        return $this->keyboardService->getCaptchaKeyboard($translations, $code);
    }

    public function sendCaptchaMessage(array $context, array &$session): array
    {
        $this->generateCaptcha($session);

        $code = $session['captcha']['code'];
        $imageData = $session['captcha']['image'];

        return [
            'type' => 'photo',
            'photo' => 'data:image/png;base64,' . $imageData,
            'caption' => $context['translations']['captcha.message'] ?? 'Please solve the captcha below:',
            'reply_markup' => $this->getCaptchaKeyboard($context['translations'] ?? [], $code),
            'parse_mode' => 'Markdown'
        ];
    }

    public function clearCaptcha(array &$session): void
    {
        unset($session['captcha']);
    }

    /**
     * Check if user needs to solve captcha
     */
    public function needsCaptcha($user): bool
    {
        // Check if user has been verified recently
        $verifiedKey = "captcha_verified:{$user->id}";
        if ($this->cacheService->has($verifiedKey)) {
            return false;
        }

        // Check if user has failed captcha attempts
        $attemptsKey = "captcha_attempts:{$user->id}";
        $attempts = $this->cacheService->get($attemptsKey, 0);

        // Require captcha if user has failed attempts or is new
        return $attempts > 0 || $user->isNew();
    }

    /**
     * Send captcha to user
     */
    public function sendCaptcha($context, $user): void
    {
        $captcha = $this->generateMathCaptcha();

        // Store captcha answer in cache
        $answerKey = "captcha_answer:{$user->id}";
        $this->cacheService->put($answerKey, $captcha['answer'], 300); // 5 minutes

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => $captcha['options'][0], 'callback_data' => 'captcha:' . $captcha['options'][0]],
                    ['text' => $captcha['options'][1], 'callback_data' => 'captcha:' . $captcha['options'][1]]
                ],
                [
                    ['text' => $captcha['options'][2], 'callback_data' => 'captcha:' . $captcha['options'][2]],
                    ['text' => $captcha['options'][3], 'callback_data' => 'captcha:' . $captcha['options'][3]]
                ]
            ]
        ];

        $message = __('messages.captcha.solve', [
            'question' => $captcha['question']
        ], $user->language_code ?? 'en');

        $context->sendMessage($message, ['reply_markup' => $keyboard]);
    }

    /**
     * Generate math captcha question and options
     */
    private function generateMathCaptcha(): array
    {
        $num1 = rand(1, 10);
        $num2 = rand(1, 10);
        $correctAnswer = $num1 + $num2;

        // Generate wrong options
        $options = [$correctAnswer];
        while (count($options) < 4) {
            $wrongAnswer = $correctAnswer + rand(-5, 5);
            if ($wrongAnswer != $correctAnswer && $wrongAnswer > 0 && !in_array($wrongAnswer, $options)) {
                $options[] = $wrongAnswer;
            }
        }

        shuffle($options);

        return [
            'question' => "{$num1} + {$num2} = ?",
            'answer' => $correctAnswer,
            'options' => $options
        ];
    }
}
