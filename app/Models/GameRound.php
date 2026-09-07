<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameRound extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'round_number',
        'first_card',
        'status',
        'winning_side',
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
