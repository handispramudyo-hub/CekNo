<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
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