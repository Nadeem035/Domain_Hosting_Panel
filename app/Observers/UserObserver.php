<?php

namespace App\Observers;

use App\Models\User;
use Spatie\Permission\Models\Role;

class UserObserver
{
    public function created(User $user): void
    {
        $user->assignRole(Role::findOrCreate('reseller', 'web'));
    }
}