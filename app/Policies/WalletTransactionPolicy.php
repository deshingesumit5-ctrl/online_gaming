<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WalletTransaction;

class WalletTransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, WalletTransaction $transaction): bool
    {
        return $user->isAdmin() || $user->id === $transaction->user_id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }
}
