<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Notice
            'notice.create',
            'notice.update',
            'notice.delete',

            // Content
            'content.create',
            'content.update',
            'content.delete',

            // Workshop
            'workshop.create',
            'workshop.update',
            'workshop.delete',

            // Video
            'video.create',
            'video.update',
            'video.delete',

            // FAQ
            'faq.create',
            'faq.update',
            'faq.delete',

            // Schedule
            'schedule.create',
            'schedule.update',
            'schedule.delete',

            // User管理
            'user.create',
            'user.update',
            'user.delete',

            //Role管理
            'role.create',
            'role.update',
            'role.delete',

            //Permission管理
            'permission.create',
            'permission.update',
            'permission.delete',

            //Room管理
            'room.create',
            'room.update',
            'room.delete',

            //MedicalInstitution管理
            'medical_institution.create',
            'medical_institution.update',
            'medical_institution.delete',

            //Category系管理
            'category.create',
            'category.update',
            'category.delete',

        ];

        foreach ($permissions as $name) {
            Permission::create(['name' => $name]);
        }
    }
}
