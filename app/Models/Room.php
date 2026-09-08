<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'name',
        'live_stream_url',
        'is_streaming',
        'betting_duration',
        'cancellation_duration',
        'allowed_denominations',
        'status',
        'start_time',
    ];

    protected function casts(): array
    {
        return [
            'allowed_denominations' => 'array',
            'betting_duration' => 'integer',
            'cancellation_duration' => 'integer',
            'is_streaming' => 'boolean',
            'start_time' => 'datetime',
        ];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(GameRound::class)->orderBy('id', 'desc');
    }

    public function gameRounds(): HasMany
    {
        return $this->hasMany(GameRound::class)->orderBy('id', 'desc');
    }

    public function currentRound(): HasOne
    {
        return $this->hasOne(GameRound::class)->latestOfMany();
    }
}
