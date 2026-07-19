<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ScheduleOccurrence;
use App\Policies\BasePolicy;

class ScheduleOccurrencePolicy extends BasePolicy
{
    public function view(User $user, ScheduleOccurrence $occurrence)
    {
        return true;
    }
}
