<?php

namespace App\Domain\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Report Domain Entity
 * 
 * Represents user reports for misconduct
 */
class Report extends Model
{
    protected $table = 'reports';

    protected $fillable = [
        'reporter_user_id',
        'reported_user_id',
        'reason',
        'description',
        'evidence',
        'status',
        'reviewed_by',
        'reviewed_at',
        'action_taken',
        'admin_notes',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'reporter_user_id' => 'integer',
        'reported_user_id' => 'integer',
        'evidence' => 'array',
        'reviewed_by' => 'integer',
        'reviewed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relationships
     */
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Business Logic Methods
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isReviewed(): bool
    {
        return $this->status === 'reviewed';
    }

    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    public function isDismissed(): bool
    {
        return $this->status === 'dismissed';
    }

    public function review(int $reviewerId, string $actionTaken, string $notes = null): void
    {
        $this->status = 'reviewed';
        $this->reviewed_by = $reviewerId;
        $this->reviewed_at = now();
        $this->action_taken = $actionTaken;
        $this->admin_notes = $notes;
        $this->save();
    }

    public function resolve(): void
    {
        $this->status = 'resolved';
        $this->save();
    }

    public function dismiss(string $reason = null): void
    {
        $this->status = 'dismissed';
        $this->admin_notes = $reason;
        $this->save();
    }
}
