<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'mobile',
        'password',
        'dob',
        'address',
        'city',
        'state',
        'country',
        'kyc_info',
        'status',
        'role',
        'wallet_balance',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'dob' => 'date',
            'password' => 'hashed',
            'wallet_balance' => 'integer',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return in_array($this->status, ['approved', 'active']);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' || $this->status === 'approved';
    }

    public function isBlocked(): bool
    {
        return $this->status === 'blocked';
    }

    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function hasSufficientBalance(int|float $amount): bool
    {
        return (int) $this->wallet_balance >= (int) $amount;
    }

    public function getPendingWithdrawalAmountAttribute(): int
    {
        return (int) $this->withdrawals()->where('status', 'pending')->sum('amount_requested');
    }

    public function getAvailableBalanceAttribute(): int
    {
        $available = (int) $this->wallet_balance - $this->pending_withdrawal_amount;
        return max(0, $available);
    }

    public function canWithdraw(int $amount): bool
    {
        return $amount > 0 && $this->available_balance >= $amount;
    }

    public function bets(): HasMany
    {
        return $this->hasMany(Bet::class);
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class)->orderBy('created_at', 'desc')->orderBy('id', 'desc');
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class)->orderBy('created_at', 'desc')->orderBy('id', 'desc');
    }

    public function pointRequests(): HasMany
    {
        return $this->hasMany(PointRequest::class)->orderBy('created_at', 'desc')->orderBy('id', 'desc');
    }
}
