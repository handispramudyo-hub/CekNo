<?php

namespace App\Policies;

use App\Models\MlModel;
use App\Models\User;

class MlModelPolicy
{
    public function manage(User $user): bool
    {
        return $user->isAdmin() && $user->isActive();
    }
}