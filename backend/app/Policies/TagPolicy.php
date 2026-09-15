<?php

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;

class TagPolicy
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