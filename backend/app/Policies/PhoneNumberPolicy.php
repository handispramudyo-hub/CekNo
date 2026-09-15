<?php

namespace App\Policies;

use App\Models\PhoneNumber;
use App\Models\User;

class PhoneNumberPolicy
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