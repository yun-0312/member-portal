<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MedicalInstitution;


class HomeController extends Controller
{
        public function index() {
        return response()->json([
            'stats' => [
                'users' => User::count(),
                'medical-institutions' => MedicalInstitution::count(),
            ],
            'links' => [
                'users' => route('admin.users.index'),
                'medical_institutions' => route('admin.medical-institutions.index'),
                'notices' => route('admin.notices.index'),
                'contents' => route('admin.contents.index'),
                'workshops' => route('admin.workshops.index'),
                'schedules' => route('admin.schedules.index'),
                'videos' => route('admin.videos.index'),
                'faqs' => route('admin.faqs.index'),


                'roles' => route('admin.roles.index'),
                'content_categories' => route('admin.content-categories.index'),
                'content_subcategories' => route('admin.content-subcategories.index'),
                'group' => route('admin.groups.index'),
                'group_categories' => route('admin.group-categories.index'),
                'faq_categories' => route('admin.faq-categories.index'),
                'notice_categories' => route('admin.notice-categories.index'),
                'rooms' => route('admin.rooms.index'),
                'permissions' => route('admin.permissions.index'),
                'schedule_category' => route('admin.schedule-categories.index'),
            ],
        ]);
    }
}
