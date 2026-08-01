<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // システム管理者（system_admin）専用のパーミッション名を定義
        $systemAdminOnlyPermissions = [
            'permission.create',
            'permission.update',
            'permission.delete',
            'notice_category.create',
            'notice_category.update',
            'notice_category.delete',
        ];

        // system_admin（system管理者）：system管理者のみ扱える権限を付与
        $systemAdminPermissions = Permission::whereIn('name', $systemAdminOnlyPermissions)->pluck('id');

        Role::where('name', 'system_admin')->first()
            ->permissions()->sync($systemAdminPermissions);

        // admin（一般管理者）：system_admin専用権限を除外した権限を付与
        $adminPermissions = Permission::whereNotIn('name', $systemAdminOnlyPermissions)->pluck('id');

        Role::where('name', 'admin')->first()
            ->permissions()->sync($adminPermissions);

        //staff
        $staffPermissions = Permission::whereIn('name', [
            'notice.create', 'notice.update', 'notice.delete',
            'content.create', 'content.update', 'content.delete',
            'workshop.create', 'workshop.update', 'workshop.delete',
            'video.create', 'video.update', 'video.delete',
            'faq.create', 'faq.update', 'faq.delete',
            'schedule.create', 'schedule.update', 'schedule.delete',
        ])->pluck('id');

        // staff に付与
        Role::where('name', 'staff')->first()
            ->permissions()->sync($staffPermissions);

        //director、member
        $directorMemberPermissions = Permission::whereIn('name', [
            'medical_institution.update',
        ])->pluck('id');

        Role::where('name', 'director')->first()
            ->permissions()->sync($directorMemberPermissions);

        Role::where('name', 'member')->first()
            ->permissions()->sync($directorMemberPermissions);

    }
}
