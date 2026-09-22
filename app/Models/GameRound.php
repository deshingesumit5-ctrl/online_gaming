<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameRound extends Model
{
    use HasFactory;

    public const SESSION_BET_LIMIT = 1000000;

    protected $fillable = [
        'room_id',
        'round_number',
        'first_card',
        'status',
        'winning_side',
        'payout_mode',
        'first_card_matched',
        'payout_locked',
        'started_at',
        'betting_ends_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'betting_ends_at' => 'datetime',
            'closed_at' => 'datetime',
            'round_number' => 'integer',
            'payout_mode' => 'integer',
            'first_card_matched' => 'boolean',
            'payout_locked' => 'boolean',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function bets(): HasMany
    {
        return $this->hasMany(Bet::class);
    }

    public function bettingWindows(): HasMany
    {
        return $this->hasMany(BettingWindow::class)->orderBy('window_number');
    }

    public function currentBettingWindow(): ?BettingWindow
    {
        return $this->bettingWindows()->latest('id')->first();
    }

    public function payoutLabel(): string
    {
        if ((int) $this->payout_mode === 25) {
            return '25% Profit';
        }
        if ((int) $this->payout_mode === 100) {
            return '100% Profit';
        }
        return 'Pending';
    }

    public function totalReturnForBet(float $amount): int
    {
        $mode = (int) ($this->payout_mode ?: 100);
        if ($mode === 25) {
            return (int) round($amount * 1.25);
        }
        return (int) round($amount * 2);
    }

    public function profitForBet(float $amount): int
    {
        return $this->totalReturnForBet($amount) - (int) round($amount);
    }

    public function userSessionBetTotal(int $userId): float
    {
        return (float) $this->bets()
            ->where('user_id', $userId)
            ->where('status', '!=', 'cancelled')
            ->sum('amount');
    }

    public function isBettingOpen(): bool
    {
        if ($this->status !== 'betting_open' || !$this->betting_ends_at) {
            return false;
        }
        return now()->lessThanOrEqualTo($this->betting_ends_at);
    }

    public function remainingBettingSeconds(): int
    {
        if (!$this->isBettingOpen()) {
            return 0;
        }
        return max(0, (int) now()->diffInSeconds($this->betting_ends_at, false));
    }
}
