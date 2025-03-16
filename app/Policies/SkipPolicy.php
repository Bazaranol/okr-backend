<?php

namespace App\Policies;

use App\Models\Skip;
use App\Models\User;

class SkipPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function update(User $user, Skip $skip): bool {
        return $user->hasRole(['dean', 'admin']);
    }
}
