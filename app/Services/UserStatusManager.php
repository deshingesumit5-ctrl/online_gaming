<?php

namespace App\Services;

use App\Models\User;
use InvalidArgumentException;

class UserStatusManager
{
    /**
     * Permitted transitions map: current_status => array of allowed next statuses
     */
    protected const ALLOWED_TRANSITIONS = [
        'pending' => ['approved', 'rejected', 'blocked'],
        'approved' => ['active', 'inactive', 'blocked'],
        'active' => ['inactive', 'blocked'],
        'inactive' => ['active', 'blocked'],
        'blocked' => ['active', 'inactive', 'pending'],
        'rejected' => ['pending', 'approved'],
    ];

    /**
     * Check if status transition is valid.
     */
    public static function canTransition(string $currentStatus, string $newStatus): bool
    {
        if ($currentStatus === $newStatus) {
            return true;
        }

        $allowed = self::ALLOWED_TRANSITIONS[$currentStatus] ?? [];
        return in_array($newStatus, $allowed, true);
    }

    /**
     * Enforce status transition.
     */
    public static function transition(User $user, string $newStatus): void
    {
        $currentStatus = $user->status;

        if (!self::canTransition($currentStatus, $newStatus)) {
            throw new InvalidArgumentException(
                "Invalid status transition from '{$currentStatus}' to '{$newStatus}'."
            );
        }

        $user->status = $newStatus;
        $user->save();
    }
}
