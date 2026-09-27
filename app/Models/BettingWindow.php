<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BettingWindow extends Model
{
    protected $fillable = [
        'game_round_id',
        'window_number',
        'status',
        'started_at',
        'ended_at',
        'first_card_matched',
        'payout_mode',
        'payout_locked',
        'payout_processed',
        'winning_side',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'window_number' => 'integer',
            'payout_mode' => 'integer',
            'payout_locked' => 'boolean',
            'payout_processed' => 'boolean',
        ];
    }

    public static function returnForMode(float $amount, ?int $mode): int
    {
        if ((int) $mode === 25) {
            return (int) round($amount * 1.25);
        }

        return (int) round($amount * 2);
    }

    public static function profitForMode(float $amount, ?int $mode): int
    {
        return self::returnForMode($amount, $mode) - (int) round($amount);
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

    public function firstCardLabel(): string
    {
        if (!$this->payout_locked) {
            return 'Not set';
        }

        return $this->first_card_matched ? 'Matched' : 'Not matched';
    }

    public function round(): BelongsTo
    {
        return $this->belongsTo(GameRound::class, 'game_round_id');
    }

    public function bets(): HasMany
    {
        return $this->hasMany(Bet::class);
    }
}
