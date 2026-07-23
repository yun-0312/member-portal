<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ScheduleOccurrence;

class ScheduleOccurrencePolicy
{
    /**
     * Create a new policy instance.
     */
    protected function isStaffOrAdmin(User $user): bool
    {
        return in_array(optional($user->role)->name, ['admin', 'staff'], true);
    }

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, ScheduleOccurrence $scheduleOccurrence): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $this->isStaffOrAdmin($user);
    }

    public function update(User $user, ScheduleOccurrence $scheduleOccurrence): bool
    {
        return $this->isStaffOrAdmin($user);
    }

    public function delete(User $user, ScheduleOccurrence $scheduleOccurrence): bool
    {
        return $this->isStaffOrAdmin($user);
    }
}
