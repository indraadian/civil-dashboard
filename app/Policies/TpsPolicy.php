<?php

namespace App\Policies;

use App\Models\Tps;
use App\Models\User;

class TpsPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Tps $tps): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    public function update(User $user, Tps $tps): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    public function delete(User $user, Tps $tps): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }
}
