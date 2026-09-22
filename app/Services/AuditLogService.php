<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\GameRound;

class AuditLogService
{
    public static function record(string $action, GameRound $round, ?string $previous = null, ?string $next = null, ?int $roomId = null): void
    {
        try {
            AuditLog::create([
                'admin_id' => auth()->id(),
                'room_id' => $roomId ?? $round->room_id,
                'game_round_id' => $round->id,
                'action' => $action,
                'previous_state' => $previous,
                'new_state' => $next,
            ]);
        } catch (\Throwable $e) {
        }
    }
}
