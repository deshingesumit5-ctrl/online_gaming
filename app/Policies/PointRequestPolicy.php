<?php

namespace App\Policies;

use App\Models\PointRequest;
use App\Models\User;

class PointRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, PointRequest $pointRequest): bool
    {
        return $user->isAdmin() || $user->id === $pointRequest->user_id;
    }

    public function create(User $user): bool
    {
        return $user->isApproved();
    }

    public function approve(User $user, PointRequest $pointRequest): bool
    {
        return $user->isAdmin();
    }

    public function reject(User $user, PointRequest $pointRequest): bool
    {
        return $user->isAdmin();
    }
}
