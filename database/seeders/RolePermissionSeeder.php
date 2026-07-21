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
        $all = Permission::pluck('id');

        //admin（全権限）
        Role::where('name', 'admin')->first()
            ->permissions()->sync($all);

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

        //medical_staff
        Role::where('name', 'medical_staff')->first()
            ->permissions()->sync(
                Permission::where('name', 'medical_institution.view')->pluck('id')
            );
    }
}
