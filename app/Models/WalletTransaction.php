<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'transaction_code', // backward compatibility
        'user_id',
        'type',
        'amount',
        'balance_after',
        'previous_balance', // backward compatibility
        'updated_balance',  // backward compatibility
        'remarks',
        'reference_type',
        'reference_id',
        'performed_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'balance_after' => 'integer',
            'updated_balance' => 'integer',
            'previous_balance' => 'integer',
        ];
    }

    public static function generateId(string $typePrefix = 'TXN'): string
    {
        return strtoupper($typePrefix) . '-' . date('YmdHis') . '-' . strtoupper(Str::random(5));
    }

    public static function generateCode(string $prefix = 'TXN'): string
    {
        return self::generateId($prefix);
    }

    // Accessor to transparently support transaction_id or transaction_code
    public function getTransactionIdAttribute($value): string
    {
        return $value ?? $this->attributes['transaction_code'] ?? '';
    }

    // Accessor to transparently support balance_after or updated_balance
    public function getBalanceAfterAttribute($value): int
    {
        return (int) ($value ?? $this->attributes['updated_balance'] ?? 0);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
