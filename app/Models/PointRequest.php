<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PointRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'user_id',
        'current_balance_at_request',
        'points_requested',
        'remarks',
        'status',
        'rejection_remark',
        'processed_by',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'current_balance_at_request' => 'integer',
            'points_requested' => 'integer',
            'processed_at' => 'datetime',
        ];
    }

    public static function generateRequestId(): string
    {
        return 'REQ-' . date('YmdHis') . '-' . strtoupper(Str::random(5));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
