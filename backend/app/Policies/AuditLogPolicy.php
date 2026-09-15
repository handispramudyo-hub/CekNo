<?php

namespace App\Policies;

use App\Models\AuditLog;
use App\Models\User;

class AuditLogPolicy
{
    public function manage(User $user): bool
    {
        return $user->isAdmin() && $user->isActive();
    }
}