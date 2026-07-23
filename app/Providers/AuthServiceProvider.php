<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

use App\Models\Content;
use App\Models\ContentCategory;
use App\Models\Notice;
use App\Models\Workshop;
use App\Models\Video;
use App\Models\Schedule;
use App\Models\Faq;
use App\Models\User;
use App\Models\MedicalInstitution;
use App\Models\ScheduleOccurrence;

use App\Policies\ContentPolicy;
use App\Policies\ContentCategoryPolicy;
use App\Policies\NoticePolicy;
use App\Policies\WorkshopPolicy;
use App\Policies\VideoPolicy;
use App\Policies\SchedulePolicy;
use App\Policies\FaqPolicy;
use App\Policies\UserPolicy;
use App\Policies\MedicalInstitutionPolicy;
use App\Policies\ScheduleOccurrencePolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Content::class => ContentPolicy::class,
        ContentCategory::class => ContentCategoryPolicy::class,
        Notice::class => NoticePolicy::class,
        Workshop::class => WorkshopPolicy::class,
        Video::class => VideoPolicy::class,
        Schedule::class => SchedulePolicy::class,
        Faq::class => FaqPolicy::class,
        User::class => UserPolicy::class,
        MedicalInstitution::class => MedicalInstitutionPolicy::class,
        ScheduleOccurrence::class =>
        ScheduleOccurrencePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
                // システムの最高管理者（admin）のみ無条件で許可
                return optional($user->role)->name === 'admin' ? true : null;
            });

            $this->registerPolicies();
    }
}
