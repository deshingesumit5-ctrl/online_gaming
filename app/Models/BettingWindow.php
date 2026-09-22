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
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'window_number' => 'integer',
        ];
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
