<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppNotification extends Model
{
    use HasFactory;

    protected $table = 'app_notifications';

    protected $fillable = [
        'user_id',
        'role',
        'type',
        'title',
        'message',
        'link',
        'icon',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function getLinkAttribute(?string $value): ?string
    {
        if (empty($value) || $value === '#') {
            return $value;
        }

        $parsed = parse_url($value);
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

        return $value;
    }

    public function getResolvedLinkAttribute(): string
    {
        return $this->link ?: '#';
    }

    public function markAsRead(): void
    {
        if (!$this->isRead()) {
            $this->update(['read_at' => now()]);
        }
    }

    public function scopeForUser($query, User $user)
    {
        if ($user->isAdmin()) {
            return $query->where(function ($q) use ($user) {
                $q->where('role', 'admin')
                  ->orWhere('user_id', $user->id);
            });
        }

        return $query->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere(function ($sub) {
                  $sub->where('role', 'player')->whereNull('user_id');
              });
        });
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
}
