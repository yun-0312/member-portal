<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Schedule;

class SchedulePolicy extends BasePolicy
{
    //全員閲覧可能
    public function view(User $user, Schedule $schedule)
    {
        return true;
    }

}