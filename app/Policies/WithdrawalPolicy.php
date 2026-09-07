<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Withdrawal;

class WithdrawalPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Withdrawal $withdrawal): bool
    {
        return $user->isAdmin() || $user->id === $withdrawal->user_id;
    }

    public function create(User $user): bool
    {
        return $user->isApproved();
    }

    public function process(User $user, Withdrawal $withdrawal): bool
    {
        return $user->isAdmin();
    }

    public function reject(User $user, Withdrawal $withdrawal): bool
    {
        return $user->isAdmin();
    }
}
