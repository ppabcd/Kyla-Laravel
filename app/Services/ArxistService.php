<?php

namespace App\Services;

use App\Helpers\CodeHelper;

class ArxistService
{
    private string $url = 'https://arxist.id';

    private bool $disabled = true;

    private EncryptionService $encryptionService;

    public function __construct(EncryptionService $encryptionService)
    {
        $this->encryptionService = $encryptionService;
        logger('Arxist Service initialized');
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function setDisabled(bool $disabled): void
    {
        $this->disabled = $disabled;
    }

    public function donation(array $data): string
    {
        $urlData = [
            'unit' => $data['unit'],
            'description' => "ID: {$data['userId']}\nSign: ".$this->encryptionService->encrypt((string) $data['userId']),
            'disableDescription' => $this->disabled,
        ];

        $url = CodeHelper::objectToUrlEncoded($urlData);

        return "{$this->url}/kyla/tip?{$url}";
    }

    public function sendKeyboard(array $data, $keyboard): array
    {
        return $keyboard->getArxistDonationKeyboard($data);
    }
}
