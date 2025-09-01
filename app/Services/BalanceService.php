<?php

namespace App\Services;

class BalanceService
{
    public function getPrettyPrintTransaction(array $data): string
    {
        $userId = $data['userId'] ?? 0;
        $currentBalance = $data['currentBalance'] ?? 0;
        $previousBalance = $data['previousBalance'] ?? 0;
        $amount = $data['amount'] ?? 0;
        $description = $data['description'] ?? '';

        return "👤 User: *{$userId}*\n".
            "👝 Previous Balance: {$previousBalance}\n".
            "💼 Current Balance: *{$currentBalance}*\n".
            "💰 Amount: *{$amount}*\n".
            "📝 Description: *{$description}*";
    }

    public function calculateNewBalance(int $currentBalance, int $amount): int
    {
        return $currentBalance + $amount;
    }

    public function canAfford(int $balance, int $cost): bool
    {
        return $balance >= $cost;
    }

    public function formatBalance(int $balance): string
    {
        return number_format($balance, 0, '.', ',');
    }

    public function getBalanceStatus(int $balance): string
    {
        if ($balance <= 0) {
            return 'insufficient';
        } elseif ($balance < 100) {
            return 'low';
        } elseif ($balance < 1000) {
            return 'medium';
        } else {
            return 'high';
        }
    }

    public function getBalanceEmoji(int $balance): string
    {
        return match ($this->getBalanceStatus($balance)) {
            'insufficient' => '❌',
            'low' => '⚠️',
            'medium' => '💰',
            'high' => '💎',
            default => '💰'
        };
    }
}
