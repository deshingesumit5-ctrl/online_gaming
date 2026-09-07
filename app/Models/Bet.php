<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bet extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_round_id',
        'user_id',
        'selection',
        'amount',
        'status',
        'payout_amount',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payout_amount' => 'decimal:2',
        ];
    }

    public function round(): BelongsTo
    {
        return $this->belongsTo(GameRound::class, 'game_round_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isCancellable(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $cancellationDuration = $this->round?->room?->cancellation_duration ?? 30;
        $secondsSinceCreation = now()->diffInSeconds($this->created_at);

        return $secondsSinceCreation <= $cancellationDuration && $this->round->isBettingOpen();
    }

    public function remainingCancelSeconds(): int
    {
        if ($this->status !== 'active') {
            return 0;
        }

        $cancellationDuration = $this->round?->room?->cancellation_duration ?? 30;
        $secondsSinceCreation = now()->diffInSeconds($this->created_at);
        return max(0, $cancellationDuration - (int) $secondsSinceCreation);
    }
}
