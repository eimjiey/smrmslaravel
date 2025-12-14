<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Incident;
use Illuminate\Auth\Access\HandlesAuthorization;

class IncidentPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->role === 'Admin') {
            return true;
        }
    }

    public function delete(User $user, Incident $incident)
    {
        return in_array($user->role, ['Admin', 'Staff']);
    }
}
