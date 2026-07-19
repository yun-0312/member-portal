<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Faq;

class FaqPolicy extends BasePolicy
{
    //全員閲覧可能
    public function view(User $user, Faq $faq)
    {
        return true;
    }
}