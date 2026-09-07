<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\User;

class NotificationService
{
    /**
     * Send notification to a specific user.
     */
    public function sendToUser(
        User|int $user,
        string $type,
        string $title,
        string $message,
        ?string $link = null,
        ?string $icon = null
    ): AppNotification {
        $userId = $user instanceof User ? $user->id : $user;
        $role = $user instanceof User && $user->isAdmin() ? 'admin' : 'player';

        return AppNotification::create([
            'user_id' => $userId,
            'role' => $role,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $this->normalizeLink($link),
            'icon' => $icon ?: $this->getDefaultIcon($type),
            'read_at' => null,
        ]);
    }

    /**
     * Send notification to all registered players.
     */
    public function sendToPlayers(
        string $type,
        string $title,
        string $message,
        ?string $link = null,
        ?string $icon = null
    ): void {
        $players = User::where('role', 'player')->get(['id']);
        $normalizedLink = $this->normalizeLink($link);
        $iconToUse = $icon ?: $this->getDefaultIcon($type);
        $now = now();

        $records = [];
        foreach ($players as $player) {
            $records[] = [
                'user_id' => $player->id,
                'role' => 'player',
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'link' => $normalizedLink,
                'icon' => $iconToUse,
                'read_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($records)) {
            AppNotification::insert($records);
        }
    }

    /**
     * Send notification to administrators.
     */
    public function sendToAdmin(
        string $type,
        string $title,
        string $message,
        ?string $link = null,
        ?string $icon = null
    ): AppNotification {
        return AppNotification::create([
            'user_id' => null,
            'role' => 'admin',
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $this->normalizeLink($link),
            'icon' => $icon ?: $this->getDefaultIcon($type),
            'read_at' => null,
        ]);
    }

    /**
     * Get unread notifications count for a user.
     */
    public function getUnreadCount(User $user): int
    {
        return AppNotification::forUser($user)->unread()->count();
    }

    /**
     * Get recent notifications list for a user.
     */
    public function getRecentNotifications(User $user, int $limit = 15)
    {
        return AppNotification::forUser($user)
            ->latest('id')
            ->take($limit)
            ->get();
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsRead(User $user): int
    {
        return AppNotification::forUser($user)
            ->unread()
            ->update(['read_at' => now()]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(int $notificationId, User $user): bool
    {
        $notification = AppNotification::forUser($user)->where('id', $notificationId)->first();
        if ($notification) {
            $notification->markAsRead();
            return true;
        }
        return false;
    }

    /**
     * Default icon emoji/class by type.
     */
    public function getDefaultIcon(string $type): string
    {
        return match ($type) {
            'registration' => '📝',
            'account_approval' => '✅',
            'points_added' => '🪙',
            'bet_confirmation', 'betting_open' => '🎲',
            'game_result' => '🏆',
            'withdrawal', 'withdrawal_request' => '🏦',
            'withdrawal_approved' => '💵',
            'withdrawal_rejected' => '❌',
            'admin_registration' => '👤',
            'admin_withdrawal' => '🏧',
            'admin_point_request' => '💳',
            'admin_game_result' => '🎯',
            default => '🔔',
        };
    }

    /**
     * Normalize notification link to a root-relative path if it belongs to local or app host.
     */
    public function normalizeLink(?string $link): ?string
    {
        if (empty($link) || $link === '#') {
            return $link;
        }

        $parsed = parse_url($link);
        $appHost = parse_url(config('app.url'), PHP_URL_HOST);

        if (isset($parsed['host']) && (in_array(strtolower($parsed['host']), ['localhost', '127.0.0.1']) || ($appHost && strtolower($parsed['host']) === strtolower($appHost)))) {
            $path = $parsed['path'] ?? '/';
            if (isset($parsed['query'])) {
                $path .= '?' . $parsed['query'];
            }
            if (isset($parsed['fragment'])) {
                $path .= '#' . $parsed['fragment'];
            }
            return $path;
        }

        return $link;
    }
}
