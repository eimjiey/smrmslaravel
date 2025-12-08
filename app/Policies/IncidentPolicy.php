<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Incident;
use Illuminate\Auth\Access\HandlesAuthorization;

class IncidentPolicy
{
    use HandlesAuthorization;

    // Optional: Allow administrators to bypass specific authorization checks
    public function before(User $user, $ability)
    {
        // Assuming a method like hasRole('admin') or checking the role column
        if ($user->role === 'Admin') {
            return true;
        }
    }

    /**
     * Determine whether the user can permanently or softly delete the incident.
     * Only allow Admin to delete, as this removes the record from general view.
     */
    public function delete(User $user, Incident $incident)
    {
        // 🎯 RULE: Only an 'Admin' or 'Staff' role can delete an incident record.
        return in_array($user->role, ['Admin', 'Staff']);
    }
}