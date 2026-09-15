<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
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