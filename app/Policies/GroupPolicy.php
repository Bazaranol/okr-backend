<?php

namespace App\Policies;

use App\Models\User;

class GroupPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function addToGroup(User $user)
    {
        return $user->hasRole(['dean', 'admin']);
    }

    public function removeFromGroup(User $user)
    {
        return $user->hasRole(['dean', 'admin']);
    }
}
