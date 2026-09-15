<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function manage(User $user): bool
    {
        return $user->isAdmin() && $user->isActive();
    }

    public function viewAnalytics(User $user): bool
    {
        return $user->isAdmin() && $user->isActive();
    }
}