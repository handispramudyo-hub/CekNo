<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function moderate(User $user): bool
    {
        return $user->isAdmin() && $user->isActive();
    }

    public function manage(User $user): bool
    {
        return $user->isAdmin() && $user->isActive();
    }
}