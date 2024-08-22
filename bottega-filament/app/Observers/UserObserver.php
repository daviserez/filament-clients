<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    public function created(User $user)
    {
        if (! $user->team_id) {
            $user->team_id = $user->id;
            $user->save();
        }
    }
}
