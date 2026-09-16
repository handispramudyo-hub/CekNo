<?php

namespace App\Policies;

use App\Models\ContactContribution;
use App\Models\User;

class ContactContributionPolicy
{
    public function moderate(User $user): bool
    {
        return $user->isAdmin() && $user->isActive();
    }
}