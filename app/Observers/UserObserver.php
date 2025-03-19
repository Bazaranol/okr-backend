<?php

namespace App\Observers;

use App\Models\Role;
use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $defaultRole = Role::where('name', 'student')->first();

        if ($defaultRole){
            $user->roles()->attach($defaultRole->id);
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }

    public function updating(User $user) {
        if ($user->isDirty('roles')) {
            $oldRoles = $user->getOriginal('roles');
            $newRoles = $user->roles;

            if ($oldRoles->contains('name', 'student') && !$newRoles->contains('name', 'student')) {
                $user->group_id = null;
            }
        }
    }
}
