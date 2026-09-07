<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Str;

class Withdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'user_id',
        'available_balance_at_request',
        'amount_requested',
        'amount', // backward compatibility
        'settlement_details',
        'status',
        'rejection_remark',
        'rejection_remarks', // backward compatibility
        'processed_by',
        'approved_by', // backward compatibility
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'available_balance_at_request' => 'integer',
            'amount_requested' => 'integer',
            'amount' => 'integer',
            'processed_at' => 'datetime',
        ];
    }

    public static function generateRequestId(): string
    {
        return 'WTH-' . date('YmdHis') . '-' . strtoupper(Str::random(5));
    }

    public function getAmountRequestedAttribute($value): int
    {
        return (int) ($value ?: ($this->attributes['amount'] ?? 0));
    }

    public function getRejectionRemarkAttribute($value): ?string
    {
        return $value ?: ($this->attributes['rejection_remarks'] ?? null);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function approver(): BelongsTo
    {
        return $this->processor();
    }

    public function transaction(): MorphOne
    {
        return $this->morphOne(WalletTransaction::class, 'reference');
    }
}
