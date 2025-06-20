<?php

namespace App\Domain\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Balance Transaction Entity
 * 
 * Domain entity for balance transaction records
 */
class BalanceTransaction extends Model
{
    protected $table = 'balance_transactions';

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'previous_balance',
        'current_balance',
        'description',
        'reference_id',
        'reference_type',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'amount' => 'decimal:2',
        'previous_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Business Logic Methods
     */
    public function isCredit(): bool
    {
        return $this->type === 'credit';
    }

    public function isDebit(): bool
    {
        return $this->type === 'debit';
    }

    public function getFormattedAmount(): string
    {
        $prefix = $this->isCredit() ? '+' : '-';
        return $prefix . number_format($this->amount, 2);
    }
}
